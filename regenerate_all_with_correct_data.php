<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Regenerando TODAS las órdenes con datos institucionales corregidos...\n";

$orders = App\Models\PurchaseOrder::whereNull('pdf_custom_data')
    ->orWhere('pdf_custom_data', '')
    ->with(['purchaseRequest', 'provider'])
    ->get();

echo "Encontradas " . $orders->count() . " órdenes sin datos personalizados\n";

$pdfService = new App\Services\PurchaseOrderPdfService();
$regenerated = 0;
$errors = 0;

foreach ($orders as $order) {
    try {
        $request = $order->purchaseRequest;
        if ($request && !$request->quotationItemSelections()->exists() && !$request->isNoQuotationService()) {
            echo "Regenerando " . $order->order_number . " con datos institucionales corregidos...\n";
            
            $pdfPath = $pdfService->generatePdf($order);
            $order->file_path = $pdfPath;
            $order->save();
            
            $regenerated++;
        }
    } catch (Exception $e) {
        echo "Error en " . $order->order_number . ": " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "Proceso completado: " . $regenerated . " PDFs regenerados con datos de The Victoria School, " . $errors . " errores\n";
