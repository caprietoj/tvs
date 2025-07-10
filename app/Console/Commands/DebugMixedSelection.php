<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\QuotationItemSelection;

class DebugMixedSelection extends Command
{
    protected $signature = 'debug:mixed-selection {request_id}';
    protected $description = 'Debugea una solicitud específica para ver su estado de selección mixta';

    public function handle()
    {
        $requestId = $this->argument('request_id');
        
        $purchaseRequest = PurchaseRequest::find($requestId);
        
        if (!$purchaseRequest) {
            $this->error("No se encontró la solicitud con ID: {$requestId}");
            return;
        }
        
        $this->info("=== INFORMACIÓN DE LA SOLICITUD ===");
        $this->info("ID: {$purchaseRequest->id}");
        $this->info("Estado: {$purchaseRequest->status}");
        $this->info("Tipo: {$purchaseRequest->request_type}");
        $this->info("Es servicio sin cotización: " . ($purchaseRequest->isNoQuotationService() ? 'SÍ' : 'NO'));
        
        // Verificar selecciones mixtas
        $selections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
        $this->info("\n=== SELECCIONES MIXTAS ===");
        $this->info("Total de selecciones: {$selections->count()}");
        
        if ($selections->count() > 0) {
            $this->info("\nDetalle de selecciones:");
            foreach ($selections as $selection) {
                $this->info("- ID: {$selection->id}");
                $this->info("  Quotation ID: {$selection->quotation_id}");
                $this->info("  Proveedor: " . ($selection->quotation->provider_name ?? 'N/A'));
                $this->info("  Item: {$selection->item_description}");
                $this->info("  Cantidad: {$selection->quantity}");
                $this->info("  Precio unitario: $" . number_format($selection->unit_price, 0));
                $this->info("  Total: $" . number_format($selection->total_price, 0));
                $this->info("");
            }
            
            // Agrupar por proveedor
            $grouped = $selections->groupBy('quotation_id');
            $this->info("=== AGRUPACIÓN POR PROVEEDOR ===");
            $this->info("Número de proveedores: " . $grouped->count());
            
            foreach ($grouped as $quotationId => $providerSelections) {
                $provider = $providerSelections->first()->quotation->provider_name ?? 'N/A';
                $total = $providerSelections->sum('total_price');
                $this->info("- Proveedor: {$provider} (Quotation ID: {$quotationId})");
                $this->info("  Items: " . $providerSelections->count());
                $this->info("  Total: $" . number_format($total, 0));
            }
        }
        
        // Verificar órdenes de compra existentes
        $orders = $purchaseRequest->purchaseOrders()->get();
        $this->info("\n=== ÓRDENES DE COMPRA EXISTENTES ===");
        $this->info("Total de órdenes: {$orders->count()}");
        
        foreach ($orders as $order) {
            $this->info("- Orden ID: {$order->id}");
            $this->info("  Número: {$order->order_number}");
            $this->info("  Proveedor: " . ($order->provider->name ?? 'N/A'));
            $this->info("  Total: $" . number_format($order->total_amount, 0));
            $this->info("  Estado: {$order->status}");
            $this->info("  PDF: " . ($order->file_path !== 'pending_generation' ? 'Generado' : 'Pendiente'));
            $this->info("");
        }
        
        // Verificar items de la solicitud
        $items = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
            
        $this->info("=== ITEMS DE LA SOLICITUD ===");
        $this->info("Total de items: " . (is_array($items) ? count($items) : 0));
        
        if (is_array($items)) {
            foreach ($items as $index => $item) {
                $this->info("- Item " . ($index + 1) . ": " . ($item['description'] ?? 'N/A'));
                $this->info("  Cantidad: " . ($item['quantity'] ?? 'N/A'));
            }
        }
    }
}
