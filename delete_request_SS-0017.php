<?php
/**
 * Script para eliminar completamente la solicitud SS-0017 de producción
 * 
 * ATENCIÓN: Este script eliminará PERMANENTEMENTE todos los datos relacionados con SS-0017
 * Ejecutar solo si está seguro de que desea eliminar esta solicitud.
 * 
 * Uso: php delete_request_SS-0017.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItemSelection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "=== SCRIPT DE ELIMINACIÓN DE SOLICITUD SS-0017 ===" . PHP_EOL;
echo "ADVERTENCIA: Este script eliminará PERMANENTEMENTE la solicitud SS-0017" . PHP_EOL;
echo "¿Está seguro de continuar? (escriba 'SI' para confirmar): ";

$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'SI') {
    echo "Operación cancelada." . PHP_EOL;
    exit(0);
}

try {
    DB::beginTransaction();
    
    // Buscar la solicitud
    $purchaseRequest = PurchaseRequest::where('request_number', 'SS-0017')->first();
    
    if (!$purchaseRequest) {
        echo "ERROR: Solicitud SS-0017 no encontrada." . PHP_EOL;
        exit(1);
    }
    
    $requestId = $purchaseRequest->id;
    echo "Solicitud encontrada - ID: {$requestId}" . PHP_EOL;
    
    // Verificar y mostrar datos antes de eliminar
    echo PHP_EOL . "Datos de la solicitud a eliminar:" . PHP_EOL;
    echo "- Número: {$purchaseRequest->request_number}" . PHP_EOL;
    echo "- Solicitante: {$purchaseRequest->requester}" . PHP_EOL;
    echo "- Área: {$purchaseRequest->section_area}" . PHP_EOL;
    echo "- Estado: {$purchaseRequest->status}" . PHP_EOL;
    echo "- Tipo: {$purchaseRequest->type}" . PHP_EOL;
    echo "- Fecha creación: {$purchaseRequest->created_at}" . PHP_EOL;
    
    // 1. Eliminar cotizaciones y sus relaciones
    echo PHP_EOL . "1. Eliminando cotizaciones..." . PHP_EOL;
    $quotations = Quotation::where('purchase_request_id', $requestId)->get();
    foreach ($quotations as $quotation) {
        echo "   - Eliminando cotización ID: {$quotation->id}" . PHP_EOL;
        
        // Eliminar archivos de cotización si existen
        if ($quotation->file_path && Storage::disk('public')->exists($quotation->file_path)) {
            Storage::disk('public')->delete($quotation->file_path);
            echo "     Archivo eliminado: {$quotation->file_path}" . PHP_EOL;
        }
        
        $quotation->forceDelete(); // Eliminación permanente
    }
    echo "   Total cotizaciones eliminadas: " . $quotations->count() . PHP_EOL;
    
    // 2. Eliminar selecciones de items de cotización
    echo "2. Eliminando selecciones de items..." . PHP_EOL;
    $deletedSelections = QuotationItemSelection::where('purchase_request_id', $requestId)->forceDelete();
    echo "   Total selecciones eliminadas: {$deletedSelections}" . PHP_EOL;
    
    // 3. Eliminar órdenes de compra (incluidas soft-deleted)
    echo "3. Eliminando órdenes de compra..." . PHP_EOL;
    $orders = PurchaseOrder::where('purchase_request_id', $requestId)->withTrashed()->get();
    foreach ($orders as $order) {
        echo "   - Eliminando orden ID: {$order->id} - {$order->order_number}" . PHP_EOL;
        
        // Eliminar archivo PDF si existe
        if ($order->file_path && $order->file_path !== 'pending_generation' && Storage::disk('public')->exists($order->file_path)) {
            Storage::disk('public')->delete($order->file_path);
            echo "     Archivo PDF eliminado: {$order->file_path}" . PHP_EOL;
        }
        
        // Eliminar comprobante de pago si existe
        if ($order->payment_receipt_path && Storage::disk('public')->exists($order->payment_receipt_path)) {
            Storage::disk('public')->delete($order->payment_receipt_path);
            echo "     Comprobante de pago eliminado: {$order->payment_receipt_path}" . PHP_EOL;
        }
        
        $order->forceDelete(); // Eliminación permanente
    }
    echo "   Total órdenes eliminadas: " . $orders->count() . PHP_EOL;
    
    // 4. Eliminar archivos adjuntos de la solicitud
    echo "4. Eliminando archivos adjuntos..." . PHP_EOL;
    $attachments = $purchaseRequest->attachments ?? [];
    $deletedFiles = 0;
    
    foreach ($attachments as $attachment) {
        if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
            Storage::disk('public')->delete($attachment['path']);
            echo "   - Archivo eliminado: {$attachment['path']}" . PHP_EOL;
            $deletedFiles++;
        }
    }
    echo "   Total archivos eliminados: {$deletedFiles}" . PHP_EOL;
    
    // 5. Verificar otras relaciones en tablas que podrían tener referencias
    echo "5. Verificando otras referencias..." . PHP_EOL;
    
    // Verificar en logs de auditoría (si existe)
    $auditLogs = DB::table('audit_logs')->where('auditable_type', 'App\\Models\\PurchaseRequest')
                   ->where('auditable_id', $requestId)->count();
    if ($auditLogs > 0) {
        DB::table('audit_logs')->where('auditable_type', 'App\\Models\\PurchaseRequest')
           ->where('auditable_id', $requestId)->delete();
        echo "   - Logs de auditoría eliminados: {$auditLogs}" . PHP_EOL;
    }
    
    // Verificar en notificaciones (si existe tabla)
    $notifications = DB::table('notifications')->where('data', 'like', '%"purchase_request_id":'.$requestId.'%')->count();
    if ($notifications > 0) {
        DB::table('notifications')->where('data', 'like', '%"purchase_request_id":'.$requestId.'%')->delete();
        echo "   - Notificaciones eliminadas: {$notifications}" . PHP_EOL;
    }
    
    // 6. Finalmente, eliminar la solicitud principal
    echo "6. Eliminando solicitud principal..." . PHP_EOL;
    $purchaseRequest->forceDelete(); // Eliminación permanente
    echo "   Solicitud SS-0017 eliminada permanentemente." . PHP_EOL;
    
    // Confirmar transacción
    DB::commit();
    
    // Log de la operación
    Log::info("Solicitud SS-0017 eliminada permanentemente", [
        'request_id' => $requestId,
        'deleted_by' => 'Script de eliminación',
        'deleted_at' => now(),
        'quotations_deleted' => $quotations->count(),
        'orders_deleted' => $orders->count(),
        'files_deleted' => $deletedFiles
    ]);
    
    echo PHP_EOL . "=== ELIMINACIÓN COMPLETADA EXITOSAMENTE ===" . PHP_EOL;
    echo "La solicitud SS-0017 y todos sus datos relacionados han sido eliminados permanentemente." . PHP_EOL;
    echo "Total de elementos eliminados:" . PHP_EOL;
    echo "- Cotizaciones: " . $quotations->count() . PHP_EOL;
    echo "- Órdenes de compra: " . $orders->count() . PHP_EOL;
    echo "- Archivos: {$deletedFiles}" . PHP_EOL;
    echo "- Logs de auditoría: {$auditLogs}" . PHP_EOL;
    echo "- Notificaciones: {$notifications}" . PHP_EOL;
    
} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "La operación ha sido revertida." . PHP_EOL;
    
    Log::error("Error al eliminar solicitud SS-0017", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    exit(1);
}

echo PHP_EOL . "Script finalizado." . PHP_EOL;
