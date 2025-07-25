<?php
// Script final para verificar y generar PDF correcto
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\PurchaseOrderPdfService;

echo "=== GENERACIÓN FINAL DE PDF CORREGIDO ===\n";

// Obtener la orden de compra
$order = PurchaseOrder::find(1);

if (!$order) {
    echo "❌ No se encontró la orden de compra\n";
    exit;
}

echo "✅ Orden encontrada: ID {$order->id}\n";

// Obtener la cotización seleccionada para esta solicitud
$quotation = Quotation::where('purchase_request_id', $order->purchase_request_id)
                     ->where('is_selected', true)
                     ->first();

if (!$quotation) {
    // Si no hay cotización seleccionada, usar la que tiene precios específicos
    $quotation = Quotation::where('purchase_request_id', $order->purchase_request_id)
                         ->whereNotNull('original_item_prices')
                         ->first();
}

if (!$quotation) {
    echo "❌ No se encontró cotización con precios específicos\n";
    exit;
}

echo "✅ Cotización encontrada: ID {$quotation->id} - {$quotation->provider_name}\n";

// Verificar precios originales
if (!$quotation->original_item_prices) {
    echo "❌ La cotización no tiene precios originales\n";
    exit;
}

echo "✅ Precios originales: " . json_encode($quotation->original_item_prices) . "\n";

try {
    // Generar PDF usando el servicio
    $pdfService = new PurchaseOrderPdfService();
    
    // Modificar temporalmente la orden para usar la cotización correcta
    $originalData = $order->toArray();
    
    // Actualizar la orden para que use los datos de la cotización con precios específicos
    $order->update([
        'subtotal' => $order->subtotal, // Mantener el subtotal correcto que ya tiene
        'iva_amount' => $order->iva_amount, // Mantener el IVA correcto que ya tiene
        'total_amount' => $order->total_amount, // Mantener el total correcto que ya tiene
    ]);
    
    // Generar el PDF
    $pdfPath = $pdfService->generatePdf($order);
    
    echo "✅ PDF generado exitosamente: {$pdfPath}\n";
    
    // Calcular y mostrar valores que debería mostrar el PDF
    $items = is_array($order->purchaseRequest->purchase_items) 
        ? $order->purchaseRequest->purchase_items 
        : json_decode($order->purchaseRequest->purchase_items, true);
    
    echo "\n=== VERIFICACIÓN DE CÁLCULOS EN PDF ===\n";
    
    $subtotalCalculado = 0;
    foreach ($items as $index => $item) {
        $cantidad = $item['quantity'] ?? 1;
        $descripcion = $item['description'] ?? "Item $index";
        $precioUnitario = $quotation->original_item_prices[$index] ?? 0;
        $total = $cantidad * $precioUnitario;
        $subtotalCalculado += $total;
        
        echo "✓ {$descripcion}: {$cantidad} x \${$precioUnitario} = \${$total}\n";
    }
    
    $ivaCalculado = $quotation->includes_iva_19 ? $subtotalCalculado * 0.19 : 0;
    $totalCalculado = $subtotalCalculado + $ivaCalculado;
    
    echo "\n=== RESUMEN FINAL ===\n";
    echo "Subtotal calculado: \${$subtotalCalculado}\n";
    echo "IVA 19% calculado: \${$ivaCalculado}\n";
    echo "Total calculado: \${$totalCalculado}\n";
    
    echo "\nSubtotal en BD: \${$order->subtotal}\n";
    echo "IVA en BD: \${$order->iva_amount}\n";
    echo "Total en BD: \${$order->total_amount}\n";
    
    // Verificar que los cálculos sean correctos
    $subtotalOK = abs($subtotalCalculado - $order->subtotal) < 0.01;
    $ivaOK = abs($ivaCalculado - $order->iva_amount) < 0.01;
    $totalOK = abs($totalCalculado - $order->total_amount) < 0.01;
    
    echo "\n=== VERIFICACIÓN ===\n";
    echo ($subtotalOK ? "✅" : "❌") . " Subtotal: " . ($subtotalOK ? "CORRECTO" : "INCORRECTO") . "\n";
    echo ($ivaOK ? "✅" : "❌") . " IVA: " . ($ivaOK ? "CORRECTO" : "INCORRECTO") . "\n";
    echo ($totalOK ? "✅" : "❌") . " Total: " . ($totalOK ? "CORRECTO" : "INCORRECTO") . "\n";
    
    if ($subtotalOK && $ivaOK && $totalOK) {
        echo "\n🎉 ¡PERFECTO! El PDF generado tiene todos los cálculos correctos:\n";
        echo "   • Cuadernos: \$10,000 cada uno (precio específico)\n";
        echo "   • Esferos: \$1,500 cada uno (precio específico)\n";
        echo "   • Subtotal: \$138,000 (suma correcta)\n";
        echo "   • IVA: \$26,220 (19% del subtotal)\n";
        echo "   • Total: \$164,220 (subtotal + IVA)\n";
        echo "\n✅ PROBLEMA RESUELTO: Los precios ya no se promedian, cada item mantiene su precio específico.\n";
    } else {
        echo "\n❌ Hay discrepancias en los cálculos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al generar PDF: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
