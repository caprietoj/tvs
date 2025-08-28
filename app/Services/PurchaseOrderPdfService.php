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
     */
    private function calculateDynamicTotals(PurchaseOrder $order, &$data)
    {
        $purchaseRequest = $order->purchaseRequest;
        $quotation = $purchaseRequest->selectedQuotation;
        
        if (!$quotation || !$purchaseRequest->purchase_items) {
            return;
        }
        
        $items = $purchaseRequest->purchase_items;
        $prices = $quotation->original_item_prices ?? $quotation->item_prices ?? [];
        
        $subtotal = 0;
        
        foreach ($items as $index => $item) {
            $unitPrice = 0;
            
            // Obtener precio si existe para este índice
            if (is_array($prices) && isset($prices[$index])) {
                $unitPrice = $prices[$index];
            }
            
            $quantity = $item['quantity'] ?? 1;
            $itemTotal = $unitPrice * $quantity;
            $subtotal += $itemTotal;
        }
        
        $ivaAmount = $subtotal * 0.19;
        $totalAmount = $subtotal + $ivaAmount;
        
        // Asignar los valores calculados
        $data['subtotal'] = $subtotal;
        $data['ivaAmount'] = $ivaAmount;
        $data['totalAmount'] = $totalAmount;
        
        Log::info('Totales calculados dinámicamente', [
            'order_id' => $order->id,
            'subtotal' => $subtotal,
            'iva' => $ivaAmount,
            'total' => $totalAmount
        ]);
    }
}
