<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Storage;

class FixPdfPaths extends Command
{
    protected $signature = 'fix:pdf-paths';
    protected $description = 'Fix PDF file paths for purchase orders';

    public function handle()
    {
        $this->info('Iniciando corrección de rutas PDF...');
        
        // Obtener todas las órdenes que puedan tener problemas
        $orders = PurchaseOrder::whereNotNull('file_path')
                               ->where('file_path', '!=', 'pending_generation')
                               ->get();
        
        $fixed = 0;
        $errors = 0;
        
        foreach ($orders as $order) {
            // Verificar si el archivo existe en public disk
            if (!Storage::disk('public')->exists($order->file_path)) {
                $this->warn("Orden #{$order->order_number}: Archivo no encontrado en {$order->file_path}");
                
                // Buscar archivos PDF existentes para esta orden
                $files = Storage::disk('public')->files('purchase_orders');
                $orderFiles = [];
                
                foreach ($files as $file) {
                    if (strpos($file, $order->order_number) !== false) {
                        $orderFiles[] = $file;
                    }
                }
                
                if (!empty($orderFiles)) {
                    // Obtener el más reciente
                    $latestFile = end($orderFiles);
                    $order->update(['file_path' => $latestFile]);
                    
                    $this->info("✅ Orden #{$order->order_number}: Path actualizado a {$latestFile}");
                    $fixed++;
                } else {
                    $this->error("❌ Orden #{$order->order_number}: No se encontraron archivos PDF");
                    $errors++;
                }
            } else {
                $this->line("✓ Orden #{$order->order_number}: OK");
            }
        }
        
        $this->info("\n📊 Resumen:");
        $this->info("- Órdenes procesadas: " . count($orders));
        $this->info("- Rutas corregidas: {$fixed}");
        $this->info("- Errores: {$errors}");
        
        if ($fixed > 0) {
            $this->info("\n🎉 Corrección completada exitosamente");
        }
    }
}
