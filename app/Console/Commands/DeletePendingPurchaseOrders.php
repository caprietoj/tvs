<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeletePendingPurchaseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:delete-pending 
                            {--force : Fuerza la eliminación sin confirmación}
                            {--dry-run : Muestra qué se eliminaría sin hacerlo realmente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todas las órdenes de compra en estado pendiente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        try {
            $this->info('🔍 Buscando órdenes de compra pendientes...');

            // Obtener todas las órdenes pendientes
            $pendingOrders = PurchaseOrder::where('status', 'pending')
                ->with('provider', 'purchaseRequest')
                ->get();

            if ($pendingOrders->isEmpty()) {
                $this->info('✅ No se encontraron órdenes pendientes para eliminar.');
                return 0;
            }

            $this->info("📊 Encontradas {$pendingOrders->count()} órdenes pendientes:");
            
            // Mostrar tabla con las órdenes
            $this->table(
                ['ID', 'Número', 'Proveedor', 'Total', 'Solicitud', 'Creada'],
                $pendingOrders->map(function ($order) {
                    return [
                        $order->id,
                        $order->order_number,
                        $order->provider->nombre ?? 'Sin proveedor',
                        '$' . number_format($order->total_amount, 2),
                        $order->purchaseRequest->request_number ?? 'Sin solicitud',
                        $order->created_at->format('d/m/Y H:i')
                    ];
                })->toArray()
            );

            if ($dryRun) {
                $this->warn('🧪 [DRY-RUN] Las siguientes órdenes serían eliminadas:');
                foreach ($pendingOrders as $order) {
                    $this->line("  - Orden #{$order->order_number} (ID: {$order->id})");
                    if ($order->file_path && $order->file_path !== 'pending_generation') {
                        $this->line("    PDF: {$order->file_path}");
                    }
                }
                $this->info('🧪 [DRY-RUN] No se realizaron cambios reales.');
                return 0;
            }

            // Confirmación del usuario
            if (!$force) {
                if (!$this->confirm('⚠️  ¿Estás seguro de que deseas eliminar TODAS estas órdenes pendientes? Esta acción NO se puede deshacer.')) {
                    $this->info('❌ Operación cancelada por el usuario');
                    return 0;
                }

                if (!$this->confirm('🚨 CONFIRMACIÓN FINAL: Esto eliminará permanentemente las órdenes y sus PDFs. ¿Continuar?')) {
                    $this->info('❌ Operación cancelada por el usuario');
                    return 0;
                }
            }

            $deleted = 0;
            $errors = 0;
            $deletedFiles = 0;

            $this->info('🗑️  Iniciando eliminación de órdenes pendientes...');

            foreach ($pendingOrders as $order) {
                $this->line("🔄 Eliminando orden #{$order->order_number}...");
                
                try {
                    DB::beginTransaction();

                    // Eliminar PDF si existe
                    if ($order->file_path && 
                        $order->file_path !== 'pending_generation' && 
                        $order->file_path !== 'error_generation' &&
                        Storage::exists($order->file_path)) {
                        
                        Storage::delete($order->file_path);
                        $deletedFiles++;
                        $this->line("  📄 PDF eliminado: {$order->file_path}");
                    }

                    // Log antes de eliminar
                    Log::info('Eliminando orden pendiente', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'provider_name' => $order->provider->nombre ?? 'Sin proveedor',
                        'total_amount' => $order->total_amount,
                        'purchase_request_id' => $order->purchase_request_id,
                        'file_path' => $order->file_path,
                        'deleted_by' => 'console_command',
                        'reason' => 'preparación_para_creación_manual'
                    ]);

                    // Eliminar la orden (soft delete)
                    $order->delete();

                    DB::commit();
                    $deleted++;
                    $this->line("  ✅ Eliminada exitosamente");

                } catch (\Exception $e) {
                    DB::rollback();
                    $errors++;
                    $this->error("  ❌ Error: " . $e->getMessage());
                    Log::error('Error eliminando orden pendiente', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->newLine();
            $this->info('📊 Resumen de eliminación:');
            $this->line("  ✅ Órdenes eliminadas: {$deleted}");
            $this->line("  📄 PDFs eliminados: {$deletedFiles}");
            $this->line("  ❌ Errores: {$errors}");

            if ($errors > 0) {
                $this->warn('⚠️  Revisa los logs para más detalles sobre los errores');
                return 1;
            }

            $this->info('🎉 ¡Proceso completado exitosamente!');
            $this->info('💡 Ahora puedes crear las órdenes manualmente usando el nuevo sistema.');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error general en eliminación: ' . $e->getMessage());
            Log::error('Error general en eliminación de órdenes pendientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
