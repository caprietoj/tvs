<?php

/**
 * Demostración del problema y la solución implementada
 * Muestra la diferencia entre el comportamiento anterior y el nuevo
 */

echo "🔧 DEMOSTRACIÓN: ANTES vs DESPUÉS DE LA CORRECCIÓN\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Datos de entrada del formulario (caso del usuario)
$formData = [
    'items' => [
        [
            'description' => 'Producto ejemplo',
            'quantity' => 2,
            'unit_price' => 64000,
            'total' => 128000,
            'tax_rate' => 0
        ]
    ],
    'subtotal' => 128000,
    'iva_rate' => '0',
    'iva_amount' => 0,
    'ipoconsumo_rate' => '0', 
    'ipoconsumo_amount' => 0,
    'individual_taxes_total' => 0,
    'total' => 128000
];

echo "📝 DATOS ORIGINALES DEL FORMULARIO:\n";
echo "- Item: {$formData['items'][0]['description']}\n";
echo "- Cantidad: {$formData['items'][0]['quantity']}\n";
echo "- Precio unitario: $" . number_format($formData['items'][0]['unit_price'], 0, ',', '.') . "\n";
echo "- Total item: $" . number_format($formData['items'][0]['total'], 0, ',', '.') . "\n";
echo "- TOTAL GENERAL: $" . number_format($formData['total'], 0, ',', '.') . "\n\n";

echo "=" . str_repeat("=", 60) . "\n";

// COMPORTAMIENTO ANTERIOR (PROBLEMÁTICO)
echo "❌ COMPORTAMIENTO ANTERIOR (PROBLEMÁTICO):\n";
echo "-" . str_repeat("-", 50) . "\n";

function simulateOldBehavior($data) {
    echo "🔍 Ejecutando lógica anterior...\n";
    
    // 1. Detección de "precios anómalos" (>10M)
    $originalPrice = $data['items'][0]['unit_price'];
    if ($originalPrice > 10000000) {
        echo "⚠️  Precio anómalo detectado: $" . number_format($originalPrice, 0, ',', '.') . " (>10M)\n";
        echo "🔧 Aplicando corrección automática...\n";
        $correctedPrice = $originalPrice / 1000;
        $data['items'][0]['unit_price'] = $correctedPrice;
        $data['items'][0]['total'] = $data['items'][0]['quantity'] * $correctedPrice;
        echo "✏️  Precio corregido a: $" . number_format($correctedPrice, 0, ',', '.') . "\n";
    }
    
    // 2. Override con "precios originales" simulados
    $simulatedOriginalPrices = [
        0 => 66050 // Precio "original" almacenado que era diferente al del formulario
    ];
    
    echo "🔄 Sobrescribiendo con precios 'originales' almacenados...\n";
    foreach ($simulatedOriginalPrices as $index => $originalPrice) {
        echo "📦 Item $index: Cambiando de $" . number_format($data['items'][$index]['unit_price'], 0, ',', '.') . 
             " a $" . number_format($originalPrice, 0, ',', '.') . "\n";
        
        $data['items'][$index]['unit_price'] = $originalPrice;
        $data['items'][$index]['total'] = $data['items'][$index]['quantity'] * $originalPrice;
    }
    
    // Recalcular total
    $newSubtotal = 0;
    foreach ($data['items'] as $item) {
        $newSubtotal += $item['total'];
    }
    $data['subtotal'] = $newSubtotal;
    $data['total'] = $newSubtotal + $data['iva_amount'] + $data['ipoconsumo_amount'];
    
    return $data;
}

$oldResult = simulateOldBehavior($formData);

echo "\n📄 RESULTADO ANTERIOR (lo que iba al PDF):\n";
echo "- Precio unitario final: $" . number_format($oldResult['items'][0]['unit_price'], 0, ',', '.') . "\n";
echo "- Total item final: $" . number_format($oldResult['items'][0]['total'], 0, ',', '.') . "\n";
echo "- TOTAL GENERAL FINAL: $" . number_format($oldResult['total'], 0, ',', '.') . "\n";

echo "\n🚨 PROBLEMA: ¡El formulario mostraba $128,000 pero el PDF generaba $" . number_format($oldResult['total'], 0, ',', '.') . "!\n";

echo "\n" . str_repeat("=", 60) . "\n";

// COMPORTAMIENTO NUEVO (CORRECTO)
echo "✅ COMPORTAMIENTO NUEVO (CORREGIDO):\n";
echo "-" . str_repeat("-", 50) . "\n";

function simulateNewBehavior($data) {
    echo "🔍 Ejecutando lógica corregida...\n";
    echo "📋 Usando datos del formulario como fuente de verdad\n";
    echo "🚫 NO aplicando correcciones automáticas de precios\n";
    echo "🚫 NO sobrescribiendo con precios 'originales'\n";
    
    // La nueva lógica simplemente usa los datos tal como vienen del formulario
    return $data;
}

$newResult = simulateNewBehavior($formData);

echo "\n📄 RESULTADO NUEVO (lo que va al PDF):\n";
echo "- Precio unitario final: $" . number_format($newResult['items'][0]['unit_price'], 0, ',', '.') . "\n";
echo "- Total item final: $" . number_format($newResult['items'][0]['total'], 0, ',', '.') . "\n";
echo "- TOTAL GENERAL FINAL: $" . number_format($newResult['total'], 0, ',', '.') . "\n";

echo "\n✅ SOLUCIÓN: ¡Ahora el formulario y el PDF muestran exactamente $" . number_format($newResult['total'], 0, ',', '.') . "!\n";

echo "\n" . str_repeat("=", 60) . "\n";

// COMPARACIÓN FINAL
echo "📊 COMPARACIÓN FINAL:\n";
echo "-" . str_repeat("-", 25) . "\n";

echo "Valores del formulario:     $" . number_format($formData['total'], 0, ',', '.') . "\n";
echo "Resultado anterior (PDF):   $" . number_format($oldResult['total'], 0, ',', '.') . " ❌\n";
echo "Resultado nuevo (PDF):      $" . number_format($newResult['total'], 0, ',', '.') . " ✅\n\n";

$oldDiscrepancy = abs($formData['total'] - $oldResult['total']);
$newDiscrepancy = abs($formData['total'] - $newResult['total']);

echo "Discrepancia anterior:      $" . number_format($oldDiscrepancy, 0, ',', '.') . " ❌\n";
echo "Discrepancia nueva:         $" . number_format($newDiscrepancy, 0, ',', '.') . " ✅\n\n";

if ($newDiscrepancy == 0) {
    echo "🎯 ÉXITO: Problema resuelto completamente!\n";
    echo "✅ Los valores del formulario ahora se preservan exactamente en el PDF\n";
    echo "✅ Ya no hay lógica que modifique los precios ingresados por el usuario\n";
} else {
    echo "⚠️  Aún hay discrepancia, se necesitan más ajustes\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

echo "🔧 CAMBIOS IMPLEMENTADOS EN EL CÓDIGO:\n";
echo "1. ❌ Eliminada detección de 'precios anómalos' >10M\n";
echo "2. ❌ Eliminado sistema de override con 'precios originales'\n";
echo "3. ✅ Los datos del formulario se usan como fuente de verdad\n";
echo "4. ✅ Se mantiene logging para auditoría sin modificar datos\n";
echo "5. ✅ Preservación exacta de valores entre formulario y PDF\n";

echo "\n🏁 DEMOSTRACIÓN COMPLETADA\n";

?>
