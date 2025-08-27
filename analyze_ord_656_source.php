<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\QuotationItemSelection;

echo "=== ANÁLISIS DETALLADO ORD-0656 ===\n\n";

$order = PurchaseOrder::with(['provider', 'purchaseRequest'])->find(100);

if ($order) {
    echo "Orden: {$order->order_number} (ID: {$order->id})\n";
    echo "Proveedor: " . ($order->provider->nombre ?? 'N/A') . "\n\n";
    
    // Verificar si tiene selecciones de cotización
    echo "=== SELECCIONES DE COTIZACIÓN ===\n";
    $selections = QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
        $query->where('provider_name', $order->provider->nombre ?? '');
    })->with(['quotation', 'purchaseRequestItem'])->get();
    
    echo "Selecciones encontradas: " . $selections->count() . "\n";
    
    if ($selections->count() > 0) {
        echo "Es una orden MIXTA - obteniendo datos de cotizaciones:\n\n";
        
        foreach ($selections as $index => $selection) {
            $item = $selection->purchaseRequestItem;
            $quotation = $selection->quotation;
            
            $unitPrice = 0;
            if ($quotation && $quotation->prices) {
                $prices = json_decode($quotation->prices, true);
                $unitPrice = $prices[$selection->item_index] ?? 0;
            }
            
            echo ($index + 1) . ". " . ($item->description ?? 'N/A') . "\n";
            echo "   Cantidad: " . ($selection->quantity ?? 1) . "\n";
            echo "   Precio unitario: $" . number_format($unitPrice, 0, ',', '.') . "\n";
            echo "   Total: $" . number_format(($selection->quantity ?? 1) * $unitPrice, 0, ',', '.') . "\n";
            echo "   Item index: " . $selection->item_index . "\n";
            
            if ($quotation) {
                echo "   Cotización ID: " . $quotation->id . "\n";
                echo "   Precios de cotización: " . ($quotation->prices ?? 'N/A') . "\n";
            }
            echo "\n";
        }
    } else {
        echo "No es orden mixta. Verificando purchase_items...\n\n";
        
        // Si no es mixta, debe tener purchase_items básicos
        $purchaseRequest = $order->purchaseRequest;
        if ($purchaseRequest && $purchaseRequest->purchase_items) {
            $items = $purchaseRequest->purchase_items;
            
            // Verificar si ya es array o string JSON
            if (is_string($items)) {
                $items = json_decode($items, true);
            }
            
            echo "Items del purchase request:\n";
            
            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    echo ($index + 1) . ". " . ($item['description'] ?? 'N/A') . "\n";
                    echo "   Cantidad: " . ($item['quantity'] ?? 1) . "\n";
                    echo "   Nota: Sin precios en purchase_items (normal)\n\n";
                }
            } else {
                echo "Items no es un array válido\n";
            }
        }
        
        // Verificar si hay precios en el purchase request
        if ($purchaseRequest && $purchaseRequest->prices) {
            echo "Precios en purchase request:\n";
            $prices = $purchaseRequest->prices;
            
            // Verificar si ya es array o string JSON
            if (is_string($prices)) {
                $prices = json_decode($prices, true);
            }
            
            if (is_array($prices)) {
                foreach ($prices as $index => $price) {
                    echo ($index + 1) . ". $" . number_format($price, 0, ',', '.') . "\n";
                }
            } else {
                echo "Formato de precios inválido\n";
            }
        } else {
            echo "No hay precios en purchase request\n";
        }
    }
    
} else {
    echo "Orden no encontrada\n";
}
