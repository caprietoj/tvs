<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Support\Facades\Log;

class FixTaxCalculation extends Command
{
    protected $signature = 'app:fix-tax-calculation {order_id}';
    protected $description = 'Corrige el cálculo de impuestos en una orden y regenera su PDF';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $this->info("Buscando orden de compra con ID: $orderId");
        
        try {
            // Buscar la orden
            $order = PurchaseOrder::findOrFail($orderId);
            
            $this->info("Orden encontrada: {$order->order_number}");
            
            // Verificar datos personalizados
            $customData = json_decode($order->pdf_custom_data, true);
            if (empty($customData)) {
                $this->error("La orden no tiene datos personalizados. Ejecute primero app:reset-pdf-custom-data.");
                return 1;
            }
            
            // Verificar que tenemos los datos necesarios
            $subtotal = $customData['subtotal'] ?? 0;
            $ivaAmount = $customData['iva_amount'] ?? 0;
            $total = $customData['total'] ?? 0;
            
            $this->info("Datos del PDF:");
            $this->info("- Subtotal: $subtotal");
            $this->info("- IVA: $ivaAmount");
            $this->info("- Total: $total");
            
            // Verificar si los cálculos son correctos
            if ($subtotal + $ivaAmount != $total) {
                $this->warn("Los cálculos no son correctos. Se ajustarán los valores.");
                
                // Ajustar valores según total bruto
                $totalBruto = $total;
                $subtotalCalculado = round($totalBruto / 1.19, 0);
                $ivaCalculado = $totalBruto - $subtotalCalculado;
                
                $this->info("Valores ajustados:");
                $this->info("- Subtotal ajustado: $subtotalCalculado");
                $this->info("- IVA ajustado: $ivaCalculado");
                $this->info("- Total (sin cambios): $totalBruto");
                
                // Actualizar valores en customData
                $customData['subtotal'] = $subtotalCalculado;
                $customData['iva_amount'] = $ivaCalculado;
                $customData['iva_rate'] = '19%';
                
                // Guardar cambios
                $order->pdf_custom_data = json_encode($customData);
                $order->subtotal = $subtotalCalculado;
                $order->iva_amount = $ivaCalculado;
                $order->includes_iva = true;
                $order->save();
                
                $this->info("Datos actualizados en la base de datos.");
            }
            
            // Regenerar el PDF
            $service = new PurchaseOrderPdfService();
            $path = $service->generatePdf($order);
            
            // Actualizar la ruta del archivo
            $order->file_path = $path;
            $order->updated_at = now();
            $order->save();
            
            $this->info("PDF regenerado exitosamente y guardado en: $path");
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Orden de compra con ID $orderId no encontrada.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Error en FixTaxCalculation: " . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
