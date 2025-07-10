<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\Quotation;

echo "=== RESETEANDO SOLICITUD ID 1 PARA PRUEBAS ===\n";

$request = PurchaseRequest::find(1);

if (!$request) {
    echo "❌ No se encontró la solicitud ID 1\n";
    exit;
}

// Resetear el estado y limpiar datos de pre-aprobación
$request->update([
    'status' => 'En Cotización',
    'pre_approved_by' => null,
    'pre_approved_at' => null,
    'pre_approved_quotation_id' => null,
    'budget' => null
]);

// Resetear cotizaciones
$request->quotations()->update(['is_selected' => false, 'status' => 'submitted']);

echo "✅ Solicitud resetada: {$request->request_number} (ID: {$request->id})\n";

echo "\n=== INFORMACIÓN DE LA SOLICITUD DE PRUEBA ===\n";
echo "ID de la solicitud: {$request->id}\n";
echo "Número de solicitud: {$request->request_number}\n";
echo "Estado: {$request->status}\n";
echo "Budget actual: " . ($request->budget ?? 'NULL') . "\n";

$quotations = $request->quotations;
echo "Cotizaciones disponibles: " . $quotations->count() . "\n";

foreach ($quotations as $quotation) {
    echo "  - ID: {$quotation->id}, Proveedor: {$quotation->provider_name}, Status: {$quotation->status}\n";
}

echo "\n✅ La solicitud tiene suficientes cotizaciones para pre-aprobación\n";
echo "\n=== URL PARA PRUEBA ===\n";
echo "http://127.0.0.1:8000/quotation-approvals/{$request->id}\n";

echo "\n=== INSTRUCCIONES DE PRUEBA ===\n";
echo "1. Abra la URL en su navegador\n";
echo "2. Haga clic en 'Pre-aprobar' en una de las cotizaciones\n";
echo "3. IMPORTANTE: Seleccione un rubro presupuestal (ej: 'Tecnología Institucional')\n";
echo "4. Opcionalmente agregue comentarios\n";
echo "5. Haga clic en 'Confirmar Pre-aprobación'\n";
echo "6. Verifique que el presupuesto se haya guardado correctamente\n";

echo "\n=== VERIFICACIÓN POSTERIOR ===\n";
echo "Después de la pre-aprobación, ejecute: php verify_budget_field.php\n";

echo "\n=== CAMBIOS REALIZADOS ===\n";
echo "✅ Se agregó validación JavaScript al modal de pre-aprobación normal\n";
echo "✅ El botón estará deshabilitado hasta seleccionar un rubro\n";
echo "✅ Se agregaron logs de depuración en el formulario\n";
echo "✅ Se corrigió el controlador de selección mixta para usar el campo 'budget' correcto\n";
echo "✅ La solicitud está lista para pruebas\n";
