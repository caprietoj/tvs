<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para corregir problemas en las órdenes de compra
 */
class PurchaseOrderFixService
{
    /**
     * Corrige los precios en una orden de compra
     * 
     * @param PurchaseOrder $order Orden de compra a corregir
     * @return bool Indica si se realizaron correcciones
     */
    public function fixOrderPrices(PurchaseOrder $order)
    {
        try {
            // Cargar relaciones necesarias
            $order->load(['purchaseRequest.selectedQuotation', 'purchaseRequest.quotationItemSelections.quotation']);
            
            // Verificar si hay datos personalizados
            if (empty($order->pdf_custom_data)) {
                Log::info("La orden #{$order->order_number} no tiene datos personalizados para corregir");
                return false;
            }
            
            $customData = json_decode($order->pdf_custom_data, true);
            if (!isset($customData['items']) || empty($customData['items'])) {
                Log::info("La orden #{$order->order_number} no tiene items en sus datos personalizados");
                return false;
            }
            
            // Crear una copia de los items originales para comparación
            $originalItems = $customData['items'];
            $items = &$customData['items'];
            $itemsFixed = 0;
            
            // Obtener los precios correctos
            $purchaseRequest = $order->purchaseRequest;
            $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
            $providerSelections = collect();
            $originalPrices = [];
            
            // Determinar la fuente de precios correctos
            if ($hasMixedSelection) {
                // Para órdenes con selección mixta
                $providerSelections = $purchaseRequest->quotationItemSelections()
                    ->whereHas('quotation', function($query) use ($order) {
                        $query->where('provider_name', $order->provider->nombre);
                    })
                    ->with('quotation')
                    ->get();
            } elseif ($purchaseRequest->selectedQuotation) {
                // Para órdenes regulares con cotización seleccionada
                $selectedQuotation = $purchaseRequest->selectedQuotation;
                
                if (isset($selectedQuotation->original_item_prices)) {
                    $originalPrices = $selectedQuotation->original_item_prices;
                }
            }
            
            // Corregir cada item basado en la fuente de precios
            foreach ($items as $index => &$item) {
                $price = null;
                $description = $item['description'] ?? '';
                $quantity = floatval($item['quantity'] ?? 1);
                
                if ($hasMixedSelection) {
                    // Buscar precio en selecciones mixtas
                    foreach ($providerSelections as $selection) {
                        if (trim(strtolower($selection->item_description)) === trim(strtolower($description))) {
                            if ($selection->quotation && isset($selection->quotation->original_item_prices[$selection->item_index])) {
                                $price = $selection->quotation->original_item_prices[$selection->item_index];
                            }
                            break;
                        }
                    }
                } elseif (!empty($originalPrices) && isset($originalPrices[$index])) {
                    // Usar precio de la cotización seleccionada
                    $price = $originalPrices[$index];
                }
                
                // Si encontramos el precio correcto, aplicarlo
                if ($price !== null) {
                    $oldPrice = $item['unit_price'] ?? 0;
                    $oldTotal = $item['total'] ?? 0;
                    
                    // Aplicar correcciones
                    $item['unit_price'] = floatval($price);
                    $item['quantity'] = $quantity;
                    $item['total'] = round($quantity * $price);
                    
                    $itemsFixed++;
                    Log::info("Corregido item #{$index} ({$description}): Precio {$oldPrice} -> {$price}, Total {$oldTotal} -> {$item['total']}");
                }
            }
            
            // Si se corrigieron items, recalcular totales
            if ($itemsFixed > 0) {
                // Calcular subtotal
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += floatval($item['total'] ?? 0);
                }
                
                // Calcular impuestos
                $ivaRate = intval(str_replace('%', '', $customData['iva_rate'] ?? '0'));
                $ipoconsumoRate = intval(str_replace('%', '', $customData['ipoconsumo_rate'] ?? '0'));
                
                // Si el IVA está en 0%, aplicar automáticamente el 19% estándar
                if ($ivaRate == 0) {
                    $ivaRate = 19;
                    $customData['iva_rate'] = '19%';
                    $this->output?->writeln("  ! IVA corregido de 0% a 19% estándar");
                }
                
                $ivaAmount = ($ivaRate > 0) ? round(($subtotal * $ivaRate) / 100) : 0;
                $ipoconsumoAmount = ($ipoconsumoRate > 0) ? round(($subtotal * $ipoconsumoRate) / 100) : 0;
                $totalAmount = $subtotal + $ivaAmount + $ipoconsumoAmount;
                
                // Actualizar datos personalizados
                $customData['subtotal'] = $subtotal;
                $customData['iva_amount'] = $ivaAmount;
                $customData['ipoconsumo_amount'] = $ipoconsumoAmount;
                $customData['total'] = $totalAmount;
                
                // Actualizar la orden
                $order->pdf_custom_data = json_encode($customData);
                $order->subtotal = $subtotal;
                $order->iva_amount = $ivaAmount;
                $order->tax_amount = $ivaAmount + $ipoconsumoAmount;
                $order->total_amount = $totalAmount;
                $order->save();
                
                // Regenerar PDF
                $pdfService = app(PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($order);
                $order->file_path = $pdfPath;
                $order->save();
                
                Log::info("Orden #{$order->order_number} corregida con éxito: {$itemsFixed} items actualizados, subtotal: {$subtotal}, total: {$totalAmount}");
                return true;
            }
            
            Log::info("No se encontraron problemas a corregir en la orden #{$order->order_number}");
            return false;
            
        } catch (\Exception $e) {
            Log::error("Error corrigiendo orden #{$order->order_number}: " . $e->getMessage(), [
                'exception' => $e,
                'order_id' => $order->id
            ]);
            return false;
        }
    }
}
