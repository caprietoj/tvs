<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

echo "=== ANÁLISIS DE APLICACIÓN DEL CÁLCULO DINÁMICO ===\n\n";

// Obtener muestra de diferentes tipos de órdenes
$orders = PurchaseOrder::with(['purchaseRequest.selectedQuotation'])
    ->take(10)
    ->get();

echo "ANALIZANDO " . $orders->count() . " ÓRDENES DE MUESTRA:\n\n";

$typeCounts = [
    'with_custom_data' => 0,
    'without_custom_but_with_quotation' => 0,
    'without_custom_without_quotation' => 0,
    'total_dynamic_calculation' => 0
];

foreach ($orders as $order) {
    echo "--- {$order->order_number} (ID: {$order->id}) ---\n";
    
    $hasCustomData = !empty($order->pdf_custom_data);
    $hasPurchaseRequest = !empty($order->purchaseRequest);
    $hasSelectedQuotation = $hasPurchaseRequest && !empty($order->purchaseRequest->selectedQuotation);
    
    echo "- pdf_custom_data: " . ($hasCustomData ? "✅ SÍ" : "❌ NO") . "\n";
    echo "- purchaseRequest: " . ($hasPurchaseRequest ? "✅ SÍ" : "❌ NO") . "\n";
    echo "- selectedQuotation: " . ($hasSelectedQuotation ? "✅ SÍ" : "❌ NO") . "\n";
    
    // Determinar si aplicaría el cálculo dinámico
    $wouldApplyDynamicCalculation = !$hasCustomData && $hasPurchaseRequest && $hasSelectedQuotation;
    
    echo "- CÁLCULO DINÁMICO: " . ($wouldApplyDynamicCalculation ? "✅ SÍ SE APLICA" : "❌ NO SE APLICA") . "\n";
    
    if ($hasCustomData) {
        $typeCounts['with_custom_data']++;
        echo "- Razón: Usa pdf_custom_data (prioritario)\n";
    } elseif ($wouldApplyDynamicCalculation) {
        $typeCounts['without_custom_but_with_quotation']++;
        $typeCounts['total_dynamic_calculation']++;
        echo "- Razón: Sin custom_data pero con cotización → CALCULA DINÁMICAMENTE\n";
    } else {
        $typeCounts['without_custom_without_quotation']++;
        echo "- Razón: Sin custom_data y sin cotización → Usa valores BD\n";
    }
    
    echo "\n";
}

echo "=== RESUMEN DE APLICACIÓN ===\n";
echo "✅ Con pdf_custom_data: {$typeCounts['with_custom_data']} órdenes\n";
echo "   → Usan totales de custom_data (NO cálculo dinámico)\n\n";

echo "🔄 Sin custom_data + Con cotización: {$typeCounts['without_custom_but_with_quotation']} órdenes\n";
echo "   → Usan CÁLCULO DINÁMICO (nueva funcionalidad)\n\n";

echo "📄 Sin custom_data + Sin cotización: {$typeCounts['without_custom_without_quotation']} órdenes\n";
echo "   → Usan valores de BD (sin cambios)\n\n";

echo "🎯 TOTAL que usa cálculo dinámico: {$typeCounts['total_dynamic_calculation']} de {$orders->count()}\n\n";

echo "=== LÓGICA DE DECISIÓN ===\n";
echo "La corrección es INTELIGENTE y aplica SOLO cuando:\n";
echo "1. ❌ NO hay pdf_custom_data (evita sobrescribir datos manuales)\n";
echo "2. ✅ SÍ hay purchaseRequest (tiene datos de origen)\n";
echo "3. ✅ SÍ hay selectedQuotation (tiene precios para calcular)\n\n";

echo "RESULTADO: Solo afecta órdenes que necesitan la corrección\n";
