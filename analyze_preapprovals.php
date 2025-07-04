<?php
/**
 * Script para verificar qué solicitudes están apareciendo en preaprobaciones
 * y si tienen cotizaciones o no
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseRequest;

echo "=== ANÁLISIS DE SOLICITUDES EN PREAPROBACIONES ===\n\n";

// Replicar la consulta del método index
$requests = PurchaseRequest::whereIn('status', ['En Cotización', 'En pre-aprobación', 'Pre-aprobada'])
    ->where(function($query) {
        $query->where('type', '!=', 'services')
              ->orWhere(function($subQuery) {
                  $subQuery->where('type', 'services')
                           ->where(function($serviceQuery) {
                               $serviceQuery->whereNull('service_type')
                                           ->orWhere('service_type', 'regular');
                           });
              });
    })
    ->with(['quotations'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de solicitudes encontradas: " . $requests->count() . "\n\n";

foreach ($requests as $request) {
    echo "--- Solicitud #{$request->request_number} ---\n";
    echo "ID: {$request->id}\n";
    echo "Estado: {$request->status}\n";
    echo "Tipo: {$request->type}\n";
    echo "Service Type: " . ($request->service_type ?? 'NULL') . "\n";
    echo "Cotizaciones: " . $request->quotations->count() . "\n";
    echo "Solicitante: " . ($request->user->name ?? 'N/A') . "\n";
    echo "Fecha: " . $request->created_at->format('d/m/Y H:i') . "\n";
    
    if ($request->quotations->count() === 0) {
        echo "⚠️  ESTA SOLICITUD NO TIENE COTIZACIONES\n";
        
        // Verificar si es un servicio sin cotización
        if ($request->type === 'services' && method_exists($request, 'isNoQuotationService')) {
            $isNoQuotation = $request->isNoQuotationService();
            echo "Es servicio sin cotización: " . ($isNoQuotation ? 'SÍ' : 'NO') . "\n";
        }
    }
    
    echo "\n";
}

echo "=== ANÁLISIS COMPLETADO ===\n";
