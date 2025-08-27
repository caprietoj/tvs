<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PurchaseOrder;

echo "🗑️ ELIMINANDO ITEM PLACEHOLDER DE ORD-0658\n";
echo "=" . str_repeat("=", 50) . "\n";

$order = PurchaseOrder::where('order_number', 'ORD-0658')->first();

if (!$order) {
    echo "❌ No se encontró la orden ORD-0658\n";
    exit;
}

$customData = is_array($order->pdf_custom_data) 
    ? $order->pdf_custom_data 
    : json_decode($order->pdf_custom_data, true);

echo "📋 Orden: {$order->order_number}\n";
echo "📄 Items antes: " . (isset($customData['items']) ? count($customData['items']) : 0) . "\n";

if (isset($customData['items'])) {
    foreach ($customData['items'] as $index => $item) {
        echo "  Item #{$index}: '{$item['description']}'\n";
    }
}

// Eliminar items placeholder que contienen "Editable" o son generados automáticamente
if (isset($customData['items'])) {
    $customData['items'] = array_filter($customData['items'], function($item) {
        $description = $item['description'] ?? '';
        // Eliminar items que:
        // 1. Contengan "Editable" 
        // 2. Sean exactamente "Item 1 - Editable"
        // 3. Sean placeholders genéricos
        return !( 
            stripos($description, 'editable') !== false ||
            stripos($description, 'item 1') !== false ||
            $description === 'Item 1 - Editable'
        );
    });
    
    // Re-indexar el array
    $customData['items'] = array_values($customData['items']);
}

// Si ya no hay items, limpiar también otros datos automáticos
if (empty($customData['items'])) {
    // Mantener información del proveedor pero limpiar items y totales automáticos
    unset($customData['items']);
    unset($customData['generated_automatically']);
    unset($customData['generated_at']);
    
    // Reset totales a 0 si no hay items
    $customData['subtotal'] = 0;
    $customData['total'] = 0;
    $customData['iva_amount'] = 0;
    $customData['ipoconsumo_amount'] = 0;
}

// Guardar cambios
$order->pdf_custom_data = $customData;
$order->save();

echo "\n✅ DESPUÉS DE LIMPIEZA:\n";
echo "📄 Items después: " . (isset($customData['items']) ? count($customData['items']) : 0) . "\n";

if (isset($customData['items']) && count($customData['items']) > 0) {
    foreach ($customData['items'] as $index => $item) {
        echo "  Item #{$index}: '{$item['description']}'\n";
    }
} else {
    echo "🎯 No hay items - la orden ahora está limpia\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>
