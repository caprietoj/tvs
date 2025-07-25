<?php

// Configuración inicial
ini_set('memory_limit', '512M');
set_time_limit(300);

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    die("❌ Error: Este script debe ejecutarse desde el directorio raíz de Laravel.\n");
}

// Cargar Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

echo "🔍 Analizando TODAS las órdenes de compra para detectar problemas...\n\n";

// Obtener todas las órdenes
$orders = PurchaseOrder::with(['purchaseRequest', 'purchaseRequest.quotationItemSelections'])
    ->orderBy('id', 'desc')
    ->get();

echo "📊 Total de órdenes encontradas: " . $orders->count() . "\n\n";

$problemsFound = 0;

foreach ($orders as $order) {
    $hasMixedSelection = $order->purchaseRequest->quotationItemSelections()->exists();
    $items = $order->purchaseRequest->purchase_items ?? [];
    if (is_string($items)) {
        $items = json_decode($items, true) ?? [];
    }
    
    $hasMultipleItems = count($items) > 1;
    $hasAdditionalItems = !empty($order->additional_items);
    
    // Mostrar información de cada orden
    echo "🔍 Orden #{$order->id} - {$order->order_number}\n";
    echo "  💰 Total: $" . number_format($order->total_amount, 2) . "\n";
    echo "  📊 Subtotal: $" . number_format($order->subtotal ?? 0, 2) . "\n";
    echo "  🔀 Selección mixta: " . ($hasMixedSelection ? "SÍ" : "NO") . "\n";
    echo "  📋 Purchase items: " . count($items) . "\n";
    echo "  📦 Additional items: " . ($hasAdditionalItems ? "SÍ" : "NO") . "\n";
    
    // Detectar problemas
    if (!$hasMixedSelection && $hasMultipleItems && !$hasAdditionalItems) {
        echo "  ⚠️  PROBLEMA: Múltiples ítems sin selección mixta y sin additional_items\n";
        echo "     Esto causará cálculos incorrectos en el PDF\n";
        $problemsFound++;
    } elseif (!$hasMixedSelection && $hasMultipleItems && $hasAdditionalItems) {
        echo "  ✅ CORREGIDO: Tiene additional_items para manejar múltiples ítems\n";
    } elseif ($hasMultipleItems) {
        echo "  ℹ️  INFO: Múltiples ítems con selección mixta (manejo correcto)\n";
    } else {
        echo "  ✅ OK: Un solo ítem o manejo correcto\n";
    }
    
    echo "\n";
}

echo str_repeat("-", 60) . "\n";
echo "📊 Resumen: {$problemsFound} órdenes con problemas detectados\n\n";

if ($problemsFound > 0) {
    echo "⚠️  Para corregir los errores, ejecute:\n";
    echo "   php fix_all_multiple_items.php fix\n\n";
} else {
    echo "🎉 ¡No se encontraron problemas!\n\n";
}

echo "✨ Análisis completado.\n";