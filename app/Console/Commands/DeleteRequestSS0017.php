<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItemSelection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteRequestSS0017 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'request:delete-ss0017 {--force : Saltar confirmación}';

    /**
     * The console command description.
     */
    protected $description = 'Eliminar completamente la solicitud SS-0017 de producción';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->error('=== ELIMINACIÓN DE SOLICITUD SS-0017 ===');
        $this->warn('ADVERTENCIA: Este comando eliminará PERMANENTEMENTE la solicitud SS-0017');
        
        if (!$this->option('force')) {
            if (!$this->confirm('¿Está seguro de que desea continuar?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();
            
            // Buscar la solicitud
            $purchaseRequest = PurchaseRequest::where('request_number', 'SS-0017')->first();
            
            if (!$purchaseRequest) {
                $this->error('Solicitud SS-0017 no encontrada.');
                return 1;
            }
            
            $requestId = $purchaseRequest->id;
            $this->info("Solicitud encontrada - ID: {$requestId}");
            
            // Mostrar información
            $this->table(['Campo', 'Valor'], [
                ['Número', $purchaseRequest->request_number],
                ['Solicitante', $purchaseRequest->requester],
                ['Área', $purchaseRequest->section_area],
                ['Estado', $purchaseRequest->status],
                ['Tipo', $purchaseRequest->type],
                ['Fecha creación', $purchaseRequest->created_at],
            ]);
            
            $progress = $this->output->createProgressBar(6);
            $progress->start();
            
            // 1. Eliminar cotizaciones
            $this->info("\n1. Eliminando cotizaciones...");
            $quotations = Quotation::where('purchase_request_id', $requestId)->get();
            $deletedQuotations = 0;
            
            foreach ($quotations as $quotation) {
                // Eliminar archivos
                if ($quotation->file_path && Storage::disk('public')->exists($quotation->file_path)) {
                    Storage::disk('public')->delete($quotation->file_path);
                }
                $quotation->forceDelete();
                $deletedQuotations++;
            }
            $this->info("   Cotizaciones eliminadas: {$deletedQuotations}");
            $progress->advance();
            
            // 2. Eliminar selecciones
            $this->info("2. Eliminando selecciones de items...");
            $deletedSelections = QuotationItemSelection::where('purchase_request_id', $requestId)->forceDelete();
            $this->info("   Selecciones eliminadas: {$deletedSelections}");
            $progress->advance();
            
            // 3. Eliminar órdenes de compra
            $this->info("3. Eliminando órdenes de compra...");
            $orders = PurchaseOrder::where('purchase_request_id', $requestId)->withTrashed()->get();
            $deletedOrders = 0;
            
            foreach ($orders as $order) {
                // Eliminar archivos
                if ($order->file_path && $order->file_path !== 'pending_generation' && Storage::disk('public')->exists($order->file_path)) {
                    Storage::disk('public')->delete($order->file_path);
                }
                if ($order->payment_receipt_path && Storage::disk('public')->exists($order->payment_receipt_path)) {
                    Storage::disk('public')->delete($order->payment_receipt_path);
                }
                $order->forceDelete();
                $deletedOrders++;
            }
            $this->info("   Órdenes eliminadas: {$deletedOrders}");
            $progress->advance();
            
            // 4. Eliminar archivos adjuntos
            $this->info("4. Eliminando archivos adjuntos...");
            $attachments = $purchaseRequest->attachments ?? [];
            $deletedFiles = 0;
            
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                    $deletedFiles++;
                }
            }
            $this->info("   Archivos eliminados: {$deletedFiles}");
            $progress->advance();
            
            // 5. Limpiar referencias
            $this->info("5. Limpiando referencias...");
            $auditLogs = 0;
            $notifications = 0;
            
            try {
                $auditLogs = DB::table('audit_logs')->where('auditable_type', 'App\\Models\\PurchaseRequest')
                               ->where('auditable_id', $requestId)->delete();
            } catch (\Exception $e) {
                // Tabla audit_logs no existe, continuar
            }
            
            try {
                $notifications = DB::table('notifications')->where('data', 'like', '%"purchase_request_id":'.$requestId.'%')->delete();
            } catch (\Exception $e) {
                // Error con notificaciones, continuar
            }
            
            $this->info("   Referencias limpiadas");
            $progress->advance();
            
            // 6. Eliminar solicitud principal
            $this->info("6. Eliminando solicitud principal...");
            $purchaseRequest->forceDelete();
            $progress->advance();
            
            DB::commit();
            $progress->finish();
            
            // Log de la operación
            Log::info("Solicitud SS-0017 eliminada permanentemente", [
                'request_id' => $requestId,
                'deleted_by' => 'Artisan Command',
                'deleted_at' => now(),
                'quotations_deleted' => $deletedQuotations,
                'orders_deleted' => $deletedOrders,
                'files_deleted' => $deletedFiles
            ]);
            
            $this->info("\n\n=== ELIMINACIÓN COMPLETADA ===");
            $this->table(['Elemento', 'Cantidad'], [
                ['Cotizaciones', $deletedQuotations],
                ['Órdenes de compra', $deletedOrders],
                ['Archivos', $deletedFiles],
                ['Logs de auditoría', $auditLogs ?? 0],
                ['Notificaciones', $notifications ?? 0],
            ]);
            
            $this->info('La solicitud SS-0017 ha sido eliminada permanentemente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('ERROR: ' . $e->getMessage());
            
            Log::error("Error al eliminar solicitud SS-0017", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }

        return 0;
    }
}
