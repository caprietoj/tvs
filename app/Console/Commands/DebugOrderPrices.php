<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class DebugOrderPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:order-prices {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra información detallada sobre los precios de una orden de compra';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $order = PurchaseOrder::with([
            'purchaseRequest.selectedQuotation', 
            'purchaseRequest.quotationItemSelections.quotation',
            'provider'
        ])->find($orderId);
        
        if (!$order) {
            $this->error("No se encontró la orden con ID: {$orderId}");
            return 1;
        }
        
        $this->info("DIAGNÓSTICO DE PRECIOS PARA ORDEN: {$order->order_number} (ID: {$order->id})");
        $this->info("Proveedor: " . ($order->provider ? $order->provider->nombre : 'No asignado'));
        $this->info("Estado: {$order->status}");
        
        // Verificar si tiene solicitud asociada
        if (!$order->purchaseRequest) {
            $this->error("Esta orden no tiene solicitud de compra asociada");
            return 1;
        }
        
        $purchaseRequest = $order->purchaseRequest;
        $this->info("Solicitud asociada: #{$purchaseRequest->request_number} (ID: {$purchaseRequest->id})");
        
        // Verificar datos PDF personalizados
        $customData = json_decode($order->pdf_custom_data, true);
        if (empty($customData) || !isset($customData['items'])) {
            $this->warn("La orden no tiene datos personalizados para el PDF o no tiene items");
            return 1;
        }
        
        $items = $customData['items'];
        $this->info("Total de items en PDF: " . count($items));
        
        // Determinar si es selección mixta
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        $this->info("¿Es selección mixta? " . ($hasMixedSelection ? 'SÍ' : 'NO'));
        
        // Analizar detalles de los precios
        $this->table(
            ['#', 'Descripción', 'Cantidad', 'Precio Unitario', 'Total', 'Tipo Precio'],
            $this->formatItemsForTable($items)
        );
        
        // Si es selección mixta, mostrar información específica
        if ($hasMixedSelection) {
            // Obtener solo las selecciones del proveedor específico
            $selections = $purchaseRequest->quotationItemSelections()
                ->whereHas('quotation', function($query) use ($order) {
                    $query->where('provider_name', $order->provider->nombre);
                })
                ->with('quotation')
                ->get();
            
            $this->info("\nDETALLES DE SELECCIÓN MIXTA");
            $this->info("Selecciones para este proveedor: " . $selections->count());
            
            foreach ($selections as $selection) {
                $this->line("\n- Item: {$selection->item_description}");
                $this->line("  Index en cotización: {$selection->item_index}");
                $this->line("  Cotización ID: " . ($selection->quotation_id ?? 'N/A'));
                
                if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                    $priceExists = isset($selection->quotation->original_item_prices[$selection->item_index]);
                    $price = $priceExists ? $selection->quotation->original_item_prices[$selection->item_index] : 'No disponible';
                    $this->line("  Precio original: {$price} (" . ($priceExists ? 'ENCONTRADO' : 'NO ENCONTRADO') . ")");
                } else {
                    $this->line("  Precio original: No disponible (cotización o precios no encontrados)");
                }
            }
        } 
        // Si es cotización normal, mostrar detalles
        else if ($purchaseRequest->selectedQuotation) {
            $quotation = $purchaseRequest->selectedQuotation;
            $this->info("\nDETALLES DE COTIZACIÓN REGULAR");
            $this->info("Cotización seleccionada ID: {$quotation->id}");
            
            if (isset($quotation->original_item_prices)) {
                $this->info("Precios originales disponibles: " . count($quotation->original_item_prices));
                
                $this->table(
                    ['Índice', 'Precio Original'],
                    $this->formatOriginalPrices($quotation->original_item_prices)
                );
            } else {
                $this->warn("La cotización no tiene precios originales almacenados");
            }
        } else {
            $this->warn("No se encontró cotización asociada");
        }
        
        return 0;
    }
    
    /**
     * Format items for display in table
     */
    private function formatItemsForTable($items)
    {
        $tableRows = [];
        foreach ($items as $index => $item) {
            $priceType = is_string($item['unit_price']) ? 'string (' . gettype($item['unit_price']) . ')' : 'numeric';
            $tableRows[] = [
                'index' => $index + 1,
                'description' => $item['description'] ?? 'Sin descripción',
                'quantity' => $item['quantity'] ?? '0',
                'unit_price' => $item['unit_price'] ?? '0',
                'total' => $item['total'] ?? '0',
                'price_type' => $priceType
            ];
        }
        return $tableRows;
    }
    
    /**
     * Format original prices for display
     */
    private function formatOriginalPrices($prices)
    {
        $tableRows = [];
        foreach ($prices as $index => $price) {
            $tableRows[] = [
                'index' => $index,
                'price' => $price
            ];
        }
        return $tableRows;
    }
}
