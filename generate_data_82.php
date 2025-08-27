<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;

echo "Generando datos para orden 82...\n";

$order = PurchaseOrder::with(['purchaseRequest.selectedQuotation', 'provider'])->find(82);
if (!$order) {
    echo "Orden no encontrada\n";
    exit(1);
}

echo "Orden: {$order->order_number}\n";
echo "Proveedor: {$order->provider->nombre}\n";

// Crear datos básicos
$items = [
    [
        'description' => 'Item 1 - Editable en interfaz',
        'quantity' => 1,
        'unit_price' => 100000,
        'total' => 100000
    ]
];

// Si hay cotización con precios originales, usarlos
if ($order->purchaseRequest->selectedQuotation && !empty($order->purchaseRequest->selectedQuotation->original_item_prices)) {
    $originalPrices = $order->purchaseRequest->selectedQuotation->original_item_prices;
    echo "Usando " . count($originalPrices) . " precios originales\n";
    
    $items = [];
    foreach ($originalPrices as $index => $price) {
        $items[] = [
            'description' => "Item " . ($index + 1) . " - Editable",
            'quantity' => 1,
            'unit_price' => floatval($price),
            'total' => floatval($price)
        ];
    }
}

$subtotal = array_sum(array_column($items, 'total'));
$ivaAmount = round($subtotal * 0.19);
$total = $subtotal + $ivaAmount;

$customData = [
    'items' => $items,
    'subtotal' => $subtotal,
    'iva_rate' => '19%',
    'iva_amount' => $ivaAmount,
    'ipoconsumo_rate' => '0%',
    'ipoconsumo_amount' => 0,
    'total' => $total,
    'provider_info' => [
        'name' => $order->provider->nombre ?? '',
        'nit' => $order->provider->nit ?? '',
        'address' => $order->provider->direccion ?? '',
        'phone' => $order->provider->telefono ?? '',
        'email' => $order->provider->correo ?? ''
    ],
    'generated_manually' => true,
    'generated_at' => now()->toDateTimeString()
];

$order->pdf_custom_data = json_encode($customData);
$order->save();

echo "✅ Datos generados:\n";
echo "- Items: " . count($items) . "\n";
echo "- Subtotal: $" . number_format($subtotal, 0, ',', '.') . "\n";
echo "- IVA: $" . number_format($ivaAmount, 0, ',', '.') . "\n";
echo "- Total: $" . number_format($total, 0, ',', '.') . "\n";
echo "\n🎉 La orden 82 ahora es editable en:\n";
echo "http://127.0.0.1:8000/purchase-orders/82/edit-pdf\n";

echo "\nVERIFICANDO CREACIÓN...\n";
$order->refresh();
echo "PDF Custom Data: " . (empty($order->pdf_custom_data) ? 'VACÍO' : 'CREADO') . "\n";

?>
