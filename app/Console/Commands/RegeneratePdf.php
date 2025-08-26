<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Support\Facades\Log;

class RegeneratePdf extends Command
{
    protected $signature = 'app:regenerate-pdf {order_id}';
    protected $description = 'Regenera el PDF para una orden específica';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $this->info("Buscando orden de compra con ID: $orderId");
        
        try {
            // Buscar la orden
            $order = PurchaseOrder::findOrFail($orderId);
            
            $this->info("Orden encontrada: {$order->order_number}");
            
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
            Log::error("Error en RegeneratePdf: " . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
