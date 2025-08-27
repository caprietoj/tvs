<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixPurchaseOrderPrices extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'orders:fix-pdf-prices {order_id? : ID específico de orden para corregir} {--all : Corregir todas las órdenes}';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Corrige problemas de redondeo en los precios de las órdenes de compra';

    /**
     * Ejecuta el comando de consola.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        $processAll = $this->option('all');
        
        if ($orderId) {
            // Corregir una orden específica
            $order = PurchaseOrder::find($orderId);
            if (!$order) {
                $this->error("Orden no encontrada con ID: $orderId");
                return 1;
            }
            
            $this->info("Procesando orden #{$order->order_number} (ID: {$order->id})");
            $result = $this->fixOrderPrices($order);
            
            if ($result) {
                $this->info("Corrección completada para la orden #{$order->order_number}");
            } else {
                $this->warn("No se realizaron correcciones en la orden #{$order->order_number}");
            }
        } elseif ($processAll) {
            // Corregir todas las órdenes
            $orders = PurchaseOrder::whereNotNull('pdf_custom_data')->get();
            $this->info("Procesando {$orders->count()} órdenes de compra");
            
            $fixedCount = 0;
            $errorCount = 0;
            
            foreach ($orders as $order) {
                $this->line("Procesando orden #{$order->order_number} (ID: {$order->id})");
                try {
                    $result = $this->fixOrderPrices($order);
                    if ($result) {
                        $fixedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error en orden #{$order->order_number}: " . $e->getMessage());
                    $errorCount++;
                }
            }
            
            $this->info("Proceso completado: $fixedCount órdenes corregidas, $errorCount errores");
        } else {
            $this->info("Por favor especifique un ID de orden o use la opción --all para procesar todas las órdenes");
        }
        
        return 0;
    }
    
    /**
     * Corrige los problemas de redondeo en los precios de una orden
     * 
     * @param PurchaseOrder $order
     * @return bool
     */
    protected function fixOrderPrices(PurchaseOrder $order)
    {
        // Obtener datos personalizados
        $customData = json_decode($order->pdf_custom_data, true) ?? [];
        
        if (empty($customData) || !isset($customData['items']) || empty($customData['items'])) {
            $this->line("  La orden no tiene datos personalizados o items");
            return false;
        }
        
        $items = $customData['items'];
        $itemsFixed = 0;
        
        // Corregir redondeo en cada item
        foreach ($items as &$item) {
            if (!isset($item['unit_price']) || !isset($item['quantity'])) {
                continue;
            }
            
            $unitPrice = floatval($item['unit_price']);
            $quantity = floatval($item['quantity']);
            $oldTotal = isset($item['total']) ? floatval($item['total']) : ($quantity * $unitPrice);
            
            // Calcular el total correcto con redondeo
            $newTotal = round($quantity * $unitPrice);
            
            if (abs($newTotal - $oldTotal) > 0.01) {  // Si hay diferencia significativa
                $item['total'] = $newTotal;
                $this->line("  ✓ Corregido: {$item['description']} - Total: " . 
                    number_format($oldTotal, 2, ',', '.') . " -> " . 
                    number_format($newTotal, 0, ',', '.'));
                $itemsFixed++;
            }
        }
        
        if ($itemsFixed > 0) {
            // Recalcular subtotal
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['total'] ?? 0;
            }
            
            // Recalcular impuestos con redondeo
            $ivaRate = intval(str_replace('%', '', $customData['iva_rate'] ?? '0'));
            $ipoconsumoRate = intval(str_replace('%', '', $customData['ipoconsumo_rate'] ?? '0'));
            
            $ivaAmount = ($ivaRate > 0) ? round(($subtotal * $ivaRate) / 100) : 0;
            $ipoconsumoAmount = ($ipoconsumoRate > 0) ? round(($subtotal * $ipoconsumoRate) / 100) : 0;
            $totalAmount = $subtotal + $ivaAmount + $ipoconsumoAmount;
            
            // Actualizar datos personalizados
            $customData['items'] = $items;
            $customData['subtotal'] = $subtotal;
            $customData['iva_amount'] = $ivaAmount;
            $customData['ipoconsumo_amount'] = $ipoconsumoAmount;
            $customData['total'] = $totalAmount;
            
            // Actualizar orden
            $order->pdf_custom_data = json_encode($customData);
            $order->subtotal = $subtotal;
            $order->iva_amount = $ivaAmount;
            $order->tax_amount = $ivaAmount + $ipoconsumoAmount;
            $order->total_amount = $totalAmount;
            $order->save();
            
            $this->line("  ✓ Orden actualizada con {$itemsFixed} items corregidos");
            $this->line("  ✓ Subtotal: " . number_format($subtotal, 0, ',', '.') . ", Total: " . number_format($totalAmount, 0, ',', '.'));
            
            // Regenerar PDF
            try {
                $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($order);
                $order->file_path = $pdfPath;
                $order->save();
                
                $this->line("  ✓ PDF regenerado: {$pdfPath}");
            } catch (\Exception $e) {
                $this->error("  ! Error al regenerar PDF: " . $e->getMessage());
                Log::error("Error al regenerar PDF: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'exception' => $e
                ]);
            }
            
            return true;
        }
        
        $this->line("  No se encontraron problemas de redondeo en esta orden");
        return false;
    }
}
