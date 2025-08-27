<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

echo "=== ANÁLISIS DE DISCREPANCIA TOTAL ORD-0656 ===\n\n";

$order = PurchaseOrder::with(['purchaseRequest.selectedQuotation'])->find(100);

if ($order) {
    echo "Orden: {$order->order_number}\n\n";
    
    echo "=== VALORES EN LA BASE DE DATOS ===\n";
    echo "order->subtotal: $" . number_format($order->subtotal ?? 0, 0, ',', '.') . "\n";
    echo "order->iva_amount: $" . number_format($order->iva_amount ?? 0, 0, ',', '.') . "\n";
    echo "order->total_amount: $" . number_format($order->total_amount ?? 0, 0, ',', '.') . "\n\n";
    
    echo "=== COTIZACIÓN SELECCIONADA ===\n";
    $quotation = $order->purchaseRequest->selectedQuotation;
    if ($quotation) {
        echo "Cotización ID: {$quotation->id}\n";
        echo "Total de cotización: $" . number_format($quotation->total_amount ?? 0, 0, ',', '.') . "\n";
        echo "Subtotal de cotización: $" . number_format($quotation->subtotal ?? 0, 0, ',', '.') . "\n";
        echo "IVA de cotización: $" . number_format($quotation->iva_amount ?? 0, 0, ',', '.') . "\n\n";
        
        // Verificar precios individuales
        $prices = $quotation->original_item_prices;
        if (is_array($prices)) {
            echo "=== PRECIOS INDIVIDUALES EN COTIZACIÓN ===\n";
            $calculatedSubtotal = 0;
            $items = $order->purchaseRequest->purchase_items;
            
            foreach ($items as $index => $item) {
                $price = $prices[$index] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                $itemTotal = $price * $quantity;
                $calculatedSubtotal += $itemTotal;
                
                echo ($index + 1) . ". {$item['description']}: $" . number_format($price, 0, ',', '.') . 
                     " × {$quantity} = $" . number_format($itemTotal, 0, ',', '.') . "\n";
            }
            
            $calculatedIva = $calculatedSubtotal * 0.19;
            $calculatedTotal = $calculatedSubtotal + $calculatedIva;
            
            echo "\n=== CÁLCULO BASADO EN ITEMS ===\n";
            echo "Subtotal calculado: $" . number_format($calculatedSubtotal, 0, ',', '.') . "\n";
            echo "IVA calculado (19%): $" . number_format($calculatedIva, 0, ',', '.') . "\n";
            echo "Total calculado: $" . number_format($calculatedTotal, 0, ',', '.') . "\n\n";
            
            echo "=== COMPARACIÓN ===\n";
            echo "Total BD vs Calculado: $" . number_format($order->total_amount, 0, ',', '.') . 
                 " vs $" . number_format($calculatedTotal, 0, ',', '.') . "\n";
            echo "Diferencia: $" . number_format(abs($order->total_amount - $calculatedTotal), 0, ',', '.') . "\n";
            
            if (abs($order->total_amount - $calculatedTotal) > 1) {
                echo "❌ DISCREPANCIA DETECTADA\n";
                echo "El total en BD no coincide con el cálculo de items\n";
            } else {
                echo "✅ Los totales coinciden\n";
            }
        }
    }
    
    echo "\n=== PROBLEMA IDENTIFICADO ===\n";
    echo "El PDF está usando order->total_amount de la BD en lugar de calcular desde los items\n";
    echo "Para esta orden, debería usar el total calculado desde los precios de items\n";
}
?>
