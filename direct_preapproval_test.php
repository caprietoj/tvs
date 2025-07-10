<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\Quotation;

echo "=== SIMULACIÓN DIRECTA DE PRE-APROBACIÓN ===\n";

$requestId = 1;
$request = PurchaseRequest::find($requestId);

if (!$request) {
    echo "❌ No se encontró la solicitud\n";
    exit;
}

if ($request->status !== 'En Cotización') {
    echo "⚠️  La solicitud no está en estado 'En Cotización'\n";
    echo "Estado actual: {$request->status}\n";
    echo "Reseteando para la prueba...\n";
    
    $request->update([
        'status' => 'En Cotización',
        'pre_approved_by' => null,
        'pre_approved_at' => null,
        'pre_approved_quotation_id' => null,
        'budget' => null
    ]);
    $request->refresh();
}

$quotation = $request->quotations()->first();

if (!$quotation) {
    echo "❌ No se encontraron cotizaciones\n";
    exit;
}

echo "Solicitud: {$request->request_number}\n";
echo "Estado inicial: {$request->status}\n";
echo "Budget inicial: " . ($request->budget ?? 'NULL') . "\n";
echo "Cotización: {$quotation->provider_name}\n";

// Simular exactamente lo que hace el controlador
echo "\n=== SIMULANDO VALIDACIÓN ===\n";
$validated = [
    'quotation_id' => $quotation->id,
    'comments' => 'Comentario de prueba',
    'budget' => 'Tecnología Institucional'
];

echo "Datos validados:\n";
foreach ($validated as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n=== ACTUALIZANDO COTIZACIÓN ===\n";
// Desmarcar cualquier otra cotización
Quotation::where('purchase_request_id', $request->id)
    ->update(['is_selected' => false]);

// Actualizar la cotización seleccionada
$quotation->update([
    'status' => 'pre-approved',
    'is_selected' => true,
    'pre_approval_date' => now(),
    'pre_approval_comments' => $validated['comments'] ?? null,
    'pre_approved_by' => 1, // Simular usuario 1
]);

echo "✅ Cotización actualizada\n";

echo "\n=== ACTUALIZANDO SOLICITUD ===\n";
// Actualizar la solicitud
$updateData = [
    'status' => 'Pre-aprobada',
    'pre_approved_quotation_id' => $quotation->id,
    'pre_approved_by' => 1,
    'pre_approved_at' => now(),
    'budget' => $validated['budget']
];

echo "Datos a actualizar:\n";
foreach ($updateData as $key => $value) {
    echo "  {$key}: {$value}\n";
}

$request->update($updateData);
$request->refresh();

echo "✅ Solicitud actualizada\n";

echo "\n=== VERIFICACIÓN FINAL ===\n";
echo "Estado final: {$request->status}\n";
echo "Budget final: " . ($request->budget ?? 'NULL') . "\n";
echo "Pre-aprobada en: {$request->pre_approved_at}\n";

if ($request->budget === $validated['budget']) {
    echo "\n✅ SUCCESS: El budget se guardó correctamente!\n";
    echo "El problema NO está en la lógica de guardado.\n";
    echo "El problema debe estar en:\n";
    echo "1. El formulario HTML no está enviando el campo\n";
    echo "2. Algún middleware está interfiriendo\n";
    echo "3. Un error en JavaScript impide el envío\n";
} else {
    echo "\n❌ ERROR: El budget no se guardó\n";
    echo "Esto indica un problema en la lógica de actualización\n";
}

echo "\n=== RECOMENDACIÓN ===\n";
echo "Como la lógica funciona directamente, el problema está en el frontend.\n";
echo "Revise:\n";
echo "1. Herramientas de desarrollador del navegador\n";
echo "2. Network tab para ver qué datos se envían\n";
echo "3. Console para errores JavaScript\n";
echo "4. Que el botón del modal no esté deshabilitado\n";
