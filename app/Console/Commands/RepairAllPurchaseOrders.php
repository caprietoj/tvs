<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderFixService;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RepairAllPurchaseOrders extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'orders:repair {order_id? : ID específico de orden para corregir} {--all : Corregir todas las órdenes} {--force : Forzar regeneración incluso sin cambios}';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Corrige problemas de precio y regenera PDFs de las órdenes de compra';

    /**
     * Servicio para corregir órdenes
     *
     * @var PurchaseOrderFixService
     */
    protected $fixService;

    /**
     * Servicio para generar PDFs
     *
     * @var PurchaseOrderPdfService
     */
    protected $pdfService;

    /**
     * Crea una nueva instancia del comando.
     *
     * @return void
     */
    public function __construct(PurchaseOrderFixService $fixService, PurchaseOrderPdfService $pdfService)
    {
        parent::__construct();
        $this->fixService = $fixService;
        $this->pdfService = $pdfService;
    }

    /**
     * Ejecuta el comando de consola.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        $processAll = $this->option('all');
        $force = $this->option('force');

        if ($orderId) {
            // Procesar una orden específica
            $order = PurchaseOrder::find($orderId);
            if (!$order) {
                $this->error("Orden no encontrada con ID: $orderId");
                return 1;
            }

            $this->processOrder($order, $force);
        } elseif ($processAll) {
            // Procesar todas las órdenes
            $this->info("Iniciando reparación de todas las órdenes...");
            
            // Obtener todas las órdenes con datos personalizados
            $orders = PurchaseOrder::whereNotNull('pdf_custom_data')->get();
            
            $this->info("Se encontraron {$orders->count()} órdenes para procesar");
            $bar = $this->output->createProgressBar($orders->count());
            $bar->start();
            
            $fixedCount = 0;
            $errorCount = 0;
            
            foreach ($orders as $order) {
                try {
                    $result = $this->processOrder($order, $force, false);
                    if ($result) {
                        $fixedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Error procesando orden #{$order->order_number}: " . $e->getMessage(), [
                        'exception' => $e,
                        'order_id' => $order->id
                    ]);
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Proceso completado: $fixedCount órdenes corregidas, $errorCount errores");
        } else {
            $this->info("Por favor especifique un ID de orden o use la opción --all para procesar todas las órdenes");
        }

        return 0;
    }

    /**
     * Procesa una orden individual
     *
     * @param PurchaseOrder $order Orden a procesar
     * @param bool $force Forzar regeneración incluso sin cambios
     * @param bool $verbose Mostrar mensajes detallados
     * @return bool
     */
    protected function processOrder(PurchaseOrder $order, bool $force = false, bool $verbose = true)
    {
        if ($verbose) {
            $this->info("Procesando orden #{$order->order_number} (ID: {$order->id})");
        }

        // Corregir precios
        $fixed = $this->fixService->fixOrderPrices($order);

        // Si se fuerza la regeneración o se hicieron cambios
        if ($force || $fixed) {
            // Regenerar PDF
            try {
                $pdfPath = $this->pdfService->generatePdf($order);
                $order->file_path = $pdfPath;
                $order->save();
                
                if ($verbose) {
                    $this->info("PDF regenerado correctamente: {$pdfPath}");
                }
                
                return true;
            } catch (\Exception $e) {
                if ($verbose) {
                    $this->error("Error regenerando PDF: " . $e->getMessage());
                }
                
                Log::error("Error regenerando PDF para orden #{$order->order_number}: " . $e->getMessage(), [
                    'exception' => $e,
                    'order_id' => $order->id
                ]);
                
                return false;
            }
        } elseif ($verbose) {
            $this->line("No se requieren cambios en la orden #{$order->order_number}");
        }

        return $fixed;
    }
}
