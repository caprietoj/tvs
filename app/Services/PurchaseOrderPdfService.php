<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\QuotationItemSelection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PurchaseOrderPdfService
{
    /**
     * Genera PDF de orden de compra usando plantilla unificada
     */
    public function generatePdf(PurchaseOrder $order, $providerSelections = null)
    {
        Log::info('Generando PDF para orden unificada', ['order_id' => $order->id]);

        // Cargar relaciones necesarias
        $order->load(['provider', 'purchaseRequest']);

        // Obtener datos personalizados si existen
        $customData = $order->pdf_custom_data ? json_decode($order->pdf_custom_data, true) : [];

        // Preparar datos para la vista
        $data = [
            'order' => $order,
            'customData' => $customData
        ];

        // Priorizar los valores de la orden sobre customData para consistencia
        $data['subtotal'] = $order->subtotal ?? ($customData['subtotal'] ?? 0);
        $data['ivaAmount'] = $order->iva_amount ?? ($customData['iva_amount'] ?? 0);
        $data['totalAmount'] = $order->total_amount ?? ($customData['total'] ?? 0);
        
        // Log para verificar valores utilizados
        Log::info('Valores utilizados en PDF', [
            'order_id' => $order->id,
            'subtotal' => $data['subtotal'],
            'iva_amount' => $data['ivaAmount'],
            'total_amount' => $data['totalAmount'],
            'source' => $order->subtotal ? 'order_fields' : 'custom_data'
        ]);

        // Solo calcular dinámicamente si no hay valores en la orden ni en customData
        if (($data['subtotal'] <= 0 || $data['totalAmount'] <= 0) && $order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
            Log::warning('Calculando totales dinámicamente debido a valores inválidos', [
                'order_id' => $order->id,
                'current_subtotal' => $data['subtotal'],
                'current_total' => $data['totalAmount']
            ]);
            $this->calculateDynamicTotals($order, $data);
        }

        // Agregar información de presupuesto compartido si existe
        if ($order->purchaseRequest) {
            $purchaseRequest = $order->purchaseRequest;
            
            // Detectar si es compra compartida
            $data['isSharedPurchase'] = $purchaseRequest->is_shared;
            
            if ($purchaseRequest->is_shared) {
                $sharedSections = [];
                
                // Agregar sección principal (la que hace la orden) si existe
                if ($purchaseRequest->section_area) {
                    $sharedSections[] = $purchaseRequest->section_area;
                }
                
                // Agregar sección compartida si existe
                if ($purchaseRequest->shared_section) {
                    $sharedSections[] = $purchaseRequest->shared_section;
                }
                
                // Agregar tercera sección si existe
                if ($purchaseRequest->third_shared_section) {
                    $sharedSections[] = $purchaseRequest->third_shared_section;
                }
                
                $data['sharedSections'] = $sharedSections;
                $data['sharedBudget'] = $purchaseRequest->shared_budget;
                $data['thirdSharedBudget'] = $purchaseRequest->third_shared_budget;
            }
            
            // Agregar observaciones del PurchaseRequest si no están en customData
            if (!isset($customData['observations']) && $purchaseRequest->observations) {
                $data['purchaseRequestObservations'] = $purchaseRequest->observations;
            }
        }

        // 🔧 LÓGICA MEJORADA: Verificar si hay datos editados más recientes en pdf_custom_data
        $hasRecentCustomData = false;
        if (!empty($customData) && is_array($customData) && isset($customData['edited_at'])) {
            $editedAt = \Carbon\Carbon::parse($customData['edited_at']);
            $orderUpdatedAt = $order->updated_at;
            $hasRecentCustomData = $editedAt->gt($orderUpdatedAt) && isset($customData['items']) && !empty($customData['items']);
            
            Log::info('Verificando datos editados recientes', [
                'order_id' => $order->id,
                'has_recent_custom_data' => $hasRecentCustomData,
                'edited_at' => $customData['edited_at'],
                'order_updated_at' => $orderUpdatedAt->toISOString(),
                'custom_items_count' => isset($customData['items']) ? count($customData['items']) : 0
            ]);
        }

        // 🔧 CORRECCIÓN CRÍTICA: Para órdenes mixtas, SIEMPRE priorizar selecciones originales
        // Los datos editados en customData suelen tener cantidades incorrectas (todas 1)
        
        // Detectar si es orden mixta o si se pasaron selecciones
        $hasMixedItems = $providerSelections !== null || 
                        QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre ?? '');
                        })->exists() || 
                        ($order->additional_items && count($order->additional_items) > 0);

        // 🔧 NUEVA PRIORIDAD 1: ÓRDENES MIXTAS - Usar selecciones originales SIEMPRE
        if ($hasMixedItems) {
            Log::info('🎯 ORDEN MIXTA DETECTADA - Priorizando selecciones originales', [
                'order_id' => $order->id,
                'ignoring_edited_data' => $hasRecentCustomData
            ]);
            
            // Usar selecciones pasadas como parámetro o buscarlas usando la relación
            if ($providerSelections && $providerSelections->count() > 0) {
                $data['quotationItemSelections'] = $providerSelections;
                Log::info('✅ Usando selecciones pasadas como parámetro', [
                    'order_id' => $order->id,
                    'selections_count' => $providerSelections->count()
                ]);
            } else {
                // Cargar la relación y obtener las selecciones
                $order->load(['provider', 'purchaseRequest.quotationItemSelections.quotation']);
                $selections = $this->getQuotationItemSelections($order);
                
                // Si no hay selecciones de BD pero hay additional_items, usar esos
                if ($selections->count() === 0 && $order->additional_items && count($order->additional_items) > 0) {
                    // Convertir additional_items a formato compatible con selecciones
                    $additionalItems = collect($order->additional_items)->map(function($item, $index) {
                        return (object) [
                            'id' => 'additional_' . $index,
                            'item_description' => $item['description'] ?? 'N/A',
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_price' => $item['total_price'] ?? 0,
                            'unit' => $item['unit'] ?? 'Unidad',
                            'observations' => $item['observations'] ?? ''
                        ];
                    });
                    $data['quotationItemSelections'] = $additionalItems;
                    Log::info('✅ Usando additional_items como selecciones', [
                        'order_id' => $order->id,
                        'items_count' => $additionalItems->count()
                    ]);
                } else {
                    $data['quotationItemSelections'] = $selections;
                    Log::info('✅ Usando selecciones desde base de datos', [
                        'order_id' => $order->id,
                        'selections_count' => $selections->count()
                    ]);
                }
            }
            
        // 🔧 PRIORIDAD 2: Si hay datos personalizados más recientes Y NO es mixta
        } elseif ($hasRecentCustomData) {
            Log::info('🎯 Usando datos editados recientes de pdf_custom_data (orden NO mixta)', [
                'order_id' => $order->id,
                'items_count' => count($customData['items'])
            ]);
            
            // Convertir customData items a formato de selecciones para compatibilidad
            $customItems = collect($customData['items'])->map(function($item, $index) {
                // 🔧 MEJORAR MANEJO DE DESCRIPCIONES VACÍAS
                $description = trim($item['description'] ?? '');
                if (empty($description)) {
                    $description = 'Descripción pendiente de editar';
                }
                
                return (object) [
                    'id' => 'custom_edited_' . $index,
                    'item_description' => $description,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total_price' => $item['total'] ?? ($item['quantity'] * $item['unit_price']),
                    'unit' => $item['unit'] ?? 'Unidad',
                    'observations' => $item['observations'] ?? ''
                ];
            });
            
            $data['quotationItemSelections'] = $customItems;
        } else {
            // 🔧 PARA ÓRDENES NO MIXTAS: Cargar items desde pdf_custom_data
            Log::info('Procesando orden NO MIXTA - buscando items en pdf_custom_data', [
                'order_id' => $order->id,
                'has_custom_data' => !empty($customData),
                'custom_data_is_array' => is_array($customData)
            ]);
            
            if (!empty($customData) && is_array($customData) && isset($customData['items'])) {
                // Convertir customData items a formato de selecciones para compatibilidad
                $customItems = collect($customData['items'])->map(function($item, $index) {
                    return (object) [
                        'id' => 'custom_' . $index,
                        'item_description' => $item['description'] ?? 'N/A',
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'total_price' => $item['total'] ?? ($item['quantity'] * $item['unit_price']),
                        'unit' => $item['unit'] ?? 'Unidad',
                        'observations' => $item['observations'] ?? ''
                    ];
                });
                
                $data['quotationItemSelections'] = $customItems;
                Log::info('✅ Items cargados desde pdf_custom_data para orden no mixta', [
                    'order_id' => $order->id,
                    'items_count' => $customItems->count(),
                    'sample_item' => $customItems->first()
                ]);
            } else {
                Log::warning('⚠️ Orden no mixta sin pdf_custom_data válido', [
                    'order_id' => $order->id,
                    'custom_data' => $customData
                ]);
                // Asignar colección vacía para evitar errores en la vista
                $data['quotationItemSelections'] = collect();
            }
        }

        // Generar PDF usando plantilla unificada
        $pdf = Pdf::loadView('purchase-orders.pdf-template-custom', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Generar nombre del archivo
        $filename = 'orden_compra_' . $order->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = 'purchase_orders/' . $filename;

        // Guardar archivo
        Storage::disk('public')->put($filepath, $pdf->output());

        Log::info('PDF generado exitosamente', [
            'order_id' => $order->id,
            'filename' => $filename,
            'filepath' => $filepath
        ]);

        // Retornar solo la ruta del archivo para compatibilidad con el controlador
        return $filepath;
    }

    /**
     * Genera PDF y retorna el objeto PDF (para notificaciones)
     */
    public function createPdf(PurchaseOrder $order, $providerSelections = null)
    {
        Log::info('Generando PDF para notificación', ['order_id' => $order->id]);

        // Cargar relaciones necesarias
        $order->load(['provider', 'purchaseRequest']);

        // Obtener datos personalizados si existen
        $customData = $order->pdf_custom_data ? json_decode($order->pdf_custom_data, true) : [];

        // Preparar datos para la vista
        $data = [
            'order' => $order,
            'customData' => $customData
        ];

        // Agregar totales correctos desde customData si están disponibles
        if (isset($customData['subtotal'])) {
            $data['subtotal'] = $customData['subtotal'];
        }
        if (isset($customData['iva_amount'])) {
            $data['ivaAmount'] = $customData['iva_amount'];
        }
        if (isset($customData['total'])) {
            $data['totalAmount'] = $customData['total'];
        }

        // Si no hay customData pero hay cotización seleccionada, calcular totales dinámicamente
        if (empty($customData) && $order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
            $this->calculateDynamicTotals($order, $data);
        }

        // Agregar información de presupuesto compartido si existe
        if ($order->purchaseRequest) {
            $purchaseRequest = $order->purchaseRequest;
            
            // Detectar si es compra compartida
            $data['isSharedPurchase'] = $purchaseRequest->is_shared;
            
            if ($purchaseRequest->is_shared) {
                $sharedSections = [];
                
                // Agregar sección principal (la que hace la orden) si existe
                if ($purchaseRequest->section_area) {
                    $sharedSections[] = $purchaseRequest->section_area;
                }
                
                // Agregar sección compartida si existe
                if ($purchaseRequest->shared_section) {
                    $sharedSections[] = $purchaseRequest->shared_section;
                }
                
                // Agregar tercera sección si existe
                if ($purchaseRequest->third_shared_section) {
                    $sharedSections[] = $purchaseRequest->third_shared_section;
                }
                
                $data['sharedSections'] = $sharedSections;
                $data['sharedBudget'] = $purchaseRequest->shared_budget;
                $data['thirdSharedBudget'] = $purchaseRequest->third_shared_budget;
            }
            
            // Agregar observaciones del PurchaseRequest si no están en customData
            if (!isset($customData['observations']) && $purchaseRequest->observations) {
                $data['purchaseRequestObservations'] = $purchaseRequest->observations;
            }
        }

        // Detectar si es orden mixta o si se pasaron selecciones
        $hasMixedItems = $providerSelections !== null || 
                        QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre ?? '');
                        })->exists() || 
                        ($order->additional_items && count($order->additional_items) > 0);

        if ($hasMixedItems) {
            // Usar selecciones pasadas como parámetro o buscarlas usando la relación
            if ($providerSelections && $providerSelections->count() > 0) {
                $data['quotationItemSelections'] = $providerSelections;
                Log::info('Usando selecciones pasadas como parámetro (createPdf)', [
                    'order_id' => $order->id,
                    'selections_count' => $providerSelections->count()
                ]);
            } else {
                // Cargar la relación y obtener las selecciones
                $order->load(['provider', 'purchaseRequest.quotationItemSelections.quotation']);
                $selections = $this->getQuotationItemSelections($order);
                
                // Si no hay selecciones de BD pero hay additional_items, usar esos
                if ($selections->count() === 0 && $order->additional_items && count($order->additional_items) > 0) {
                    // Convertir additional_items a formato compatible con selecciones
                    $additionalItems = collect($order->additional_items)->map(function($item, $index) {
                        return (object) [
                            'id' => 'additional_' . $index,
                            'item_description' => $item['description'] ?? 'N/A',
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_price' => $item['total_price'] ?? 0,
                            'unit' => $item['unit'] ?? 'Unidad',
                            'observations' => $item['observations'] ?? ''
                        ];
                    });
                    $data['quotationItemSelections'] = $additionalItems;
                    Log::info('Usando additional_items como selecciones (createPdf)', [
                        'order_id' => $order->id,
                        'items_count' => $additionalItems->count()
                    ]);
                } else {
                    $data['quotationItemSelections'] = $selections;
                    Log::info('Obteniendo selecciones desde base de datos (createPdf)', [
                        'order_id' => $order->id,
                        'selections_count' => $selections->count()
                    ]);
                }
            }
        }

        // Generar y retornar PDF sin guardar
        $pdf = Pdf::loadView('purchase-orders.pdf-template-custom', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        Log::info('PDF para notificación generado exitosamente', ['order_id' => $order->id]);

        return $pdf;
    }

    /**
     * Obtiene las selecciones de cotización para orden mixta
     */
    private function getQuotationItemSelections(PurchaseOrder $order)
    {
        if (!$order->purchaseRequest) {
            Log::warning('Orden sin solicitud de compra asignada', ['order_id' => $order->id]);
            return collect();
        }

        // ✅ CORREGIDO: Buscar selecciones específicas de esta solicitud y proveedor
        $allSelections = QuotationItemSelection::with(['quotation'])
            ->where('purchase_request_id', $order->purchaseRequest->id)
            ->whereHas('quotation', function($query) use ($order) {
                if ($order->provider) {
                    $query->where('provider_name', $order->provider->nombre);
                }
            })
            ->get();

        Log::info('Selecciones de cotización obtenidas', [
            'order_id' => $order->id,
            'purchase_request_id' => $order->purchaseRequest->id,
            'provider_name' => $order->provider ? $order->provider->nombre : 'N/A',
            'selections_count' => $allSelections->count(),
            'selections_debug' => $allSelections->map(function($sel) {
                return [
                    'id' => $sel->id,
                    'item_description' => $sel->item_description,
                    'quantity' => $sel->quantity,
                    'unit_price' => $sel->unit_price,
                    'total_price' => $sel->total_price,
                    'quotation_id' => $sel->quotation_id,
                    'quotation_provider' => $sel->quotation ? $sel->quotation->provider_name : 'N/A'
                ];
            })
        ]);

        return $allSelections;
    }

    /**
     * Genera PDF personalizado (alias del método principal)
     */
    public function generateCustomPdf(PurchaseOrder $order, $providerSelections = null)
    {
        return $this->generatePdf($order, $providerSelections);
    }

    /**
     * Calcula totales dinámicamente desde los items de la cotización seleccionada
     * Consistente con la lógica del controlador: asume que los precios incluyen IVA
     */
    private function calculateDynamicTotals(PurchaseOrder $order, &$data)
    {
        $purchaseRequest = $order->purchaseRequest;
        $quotation = $purchaseRequest->selectedQuotation;
        
        if (!$quotation || !$purchaseRequest->purchase_items) {
            Log::warning('No se puede calcular totales dinámicamente', [
                'order_id' => $order->id,
                'has_quotation' => !!$quotation,
                'has_items' => !!$purchaseRequest->purchase_items
            ]);
            return;
        }
        
        $items = $purchaseRequest->purchase_items;
        $prices = $quotation->original_item_prices ?? $quotation->item_prices ?? [];
        
        $totalWithIva = 0;
        
        foreach ($items as $index => $item) {
            $unitPrice = 0;
            
            // Obtener precio si existe para este índice
            if (is_array($prices) && isset($prices[$index])) {
                $unitPrice = floatval($prices[$index]);
            }
            
            $quantity = floatval($item['quantity'] ?? 1);
            $itemTotal = $unitPrice * $quantity;
            $totalWithIva += $itemTotal;
        }
        
        // CORREGIDO: Verificar impuestos específicos de la cotización
        $hasSpecificTaxes = ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) ||
                           ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) ||
                           ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) ||
                           ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0);
        
        if ($hasSpecificTaxes) {
            // Usar impuestos específicos de la cotización
            $ivaAmount = 0;
            if ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) {
                $ivaAmount += $quotation->iva_19_amount;
            }
            if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                $ivaAmount += $quotation->iva_5_amount;
            }
            // Agregar otros impuestos si fuera necesario
            
            $subtotal = $totalWithIva - $ivaAmount;
            $totalAmount = $totalWithIva;
        } else {
            // Sin impuestos específicos - no calcular IVA automáticamente
            $subtotal = $totalWithIva;
            $ivaAmount = 0;
            $totalAmount = $totalWithIva;
        }
        
        // Asignar los valores calculados
        $data['subtotal'] = $subtotal;
        $data['ivaAmount'] = $ivaAmount;
        $data['totalAmount'] = $totalAmount;
        
        Log::info('Totales calculados dinámicamente', [
            'order_id' => $order->id,
            'includes_iva' => $includesIva,
            'items_count' => count($items),
            'total_with_iva' => $totalWithIva,
            'subtotal' => $subtotal,
            'iva' => $ivaAmount,
            'total' => $totalAmount
        ]);
    }
}
