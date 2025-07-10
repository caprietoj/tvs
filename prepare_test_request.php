<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\Quotation;

echo "=== PREPARANDO SOLICITUD EXISTENTE PARA PRUEBAS ===\n";

// Buscar solicitud con estado adecuado
$request = PurchaseRequest::where('status', 'En Cotización')
    ->orWhere('status', 'En pre-aprobación')
    ->first();

if (!$request) {
    // Si no hay solicitudes en cotización, buscar cualquiera y cambiarle el estado
    $request = PurchaseRequest::whereHas('quotations', function($query) {
        $query->where('id', '>', 0);
    })->first();
    
    if ($request) {
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
    }
}

if (!$request) {
    echo "❌ No se encontró ninguna solicitud con cotizaciones\n";
    exit;
}

echo "=== INFORMACIÓN DE LA SOLICITUD DE PRUEBA ===\n";
echo "ID de la solicitud: {$request->id}\n";
echo "Número de solicitud: {$request->request_number}\n";
echo "Estado: {$request->status}\n";
echo "Budget actual: " . ($request->budget ?? 'NULL') . "\n";

$quotations = $request->quotations;
echo "Cotizaciones disponibles: " . $quotations->count() . "\n";

foreach ($quotations as $quotation) {
    echo "  - ID: {$quotation->id}, Proveedor: {$quotation->provider_name}, Status: {$quotation->status}\n";
}

if ($quotations->count() >= 2) {
    echo "\n✅ La solicitud tiene suficientes cotizaciones para pre-aprobación\n";
    echo "\n=== URL PARA PRUEBA ===\n";
    echo "http://127.0.0.1:8000/quotation-approvals/{$request->id}\n";
    
    echo "\n=== INSTRUCCIONES DE PRUEBA ===\n";
    echo "1. Abra la URL en su navegador\n";
    echo "2. Haga clic en 'Pre-aprobar' en una de las cotizaciones\n";
    echo "3. IMPORTANTE: Seleccione un rubro presupuestal (ej: 'Tecnología Institucional')\n";
    echo "4. Opcionalmente agregue comentarios\n";
    echo "5. Haga clic en 'Confirmar Pre-aprobación'\n";
    echo "6. Vaya a la lista de órdenes de compra para ver el PDF generado\n";
    echo "7. Verifique que el rubro presupuestal aparezca en el PDF\n";
    
    echo "\n=== VERIFICACIÓN POSTERIOR ===\n";
    echo "Después de la pre-aprobación, ejecute: php verify_budget_after_preapproval.php {$request->id}\n";
} else {
    echo "\n❌ La solicitud necesita al menos 2 cotizaciones para pre-aprobación\n";
}

echo "\n=== VALIDACIONES AGREGADAS ===\n";
echo "✅ Se agregó validación JavaScript al modal de pre-aprobación\n";
echo "✅ El botón estará deshabilitado hasta seleccionar un rubro\n";
echo "✅ Se agregaron logs de depuración en el formulario\n";
echo "✅ Se corrigió el controlador de selección mixta para usar el campo 'budget'\n";
