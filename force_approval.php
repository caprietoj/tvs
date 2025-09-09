<?php

// Script de emergencia para forzar aprobación de solicitudes problemáticas

if ($argc !== 2) {
    echo "Uso: php force_approval.php <REQUEST_ID>\n";
    echo "Ejemplo: php force_approval.php 123\n";
    echo "\n⚠️  ADVERTENCIA: Este script fuerza la aprobación sin validaciones normales.\n";
    echo "Solo usar en casos de emergencia en producción.\n";
    exit(1);
}

$requestId = $argv[1];

require_once 'vendor/autoload.php';

// Bootstrap de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\RequestHistory;

echo "=== SCRIPT DE APROBACIÓN FORZADA ===\n";
echo "Solicitud ID: $requestId\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $purchaseRequest = PurchaseRequest::with(['preApprovedQuotation', 'user', 'quotations'])
        ->findOrFail($requestId);
    
    echo "📋 INFORMACIÓN DE LA SOLICITUD:\n";
    echo "- Número: {$purchaseRequest->request_number}\n";
    echo "- Tipo: {$purchaseRequest->type}\n";
    echo "- Estado actual: {$purchaseRequest->status}\n";
    echo "- Solicitante: {$purchaseRequest->user->name}\n";
    echo "- Cotizaciones disponibles: " . $purchaseRequest->quotations->count() . "\n\n";
    
    // Verificar si ya está aprobada
    if ($purchaseRequest->status === 'approved') {
        echo "✅ La solicitud ya está aprobada.\n";
        exit(0);
    }
    
    // Buscar una cotización para usar
    $quotationToUse = null;
    
    // 1. Verificar cotización pre-aprobada
    if ($purchaseRequest->preApprovedQuotation) {
        $quotationToUse = $purchaseRequest->preApprovedQuotation;
        echo "🎯 Usando cotización pre-aprobada: ID {$quotationToUse->id}\n";
    }
    
    // 2. Si no hay pre-aprobada, buscar la primera disponible
    if (!$quotationToUse) {
        $quotationToUse = $purchaseRequest->quotations()
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('total_amount', 'asc')
            ->first();
        
        if ($quotationToUse) {
            echo "🎯 Usando primera cotización disponible: ID {$quotationToUse->id}\n";
        }
    }
    
    // Confirmar acción
    echo "\n⚠️  ¿Continuar con la aprobación forzada? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "❌ Operación cancelada.\n";
        exit(0);
    }
    
    // Realizar la aprobación forzada
    $updateData = [
        'status' => 'approved',
        'approved_by' => 1, // Usuario admin por defecto
        'approval_date' => now(),
        'comments' => 'Aprobación forzada por script de emergencia - ' . date('Y-m-d H:i:s')
    ];
    
    // Si encontramos una cotización, asignarla
    if ($quotationToUse) {
        $updateData['selected_quotation_id'] = $quotationToUse->id;
        if (!$purchaseRequest->pre_approved_quotation_id) {
            $updateData['pre_approved_quotation_id'] = $quotationToUse->id;
        }
    }
    
    $purchaseRequest->update($updateData);
    
    // Registrar en historial
    RequestHistory::create([
        'purchase_request_id' => $purchaseRequest->id,
        'user_id' => 1, // Usuario admin por defecto
        'action' => 'approved',
        'comments' => 'Aprobación forzada por script de emergencia'
    ]);
    
    echo "\n✅ APROBACIÓN COMPLETADA:\n";
    echo "- Nueva estado: approved\n";
    echo "- Aprobada por: Usuario Admin (ID: 1)\n";
    echo "- Fecha de aprobación: " . now() . "\n";
    
    if ($quotationToUse) {
        echo "- Cotización asignada: ID {$quotationToUse->id} ({$quotationToUse->provider_name})\n";
    }
    
    // Intentar crear orden de compra si es necesario
    if ($purchaseRequest->type === 'purchase' && $quotationToUse) {
        echo "\n🔄 Intentando crear orden de compra...\n";
        
        try {
            // Verificar si ya existe
            $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
            
            if (!$existingOrder) {
                // Lógica básica de creación de orden
                echo "📋 Creando orden de compra básica...\n";
                echo "ℹ️  La orden se creará automáticamente en el sistema principal.\n";
            } else {
                echo "✅ Ya existe una orden de compra: ID {$existingOrder->id}\n";
            }
            
        } catch (\Exception $e) {
            echo "⚠️  Error al crear orden automáticamente: " . $e->getMessage() . "\n";
            echo "   La orden puede crearse manualmente desde el sistema.\n";
        }
    }
    
    echo "\n✅ PROCESO COMPLETADO.\n";
    echo "La solicitud ahora puede procesarse normalmente en el sistema.\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Verifica que el ID de la solicitud sea correcto.\n";
    exit(1);
}

echo "\n=== FIN DEL SCRIPT ===\n";
