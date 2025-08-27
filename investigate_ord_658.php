<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PurchaseOrder;

echo "🔍 INVESTIGANDO ORD-0658 - ITEMS REALES VS PLACEHOLDER\n";
echo "=" . str_repeat("=", 60) . "\n";

$order = PurchaseOrder::with(['purchaseRequest.quotations', 'purchaseRequest.selectedQuotation'])
    ->where('order_number', 'ORD-0658')->first();

if (!$order) {
    echo "❌ No se encontró la orden ORD-0658\n";
    exit;
}

echo "📋 Orden: {$order->order_number}\n";
echo "🏢 Proveedor: " . ($order->provider ? $order->provider->nombre : 'N/A') . "\n\n";

// Verificar customData
echo "📄 PDF CUSTOM DATA:\n";
if ($order->pdf_custom_data) {
    $customData = is_array($order->pdf_custom_data) 
        ? $order->pdf_custom_data 
        : json_decode($order->pdf_custom_data, true);
    
    echo "- Generado automáticamente: " . ($customData['generated_automatically'] ?? 'NO') . "\n";
    echo "- Fecha generación: " . ($customData['generated_at'] ?? 'N/A') . "\n";
    
    if (isset($customData['items'])) {
        echo "\n📦 ITEMS EN CUSTOM DATA:\n";
        foreach ($customData['items'] as $index => $item) {
            echo "  Item #{$index}:\n";
            echo "    📝 Description: '" . ($item['description'] ?? 'VACÍO') . "'\n";
            echo "    🔢 Quantity: " . ($item['quantity'] ?? 'VACÍO') . "\n";
            echo "    💰 Unit Price: " . ($item['unit_price'] ?? 'VACÍO') . "\n";
            echo "    💵 Total: " . ($item['total'] ?? 'VACÍO') . "\n";
        }
    }
} else {
    echo "❌ No hay custom data\n";
}

// Verificar cotización seleccionada
echo "\n📊 COTIZACIÓN SELECCIONADA:\n";
$selectedQuotation = null;
if ($order->purchaseRequest && $order->purchaseRequest->quotations->isNotEmpty()) {
    $selectedQuotation = $order->purchaseRequest->quotations->where('selected', 1)->first();
    if (!$selectedQuotation && $order->purchaseRequest->selectedQuotation) {
        $selectedQuotation = $order->purchaseRequest->selectedQuotation;
    }
}

if ($selectedQuotation) {
    echo "- ID: {$selectedQuotation->id}\n";
    echo "- Proveedor: {$selectedQuotation->provider_name}\n";
    echo "- Items count: " . ($selectedQuotation->items ? count($selectedQuotation->items) : 0) . "\n";
    
    if ($selectedQuotation->items && count($selectedQuotation->items) > 0) {
        echo "\n📦 ITEMS EN COTIZACIÓN:\n";
        foreach ($selectedQuotation->items as $index => $item) {
            echo "  Item #{$index}:\n";
            echo "    📝 Description: '{$item->item_description}'\n";
            echo "    🔢 Quantity: {$item->quantity}\n";
            echo "    💰 Unit Price: {$item->unit_price}\n";
            echo "    💵 Total: {$item->total_price}\n";
        }
    }
} else {
    echo "❌ No hay cotización seleccionada\n";
}

// Verificar purchase request original
echo "\n📋 PURCHASE REQUEST ORIGINAL:\n";
if ($order->purchaseRequest) {
    echo "- ID: {$order->purchaseRequest->id}\n";
    echo "- Created: {$order->purchaseRequest->created_at}\n";
    
    // Verificar materials y services directamente
    $materials = json_decode($order->purchaseRequest->materials ?? '[]', true);
    $services = json_decode($order->purchaseRequest->services ?? '[]', true);
    
    echo "- Materials: " . count($materials) . "\n";
    echo "- Services: " . count($services) . "\n";
    
    if (count($materials) > 0) {
        echo "\n🛠️ MATERIALS:\n";
        foreach ($materials as $index => $material) {
            echo "  Material #{$index}: " . (isset($material['material']) ? $material['material'] : (isset($material['description']) ? $material['description'] : 'SIN NOMBRE')) . "\n";
        }
    }
    
    if (count($services) > 0) {
        echo "\n🔧 SERVICES:\n";
        foreach ($services as $index => $service) {
            echo "  Service #{$index}: " . (isset($service['service']) ? $service['service'] : (isset($service['description']) ? $service['description'] : 'SIN NOMBRE')) . "\n";
        }
    }
}

echo "\n🎯 RECOMENDACIÓN:\n";
echo "Si 'Item 1 - Editable' es un placeholder no deseado:\n";
echo "1. Eliminar del custom_data\n";
echo "2. Usar items reales de la cotización o purchase request\n";
echo "3. Hacer que la columna específica no sea editable\n";

echo "\n" . str_repeat("=", 60) . "\n";
?>
