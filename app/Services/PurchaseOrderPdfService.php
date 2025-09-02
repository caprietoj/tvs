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

        // Detectar si es orden mixta o si se pasaron selecciones
        $hasMixedItems = $providerSelections !== null || 
                        QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre ?? '');
                        })->exists();

        if ($hasMixedItems) {
            // Usar selecciones pasadas como parámetro o buscarlas
            $data['quotationItemSelections'] = $providerSelections ?: $this->getQuotationItemSelections($order);
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
                        })->exists();

        if ($hasMixedItems) {
            // Usar selecciones pasadas como parámetro o buscarlas
            $data['quotationItemSelections'] = $providerSelections ?: $this->getQuotationItemSelections($order);
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
            'selections_count' => $allSelections->count()
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
        
        // Verificar si la cotización especifica si incluye IVA
        $includesIva = $quotation->includes_iva ?? true;
        
        if ($includesIva) {
            // Los precios ya incluyen IVA (19%)
            $subtotal = round($totalWithIva / 1.19, 2);
            $ivaAmount = round($totalWithIva - $subtotal, 2);
            $totalAmount = $totalWithIva;
        } else {
            // Los precios no incluyen IVA
            $subtotal = $totalWithIva;
            $ivaAmount = round($totalWithIva * 0.19, 2);
            $totalAmount = $subtotal + $ivaAmount;
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
