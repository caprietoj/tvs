<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Storage;

class CheckOrderPaths extends Command
{
    protected $signature = 'check:order-paths';
    protected $description = 'Check purchase order PDF paths';

    public function handle()
    {
        $orders = PurchaseOrder::all();
        
        $this->info('Verificando rutas de archivos PDF de órdenes de compra:');
        $this->newLine();
        
        foreach ($orders as $order) {
            $this->info("Orden #{$order->id} - {$order->order_number}");
            $this->line("  Ruta almacenada: {$order->file_path}");
            
            // Verificar si el archivo existe en storage
            $exists = Storage::exists($order->file_path);
            $this->line("  Archivo existe: " . ($exists ? '✅ Sí' : '❌ No'));
            
            // Mostrar URL de visualización
            $viewUrl = route('purchase-orders.view', $order->id);
            $this->line("  URL de visualización: {$viewUrl}");
            
            // Mostrar URL de storage (problemática)
            $storageUrl = url('/storage/' . $order->file_path);
            $this->line("  URL de storage directa: {$storageUrl}");
            
            $this->newLine();
        }
        
        return 0;
    }
}
