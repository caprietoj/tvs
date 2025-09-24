<?php

/**
 * Script para investigar la solicitud 780 y entender por qué falla la validación
 */

require_once 'c:/xampp/htdocs/tvs/vendor/autoload.php';

$app = require_once 'c:/xampp/htdocs/tvs/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\QuotationItemSelection;

echo "=== INVESTIGACIÓN DE SOLICITUD 780 ===\n\n";

// Buscar la solicitud 780
$purchaseRequest = PurchaseRequest::with(['quotations', 'preApprovedQuotation', 'quotationItemSelections'])
    ->find(780);

if (!$purchaseRequest) {
    echo "❌ No se encontró la solicitud 780\n";
    echo "Buscando solicitudes recientes...\n";
    
    $recentRequests = PurchaseRequest::orderBy('id', 'desc')->take(5)->get(['id', 'status', 'type']);
    echo "Solicitudes recientes:\n";
    foreach ($recentRequests as $req) {
        echo "  - ID: {$req->id}, Tipo: {$req->type}, Estado: {$req->status}\n";
    }
    exit;
}

echo "=== DATOS DE LA SOLICITUD ===\n";
echo "ID: {$purchaseRequest->id}\n";
echo "Tipo: {$purchaseRequest->type}\n";
echo "Estado: {$purchaseRequest->status}\n";
echo "Sección: {$purchaseRequest->section_area}\n";
echo "Usuario: " . ($purchaseRequest->user ? $purchaseRequest->user->name : 'No encontrado') . "\n";
echo "Creada: {$purchaseRequest->created_at}\n\n";

echo "=== VALIDACIONES QUE SE EJECUTAN ===\n";

// Simular las validaciones del método approve()
if ($purchaseRequest->type === 'purchase') {
    if (in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada'])) {
        echo "✅ Es solicitud de compra en estado pre-aprobado\n\n";
        
        // 1. Verificar cotización pre-aprobada tradicional
        $hasPreApprovedQuotation = $purchaseRequest->preApprovedQuotation !== null;
        echo "1. hasPreApprovedQuotation: " . ($hasPreApprovedQuotation ? '✅ SÍ' : '❌ NO') . "\n";
        if ($hasPreApprovedQuotation) {
            echo "   - Cotización pre-aprobada ID: {$purchaseRequest->pre_approved_quotation_id}\n";
        }
        
        // 2. Verificar selección mixta completa
        $quotationSelections = $purchaseRequest->quotationItemSelections;
        $selectionsCount = $quotationSelections->count();
        
        // Obtener items de la solicitud
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
        
        $itemsCount = empty($purchaseItems) ? 0 : count($purchaseItems);
        
        echo "2. hasMixedSelection: ";
        if (empty($purchaseItems)) {
            // Para solicitudes simples sin items detallados
            $hasMixedSelection = $selectionsCount > 0;
            echo ($hasMixedSelection ? '✅ SÍ' : '❌ NO') . " (solicitud simple)\n";
            echo "   - Selecciones existentes: {$selectionsCount}\n";
        } else {
            // NUEVA LÓGICA: Para solicitudes con items detallados, solo requiere AL MENOS UNA selección
            $hasMixedSelection = $selectionsCount > 0;
            echo ($hasMixedSelection ? '✅ SÍ' : '❌ NO') . " (requiere al menos 1 selección)\n";
            echo "   - Items en solicitud: {$itemsCount}\n";
            echo "   - Selecciones existentes: {$selectionsCount}\n";
            echo "   - Lógica anterior (completa): " . ($selectionsCount === $itemsCount ? 'SÍ' : 'NO') . "\n";
            echo "   - Nueva lógica (al menos 1): " . ($selectionsCount > 0 ? 'SÍ' : 'NO') . "\n";
        }
        
        // 3. Verificar pre-aprobada sin cotización
        $quotationsCount = $purchaseRequest->quotations->count();
        $isPreApprovedWithoutQuotation = ($quotationsCount === 0) && ($purchaseRequest->preApprovedQuotation === null);
        echo "3. isPreApprovedWithoutQuotation: " . ($isPreApprovedWithoutQuotation ? '✅ SÍ' : '❌ NO') . "\n";
        echo "   - Cotizaciones totales: {$quotationsCount}\n";
        
        // 4. Verificar cotización disponible
        $availableQuotationsCount = $purchaseRequest->quotations()->whereIn('status', ['pending', 'approved'])->count();
        $hasAvailableQuotation = $availableQuotationsCount > 0;
        echo "4. hasAvailableQuotation: " . ($hasAvailableQuotation ? '✅ SÍ' : '❌ NO') . "\n";
        echo "   - Cotizaciones disponibles (pending/approved): {$availableQuotationsCount}\n\n";
        
        // Resultado final
        $validForApproval = $hasPreApprovedQuotation || $hasMixedSelection || $isPreApprovedWithoutQuotation || $hasAvailableQuotation;
        echo "=== RESULTADO FINAL ===\n";
        echo "validForApproval: " . ($validForApproval ? '✅ VÁLIDA' : '❌ NO VÁLIDA') . "\n";
        
        if (!$validForApproval) {
            echo "\n🔧 SOLUCIONES POSIBLES:\n";
            echo "1. Usar force_approve: Agregar ?force_approve=1 a la URL\n";
            echo "2. Pre-aprobar una cotización específica\n";
            echo "3. Completar la selección mixta de proveedores\n";
            echo "4. Verificar que las cotizaciones tengan estado 'approved' o 'pending'\n";
        }
        
    } else {
        echo "❌ La solicitud no está en estado pre-aprobado\n";
        echo "Estado actual: {$purchaseRequest->status}\n";
    }
} else {
    echo "ℹ️ Esta no es una solicitud de compra (tipo: {$purchaseRequest->type})\n";
}

echo "\n=== DETALLES ADICIONALES ===\n";

// Mostrar cotizaciones disponibles
echo "Cotizaciones asociadas:\n";
foreach ($purchaseRequest->quotations as $quotation) {
    echo "  - ID: {$quotation->id}, Proveedor: {$quotation->provider_name}, Estado: {$quotation->status}, Total: $" . number_format($quotation->total_amount, 2) . "\n";
}

// Mostrar selecciones de items
if ($selectionsCount > 0) {
    echo "\nSelecciones de items mixtos:\n";
    foreach ($quotationSelections as $selection) {
        echo "  - Item: {$selection->item_description}, Proveedor: " . ($selection->quotation ? $selection->quotation->provider_name : 'N/A') . ", Total: $" . number_format($selection->total_price, 2) . "\n";
    }
}

// Mostrar items de la solicitud
if (!empty($purchaseItems)) {
    echo "\nItems de la solicitud original:\n";
    foreach ($purchaseItems as $index => $item) {
        echo "  - {$index}: {$item['description']}, Cantidad: {$item['quantity']}, Unidad: " . ($item['unit'] ?? 'N/A') . "\n";
    }
}

echo "\n=== FIN DE LA INVESTIGACIÓN ===\n";

?>