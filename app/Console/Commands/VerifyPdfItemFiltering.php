<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class VerifyPdfItemFiltering extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:verify-filtering {order_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica que el filtrado de items en PDFs de órdenes con selección mixta esté funcionando correctamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        
        if ($orderNumber) {
            $this->verifySpecificOrder($orderNumber);
        } else {
            $this->verifyAllMixedOrders();
        }
        
        return 0;
    }
    
    private function verifySpecificOrder($orderNumber)
    {
        $order = PurchaseOrder::where('order_number', $orderNumber)
            ->with(['purchaseRequest.quotationItemSelections.quotation', 'provider'])
            ->first();
            
        if (!$order) {
            $this->error("Orden {$orderNumber} no encontrada");
            return;
        }
        
        $this->info("Verificando orden: {$order->order_number}");
        $this->analyzeOrder($order);
    }
    
    private function verifyAllMixedOrders()
    {
        $mixedOrders = PurchaseOrder::whereHas('purchaseRequest.quotationItemSelections')
            ->with(['purchaseRequest.quotationItemSelections.quotation', 'provider'])
            ->get();
            
        if ($mixedOrders->isEmpty()) {
            $this->info('No se encontraron órdenes con selección mixta');
            return;
        }
        
        $this->info("Verificando {$mixedOrders->count()} órdenes con selección mixta:");
        $this->line('');
        
        foreach ($mixedOrders as $order) {
            $this->analyzeOrder($order);
            $this->line('');
        }
    }
    
    private function analyzeOrder($order)
    {
        $purchaseRequest = $order->purchaseRequest;
        
        // Contar selecciones totales y del proveedor
        $totalSelections = $purchaseRequest->quotationItemSelections()->count();
        $providerSelections = $purchaseRequest->quotationItemSelections()
            ->whereHas('quotation', function($query) use ($order) {
                $query->where('provider_name', $order->provider->nombre);
            })
            ->count();
            
        // Verificar datos del PDF personalizado
        $customData = json_decode($order->pdf_custom_data ?? '{}', true);
        $pdfItems = $customData['items'] ?? [];
        
        $this->line("Orden: <info>{$order->order_number}</info>");
        $this->line("Proveedor: <info>{$order->provider->nombre}</info>");
        $this->line("Selecciones totales en BD: <comment>{$totalSelections}</comment>");
        $this->line("Selecciones del proveedor: <comment>{$providerSelections}</comment>");
        $this->line("Items en PDF personalizado: <comment>" . count($pdfItems) . "</comment>");
        
        // Verificar si hay problema
        if (count($pdfItems) > $providerSelections && $providerSelections > 0) {
            $this->error("❌ PROBLEMA: El PDF tiene más items (" . count($pdfItems) . ") que las selecciones del proveedor ({$providerSelections})");
            $this->line("Esto indica que se guardaron items de otros proveedores");
        } elseif (count($pdfItems) === $providerSelections) {
            $this->info("✅ CORRECTO: El PDF tiene exactamente los items del proveedor ({$providerSelections})");
        } elseif (count($pdfItems) === 0) {
            $this->line("⚪ SIN DATOS: No hay datos personalizados en el PDF");
        } else {
            $this->warn("⚠️  REVISAR: Items en PDF: " . count($pdfItems) . ", Selecciones del proveedor: {$providerSelections}");
        }
        
        // Mostrar algunos items si existen
        if (!empty($pdfItems)) {
            $this->line("Primeros items en PDF:");
            foreach (array_slice($pdfItems, 0, 3) as $index => $item) {
                $description = $item['description'] ?? 'Sin descripción';
                $this->line("  " . ($index + 1) . ". {$description}");
            }
            if (count($pdfItems) > 3) {
                $this->line("  ... y " . (count($pdfItems) - 3) . " más");
            }
        }
    }
}
