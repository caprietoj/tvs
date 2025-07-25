<?php
// Script para analizar la discrepancia específica de $50,000
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\Quotation;

echo "=== ANÁLISIS DE DISCREPANCIA DE \$50,000 ===\n";

$order = PurchaseOrder::where('order_number', 'ORD-0002')->first();
$quotation = Quotation::where('purchase_request_id', $order->purchase_request_id)->first();

echo "Total actual: \$29,214,500\n";
echo "Total esperado: \$29,164,500\n";
echo "Diferencia: \$50,000\n\n";

// Calcular hacia atrás desde el total esperado
$totalEsperado = 29164500;
$ivaRate = 0.19;
$subtotalEsperado = $totalEsperado / (1 + $ivaRate);
$ivaEsperado = $totalEsperado - $subtotalEsperado;

echo "=== CÁLCULO INVERSO ===\n";
echo "Si el total correcto es \$29,164,500:\n";
echo "Subtotal esperado: \$" . number_format($subtotalEsperado, 2) . "\n";
echo "IVA esperado: \$" . number_format($ivaEsperado, 2) . "\n\n";

// Comparar con valores actuales
echo "=== COMPARACIÓN ===\n";
echo "Subtotal actual: \$24,550,000\n";
echo "Subtotal esperado: \$" . number_format($subtotalEsperado, 2) . "\n";
echo "Diferencia subtotal: \$" . number_format(24550000 - $subtotalEsperado, 2) . "\n\n";

// Analizar posibles errores en precios unitarios
echo "=== ANÁLISIS DE PRECIOS UNITARIOS ===\n";

// Opción 1: Error en precio de computadores
$precioComputadorActual = 2000000;
$precioComputadorCorregido = ($subtotalEsperado - (11 * 50000)) / 12;
echo "Opción 1 - Error en computadores:\n";
echo "Precio actual: \$2,000,000\n";
echo "Precio corregido: \$" . number_format($precioComputadorCorregido, 2) . "\n";
echo "Diferencia: \$" . number_format($precioComputadorActual - $precioComputadorCorregido, 2) . "\n\n";

// Opción 2: Error en precio de mouse
$precioMouseActual = 50000;
$precioMouseCorregido = ($subtotalEsperado - (12 * 2000000)) / 11;
echo "Opción 2 - Error en mouse:\n";
echo "Precio actual: \$50,000\n";
echo "Precio corregido: \$" . number_format($precioMouseCorregido, 2) . "\n";
echo "Diferencia: \$" . number_format($precioMouseActual - $precioMouseCorregido, 2) . "\n\n";

// Opción 3: Error en cantidad
echo "Opción 3 - Error en cantidades:\n";
echo "Si el precio del mouse fuera \$45,454.55 (aprox):\n";
$precioMouseOpcion3 = 45454.55;
$totalMouseOpcion3 = 11 * $precioMouseOpcion3;
$subtotalOpcion3 = 24000000 + $totalMouseOpcion3;
$ivaOpcion3 = $subtotalOpcion3 * 0.19;
$totalOpcion3 = $subtotalOpcion3 + $ivaOpcion3;
echo "Mouse: 11 × \$45,454.55 = \$" . number_format($totalMouseOpcion3, 2) . "\n";
echo "Subtotal: \$" . number_format($subtotalOpcion3, 2) . "\n";
echo "Total: \$" . number_format($totalOpcion3, 2) . "\n\n";

// Verificar los datos originales de la solicitud
$purchaseRequest = $order->purchaseRequest;
$originalItems = is_array($purchaseRequest->purchase_items) 
    ? $purchaseRequest->purchase_items 
    : json_decode($purchaseRequest->purchase_items, true);

echo "=== DATOS ORIGINALES DE LA SOLICITUD ===\n";
foreach ($originalItems as $index => $item) {
    echo "Item {$index}: {$item['description']}\n";
    echo "  - Cantidad original: {$item['quantity']}\n";
    echo "  - Precio cotizado: \$" . number_format($quotation->original_item_prices[$index], 2) . "\n\n";
}

// Verificar todas las cotizaciones para esta solicitud
echo "=== TODAS LAS COTIZACIONES ===\n";
$allQuotations = Quotation::where('purchase_request_id', $order->purchase_request_id)->get();
foreach ($allQuotations as $q) {
    echo "Cotización {$q->id} - {$q->provider_name}:\n";
    echo "  - Subtotal: \${$q->subtotal}\n";
    echo "  - Total: \${$q->total_amount}\n";
    echo "  - Precios: " . json_encode($q->original_item_prices) . "\n";
    echo "  - Seleccionada: " . ($q->is_selected ? 'SÍ' : 'NO') . "\n\n";
}

echo "=== RECOMENDACIONES ===\n";
if (abs($precioMouseCorregido - 45454) < 1000) {
    echo "🎯 PROBABLE SOLUCIÓN: El precio del mouse debería ser ~\$45,454 en lugar de \$50,000\n";
    echo "   Esto reduciría el subtotal en \$50,000 exactamente\n";
} else {
    echo "🤔 La discrepancia no corresponde a un ajuste simple de precios\n";
    echo "   Revisar los datos originales de la cotización o los cálculos\n";
}
