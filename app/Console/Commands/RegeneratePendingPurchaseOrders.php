<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegeneratePendingPurchaseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:regenerate-pending 
                            {--force : Forzar regeneración incluso si no hay errores}
                            {--dry-run : Solo mostrar qué se haría sin ejecutar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenera automáticamente todas las órdenes de compra con estado pendiente aplicando las mejoras de selección mixta';

    protected $pdfService;

    /**
     * Create a new command instance.
     */
    public function __construct(PurchaseOrderPdfService $pdfService)
    {
        parent::__construct();
        $this->pdfService = $pdfService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando regeneración de órdenes de compra pendientes...');
        
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        if ($dryRun) {
            $this->warn('📋 MODO DRY-RUN: Solo se mostrarán los cambios, no se ejecutarán');
        }

        try {
            // Obtener órdenes pendientes con selección mixta
            $pendingOrders = PurchaseOrder::with([
                'purchaseRequest.quotationItemSelections.quotation',
                'provider'
            ])
            ->where('status', 'pending')
            ->whereHas('purchaseRequest.quotationItemSelections')
            ->get();

            $this->info("📊 Encontradas {$pendingOrders->count()} órdenes pendientes con selección mixta");

            if ($pendingOrders->isEmpty()) {
                $this->info('✅ No hay órdenes pendientes con selección mixta para regenerar');
                return 0;
            }

            $this->table(
                ['ID', 'Número', 'Proveedor', 'Total', 'Creada'],
                $pendingOrders->map(function($order) {
                    return [
                        $order->id,
                        $order->order_number,
                        $order->provider->nombre ?? 'N/A',
                        '$' . number_format($order->total_amount, 2),
                        $order->created_at->format('d/m/Y H:i')
                    ];
                })->toArray()
            );

            if (!$this->confirm('¿Deseas continuar con la regeneración de estas órdenes?')) {
                $this->info('❌ Operación cancelada por el usuario');
                return 0;
            }

            $regenerated = 0;
            $errors = 0;
            $skipped = 0;

            foreach ($pendingOrders as $order) {
                $this->info("🔄 Procesando orden #{$order->order_number}...");
                
                try {
                    $result = $this->regenerateOrder($order, $dryRun, $force);
                    
                    if ($result === 'regenerated') {
                        $regenerated++;
                        $this->line("  ✅ Regenerada exitosamente");
                    } elseif ($result === 'skipped') {
                        $skipped++;
                        $this->line("  ⏭️  Saltada (no necesita regeneración)");
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("  ❌ Error: " . $e->getMessage());
                    Log::error('Error regenerando orden', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->newLine();
            $this->info('📊 Resumen de regeneración:');
            $this->line("  ✅ Regeneradas: {$regenerated}");
            $this->line("  ⏭️  Saltadas: {$skipped}");
            $this->line("  ❌ Errores: {$errors}");

            if ($errors > 0) {
                $this->warn('⚠️  Revisa los logs para más detalles sobre los errores');
            }

            return $errors > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error('❌ Error general en regeneración: ' . $e->getMessage());
            Log::error('Error general en regeneración de órdenes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Regenera una orden individual
     */
    private function regenerateOrder(PurchaseOrder $order, bool $dryRun = false, bool $force = false)
    {
        // Verificar si la orden necesita regeneración
        if (!$force && $this->orderIsCorrect($order)) {
            return 'skipped';
        }

        if ($dryRun) {
            $this->line("  📋 [DRY-RUN] Se regeneraría la orden #{$order->order_number}");
            return 'regenerated';
        }

        DB::beginTransaction();

        try {
            // Obtener selecciones específicas para este proveedor
            $allSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
            
            // Filtrar solo las selecciones que corresponden a este proveedor
            $providerSelections = $allSelections->filter(function($selection) use ($order) {
                return $selection->quotation && 
                       $selection->quotation->provider_name === $order->provider->nombre;
            });

            Log::info('Regenerando orden con selecciones filtradas', [
                'order_id' => $order->id,
                'provider_name' => $order->provider->nombre,
                'filtered_selections' => $providerSelections->count(),
                'items_count' => $providerSelections->count()
            ]);

            // Recalcular totales basado en las selecciones correctas
            $newTotalAmount = $providerSelections->sum('total_price');
            $newSubtotal = round($newTotalAmount / 1.19, 2);
            $newIvaAmount = round($newTotalAmount - $newSubtotal, 2);

            // Actualizar los montos directamente en base de datos
            $updateResult = DB::table('purchase_orders')
                ->where('id', $order->id)
                ->update([
                    'total_amount' => $newTotalAmount,
                    'subtotal' => $newSubtotal,
                    'iva_amount' => $newIvaAmount,
                    'updated_at' => now()
                ]);

            if (!$updateResult) {
                throw new \Exception("No se pudo actualizar la orden {$order->id}");
            }

            // Eliminar PDF anterior si existe
            if ($order->file_path && $order->file_path !== 'pending_generation' && Storage::exists($order->file_path)) {
                Storage::delete($order->file_path);
                Log::info('PDF anterior eliminado para orden de compra #' . $order->id);
            }

            // Generar nuevo PDF con las selecciones correctas
            $newPdfPath = null;
            try {
                $newPdfPath = $this->pdfService->generatePdf($order, $providerSelections);
                
                // Actualizar la ruta del nuevo PDF directamente en DB
                DB::table('purchase_orders')
                    ->where('id', $order->id)
                    ->update([
                        'file_path' => $newPdfPath,
                        'updated_at' => now()
                    ]);

                Log::info('PDF generado y guardado exitosamente', [
                    'order_id' => $order->id,
                    'pdf_path' => $newPdfPath
                ]);
            } catch (\Exception $pdfError) {
                Log::error('Error al generar PDF para orden', [
                    'order_id' => $order->id,
                    'provider_name' => $order->provider->nombre,
                    'error' => $pdfError->getMessage()
                ]);
                // Continuar sin PDF
                $newPdfPath = 'error_generation';
                DB::table('purchase_orders')
                    ->where('id', $order->id)
                    ->update([
                        'file_path' => $newPdfPath,
                        'updated_at' => now()
                    ]);
            }

            // Commit la transacción
            DB::commit();

            Log::info('Orden regenerada exitosamente', [
                'order_id' => $order->id,
                'old_total' => $order->getOriginal('total_amount'),
                'new_total' => $newTotalAmount,
                'items_count' => $providerSelections->count(),
                'pdf_path' => $newPdfPath ?? 'error_generation'
            ]);

            return 'regenerated';

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en regeneración de orden', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verifica si la orden ya está correcta
     */
    private function orderIsCorrect(PurchaseOrder $order)
    {
        // Obtener selecciones específicas para este proveedor
        $allSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
        
        $providerSelections = $allSelections->filter(function($selection) use ($order) {
            return $selection->quotation && 
                   $selection->quotation->provider_name === $order->provider->nombre;
        });

        $expectedTotal = $providerSelections->sum('total_price');
        
        // Verificar si el total actual coincide con el esperado (con tolerancia de $1)
        $difference = abs($order->total_amount - $expectedTotal);
        
        return $difference < 1.0; // Tolerancia de $1 por redondeos
    }
}
