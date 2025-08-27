<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Generando datos personalizados para orden 82...\n";

$order = App\Models\PurchaseOrder::find(82);
if (!$order) {
    echo "Orden 82 no encontrada\n";
    exit(1);
}

$order->load(['purchaseRequest.selectedQuotation', 'provider']);
$quotation = $order->purchaseRequest->selectedQuotation;

if (!$quotation) {
    echo "La orden no tiene cotización seleccionada\n";
    exit(1);
}

echo "Orden: {$order->order_number}\n";
echo "Cotización ID: {$quotation->id}\n";
echo "Proveedor: {$quotation->provider_name}\n";

// Verificar si ya tiene datos personalizados
if (!empty($order->pdf_custom_data)) {
    echo "ATENCIÓN: La orden ya tiene datos personalizados. ¿Sobrescribir? (esto permitirá la edición)\n";
    echo "Datos existentes encontrados.\n";
}

// Crear datos personalizados básicos desde la cotización
$items = [];
$originalPrices = $quotation->original_item_prices ?? [];

if (empty($originalPrices)) {
    echo "No hay precios originales en la cotización. Usando datos básicos...\n";
    // Crear un item básico si no hay precios originales
    $items = [
        [
            'description' => 'Item de muestra - Editable',
            'quantity' => 1,
            'unit_price' => 100000,
            'total' => 100000
        ]
    ];
} else {
    echo "Encontrados " . count($originalPrices) . " precios originales\n";
    foreach ($originalPrices as $index => $price) {
        $quantity = 1; // Cantidad por defecto
        $items[] = [
            'description' => "Item " . ($index + 1) . " - Editable",
            'quantity' => $quantity,
            'unit_price' => floatval($price),
            'total' => $quantity * floatval($price)
        ];
    }
}

$subtotal = array_sum(array_column($items, 'total'));
$ivaRate = 19;
$ivaAmount = round(($subtotal * $ivaRate) / 100);
$totalAmount = $subtotal + $ivaAmount;

$customData = [
    'items' => $items,
    'subtotal' => $subtotal,
    'iva_rate' => $ivaRate . '%',
    'iva_amount' => $ivaAmount,
    'ipoconsumo_rate' => '0%',
    'ipoconsumo_amount' => 0,
    'total' => $totalAmount,
    'provider_info' => [
        'name' => $order->provider->nombre ?? 'N/A',
        'nit' => $order->provider->nit ?? 'N/A',
        'address' => $order->provider->direccion ?? 'N/A',
        'phone' => $order->provider->telefono ?? 'N/A',
        'email' => $order->provider->correo ?? 'N/A'
    ]
];

// Guardar los datos personalizados
$order->pdf_custom_data = json_encode($customData);
$order->save();

echo "\n✅ DATOS PERSONALIZADOS GENERADOS EXITOSAMENTE!\n";
echo "Items: " . count($items) . "\n";
echo "Subtotal: $" . number_format($subtotal, 0, ',', '.') . "\n";
echo "IVA (19%): $" . number_format($ivaAmount, 0, ',', '.') . "\n";
echo "Total: $" . number_format($totalAmount, 0, ',', '.') . "\n";
echo "\n🎉 La orden 82 ahora es editable en:\n";
echo "http://127.0.0.1:8000/purchase-orders/82/edit-pdf\n";
echo "\nEn esa página podrás:\n";
echo "- Editar descripciones, cantidades y precios\n";
echo "- Agregar o eliminar items\n";
echo "- Ajustar impuestos\n";
echo "- Guardar y regenerar el PDF\n";

?>
