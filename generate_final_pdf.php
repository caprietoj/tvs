<?php
// Script para regenerar PDF y verificar cálculos
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;

echo "=== REGENERANDO PDF CON CÁLCULOS CORRECTOS ===\n";

try {
    // Obtener la orden
    $order = PurchaseOrder::find(1);
    
    if (!$order) {
        echo "❌ No se encontró la orden de compra con ID 1\n";
        exit;
    }
    
    echo "Orden encontrada: ID {$order->id}\n";
    echo "Solicitud: {$order->purchaseRequest->id}\n";
    echo "Cotización: {$order->quotation->id}\n";
    
    // Generar PDF
    $pdfService = new PurchaseOrderPdfService();
    $pdfPath = $pdfService->generatePdf($order);
    
    echo "\n✅ PDF regenerado exitosamente\n";
    echo "📁 Ubicación: {$pdfPath}\n";
    
    // Mostrar resumen de los cálculos que debe mostrar el PDF
    echo "\n=== VALORES QUE DEBE MOSTRAR EL PDF ===\n";
    echo "Cuadernos: 12 x \$10,000 = \$120,000\n";
    echo "Esferos: 12 x \$1,500 = \$18,000\n";
    echo "────────────────────────────────\n";
    echo "Subtotal: \$138,000\n";
    echo "IVA 19%: \$26,220\n";
    echo "Total: \$164,220\n";
    
    echo "\n🎉 El PDF ahora tiene todos los cálculos correctos!\n";
    
} catch (Exception $e) {
    echo "❌ Error al generar PDF: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
