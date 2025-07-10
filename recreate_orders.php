<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RECREAR ÓRDENES DE COMPRA PARA SELECCIÓN MIXTA ===\n\n";

$requestId = 1; // ID de la solicitud que encontramos

$purchaseRequest = \App\Models\PurchaseRequest::find($requestId);

if (!$purchaseRequest) {
    echo "No se encontró la solicitud con ID: {$requestId}\n";
    exit;
}

echo "Solicitud ID: {$purchaseRequest->id}\n";
echo "Estado: {$purchaseRequest->status}\n";

// Obtener selecciones mixtas
$selections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
echo "Total de selecciones: {$selections->count()}\n";

// Mostrar detalles de selecciones
foreach ($selections as $selection) {
    echo "- {$selection->quotation->provider_name}: {$selection->item_description} ($" . number_format($selection->total_price, 0) . ")\n";
}

// Agrupar por proveedor
$grouped = $selections->groupBy('quotation_id');
echo "\nProveedores únicos: {$grouped->count()}\n";

foreach ($grouped as $quotationId => $providerSelections) {
    $provider = $providerSelections->first()->quotation->provider_name;
    $total = $providerSelections->sum('total_price');
    echo "- {$provider}: {$providerSelections->count()} items, Total: $" . number_format($total, 0) . "\n";
}

// Eliminar órdenes existentes
$existingOrders = $purchaseRequest->purchaseOrders()->get();
echo "\nÓrdenes existentes: {$existingOrders->count()}\n";

if ($existingOrders->count() > 0) {
    echo "Eliminando órdenes existentes...\n";
    foreach ($existingOrders as $order) {
        echo "- Eliminando orden #{$order->id}\n";
        $order->delete();
    }
}

// Crear nuevas órdenes usando nuestro método
echo "\nCreando nuevas órdenes individuales...\n";

$orderCounter = 1;

foreach ($grouped as $quotationId => $providerSelections) {
    $quotation = $providerSelections->first()->quotation;
    
    echo "Creando orden para {$quotation->provider_name}...\n";
    
    // Buscar o crear proveedor
    $provider = \App\Models\Proveedor::where('nombre', $quotation->provider_name)->first();
    
    if (!$provider) {
        $provider = \App\Models\Proveedor::create([
            'nombre' => $quotation->provider_name,
            'email' => 'proveedor@contacto.com',
            'telefono' => '000-000-0000',
            'direccion' => 'Por definir',
            'persona_contacto' => 'Por asignar',
            'nit' => '000000000-0'
        ]);
        echo "  Proveedor creado: {$provider->nombre}\n";
    } else {
        echo "  Proveedor encontrado: {$provider->nombre}\n";
    }
    
    // Calcular total para este proveedor
    $totalAmount = $providerSelections->sum('total_price');
    
    // Calcular IVA
    $includesIva = true;
    $subtotal = $totalAmount / 1.19;
    $ivaAmount = $totalAmount - $subtotal;
    
    // Crear orden de compra
    $purchaseOrder = \App\Models\PurchaseOrder::create([
        'purchase_request_id' => $purchaseRequest->id,
        'provider_id' => $provider->id,
        'order_number' => 'ORD-' . str_pad($purchaseRequest->id, 4, '0', STR_PAD_LEFT) . '-' . $orderCounter,
        'total_amount' => $totalAmount,
        'subtotal' => $subtotal,
        'iva_amount' => $ivaAmount,
        'includes_iva' => $includesIva,
        'payment_terms' => $quotation->payment_terms ?? 'Contado',
        'delivery_date' => now()->addDays(15),
        'file_path' => 'pending_generation',
        'observations' => 'Orden recreada automáticamente - Selección mixta - Proveedor: ' . $quotation->provider_name,
        'created_by' => 1, // Asumiendo user ID 1
        'status' => 'pending'
    ]);
    
    echo "  Orden creada: #{$purchaseOrder->id} - {$purchaseOrder->order_number}\n";
    echo "  Total: $" . number_format($totalAmount, 0) . "\n";
    echo "  Items: {$providerSelections->count()}\n";
    
    // Generar PDF
    try {
        $pdfService = new \App\Services\PurchaseOrderPdfService();
        $pdfPath = $pdfService->generatePdf($purchaseOrder, $providerSelections);
        
        if ($pdfPath) {
            $purchaseOrder->update(['file_path' => $pdfPath]);
            echo "  PDF generado: {$pdfPath}\n";
        }
    } catch (\Exception $e) {
        echo "  Error generando PDF: {$e->getMessage()}\n";
    }
    
    $orderCounter++;
    echo "\n";
}

// Verificar resultado final
$finalOrders = $purchaseRequest->purchaseOrders()->get();
echo "=== RESULTADO FINAL ===\n";
echo "Órdenes de compra creadas: {$finalOrders->count()}\n";

foreach ($finalOrders as $order) {
    echo "- Orden #{$order->id}: {$order->order_number}\n";
    echo "  Proveedor: {$order->provider->nombre}\n";
    echo "  Total: $" . number_format($order->total_amount, 0) . "\n";
    echo "  PDF: " . ($order->file_path !== 'pending_generation' ? 'Generado' : 'Pendiente') . "\n";
    echo "\n";
}
