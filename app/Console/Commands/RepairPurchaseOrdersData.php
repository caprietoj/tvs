<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderValidationService;
use Illuminate\Support\Facades\Log;

class RepairPurchaseOrdersData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:repair {--dry-run : Solo mostrar qué se haría sin hacer cambios} {--order= : Reparar solo una orden específica}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repara y valida datos de órdenes de compra (items, IVA, totales)';

    protected $validationService;

    public function __construct(PurchaseOrderValidationService $validationService)
    {
        parent::__construct();
        $this->validationService = $validationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando reparación de órdenes de compra...');
        
        $dryRun = $this->option('dry-run');
        $specificOrder = $this->option('order');
        
        if ($dryRun) {
            $this->warn('📋 MODO DRY-RUN: Solo se mostrarán los cambios sin aplicarlos');
        }
        
        // Obtener órdenes a procesar
        if ($specificOrder) {
            $orders = PurchaseOrder::where('order_number', $specificOrder)->get();
            if ($orders->isEmpty()) {
                $this->error("❌ Orden {$specificOrder} no encontrada");
                return 1;
            }
        } else {
            $orders = PurchaseOrder::whereNotNull('pdf_custom_data')
                ->where('pdf_custom_data', '!=', '')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        $this->info("🔍 Procesando {$orders->count()} órdenes...");
        
        $processed = 0;
        $repaired = 0;
        $errors = 0;
        
        $this->withProgressBar($orders, function ($order) use (&$processed, &$repaired, &$errors, $dryRun) {
            try {
                $result = $this->validationService->validateAndRepair($order, $dryRun);
                
                if ($result['repaired']) {
                    $repaired++;
                    
                    if (!$dryRun) {
                        $this->line("\n✅ {$order->order_number}: " . implode(', ', $result['fixes']));
                    } else {
                        $this->line("\n📋 {$order->order_number}: " . implode(', ', $result['fixes']));
                    }
                }
                
                $processed++;
                
            } catch (\Exception $e) {
                $errors++;
                $this->line("\n❌ Error en {$order->order_number}: " . $e->getMessage());
                Log::error('Error reparando orden', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        });
        
        $this->newLine(2);
        $this->info("📊 RESUMEN:");
        $this->info("• Órdenes procesadas: {$processed}");
        $this->info("• Órdenes reparadas: {$repaired}");
        $this->info("• Errores: {$errors}");
        
        if ($dryRun && $repaired > 0) {
            $this->warn("\n💡 Para aplicar los cambios, ejecuta el comando sin --dry-run");
        }
        
        return 0;
    }
}
