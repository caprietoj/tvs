<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

echo "=== INVESTIGACIÓN ORD-0656 ===\n\n";

// Buscar la orden ORD-0656
$order = PurchaseOrder::where('order_number', 'ORD-0656')->with(['provider', 'purchaseRequest'])->first();

if (!$order) {
    echo "Orden ORD-0656 no encontrada. Buscando por patrón...\n";
    $order = PurchaseOrder::where('order_number', 'like', '%656%')->with(['provider', 'purchaseRequest'])->first();
}

if ($order) {
    echo "Orden encontrada: {$order->order_number} (ID: {$order->id})\n";
    echo "Proveedor: " . ($order->provider->nombre ?? 'N/A') . "\n\n";
    
    echo "=== DATOS DE LA ORDEN ===\n";
    echo "subtotal: " . ($order->subtotal ?? 'NULL') . "\n";
    echo "iva_amount: " . ($order->iva_amount ?? 'NULL') . "\n";
    echo "total_amount: " . ($order->total_amount ?? 'NULL') . "\n\n";
    
    echo "=== PDF CUSTOM DATA ===\n";
    if ($order->pdf_custom_data) {
        $customData = json_decode($order->pdf_custom_data, true);
        
        if (isset($customData['items'])) {
            echo "Items en custom data:\n";
            foreach ($customData['items'] as $index => $item) {
                echo ($index + 1) . ". {$item['description']} - Qty: {$item['quantity']} - Unit: {$item['unit_price']} - Total: {$item['total']}\n";
            }
        }
        
        if (isset($customData['prices'])) {
            echo "\nPrecios en custom data:\n";
            foreach ($customData['prices'] as $index => $price) {
                echo ($index + 1) . ". $price\n";
            }
        }
        
        echo "\nTotales custom:\n";
        echo "Subtotal: " . ($customData['subtotal'] ?? 'N/A') . "\n";
        echo "IVA: " . ($customData['iva_amount'] ?? 'N/A') . "\n";
        echo "Total: " . ($customData['total'] ?? 'N/A') . "\n";
        
    } else {
        echo "No hay datos personalizados (pdf_custom_data es NULL)\n";
    }
    
    echo "\n=== VERIFICACIÓN DE PROBLEMA ===\n";
    if ($order->pdf_custom_data) {
        $customData = json_decode($order->pdf_custom_data, true);
        if (isset($customData['items'])) {
            $zeroItems = 0;
            foreach ($customData['items'] as $item) {
                if (($item['unit_price'] ?? 0) == 0) {
                    $zeroItems++;
                }
            }
            echo "Items con precio $0: $zeroItems de " . count($customData['items']) . "\n";
            
            if ($zeroItems > 0) {
                echo "❌ PROBLEMA DETECTADO: Hay items con precio unitario $0\n";
            }
        }
    }
    
} else {
    echo "Orden ORD-0656 no encontrada en la base de datos\n";
    
    // Buscar órdenes similares
    echo "\nBuscando órdenes similares...\n";
    $similarOrders = PurchaseOrder::where('order_number', 'like', '%656%')->select('id', 'order_number')->get();
    
    if ($similarOrders->count() > 0) {
        echo "Órdenes encontradas:\n";
        foreach ($similarOrders as $similar) {
            echo "- {$similar->order_number} (ID: {$similar->id})\n";
        }
    } else {
        echo "No se encontraron órdenes similares\n";
    }
}
