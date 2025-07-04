<?php
/**
 * Script para verificar solicitudes pre-aprobadas sin cotización
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;

echo "=== ANÁLISIS DE SOLICITUDES PRE-APROBADAS SIN COTIZACIÓN ===\n\n";

// Verificar solicitudes pre-aprobadas sin cotizaciones
$preApprovedRequests = PurchaseRequest::where('status', 'Pre-aprobada')
    ->with(['quotations', 'preApprovedQuotation'])
    ->get();

echo "Total de solicitudes pre-aprobadas: " . $preApprovedRequests->count() . "\n\n";

foreach ($preApprovedRequests as $request) {
    echo "--- Solicitud #{$request->request_number} (ID: {$request->id}) ---\n";
    echo "Estado: {$request->status}\n";
    echo "Tipo: {$request->type}\n";
    echo "Cotizaciones: " . $request->quotations->count() . "\n";
    echo "Pre-approved Quotation ID: " . ($request->pre_approved_quotation_id ?? 'NULL') . "\n";
    echo "Budget Line: " . ($request->budget_line ?? 'NULL') . "\n";
    echo "Pre-approval Comments: " . ($request->pre_approval_comments ?? 'NULL') . "\n";
    echo "Pre-approved By: " . ($request->pre_approved_by ?? 'NULL') . "\n";
    echo "Pre-approval Date: " . ($request->pre_approval_date ?? 'NULL') . "\n";
    
    // Determinar si fue pre-aprobada sin cotización
    $hasQuotations = $request->quotations->count() > 0;
    $hasPreApprovedQuotation = $request->pre_approved_quotation_id !== null;
    $hasComments = !empty($request->pre_approval_comments);
    $hasBudgetLine = !empty($request->budget_line);
    
    if (!$hasQuotations && !$hasPreApprovedQuotation && $hasComments && $hasBudgetLine) {
        echo "✅ IDENTIFICADA COMO: Pre-aprobada SIN cotización\n";
        echo "   → Debería ser válida para aprobación final\n";
    } elseif ($hasPreApprovedQuotation) {
        echo "✅ IDENTIFICADA COMO: Pre-aprobada CON cotización tradicional\n";
    } else {
        echo "⚠️  CASO AMBIGUO: No se puede determinar claramente el tipo\n";
    }
    
    echo "\n";
}

echo "=== ANÁLISIS COMPLETADO ===\n";
