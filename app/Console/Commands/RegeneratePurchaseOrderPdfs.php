<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RegeneratePurchaseOrderPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:regenerate-pdfs 
                            {--all : Regenerar todos los PDFs}
                            {--order-id= : ID específico de la orden a regenerar}
                            {--order-number= : Número específico de la orden a regenerar (ej: ORD-1234)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenera los PDFs de órdenes de compra asegurando cálculos de impuestos consistentes';

    /**
     * @var PurchaseOrderPdfService
     */
    protected $pdfService;

    /**
     * Create a new command instance.
     *
     * @param PurchaseOrderPdfService $pdfService
     */
    public function __construct(PurchaseOrderPdfService $pdfService)
    {
        parent::__construct();
        $this->pdfService = $pdfService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando regeneración de PDFs de órdenes de compra...');

        // Determinar qué órdenes procesar
        if ($orderId = $this->option('order-id')) {
            $orders = PurchaseOrder::where('id', $orderId)->get();
            $this->info("Procesando orden con ID: $orderId");
        } elseif ($orderNumber = $this->option('order-number')) {
            $orders = PurchaseOrder::where('order_number', $orderNumber)->get();
            $this->info("Procesando orden con número: $orderNumber");
        } elseif ($this->option('all')) {
            // Confirmar que realmente quiere regenerar TODOS los PDFs
            if (!$this->confirm('¿Estás seguro que deseas regenerar TODOS los PDFs de órdenes? Esto puede tomar tiempo.')) {
                $this->warn('Operación cancelada por el usuario.');
                return 0;
            }
            $orders = PurchaseOrder::whereNotNull('file_path')->get();
            $this->info("Procesando todas las órdenes con PDFs: " . $orders->count() . " encontradas");
        } else {
            $this->error('Debe especificar --all, --order-id o --order-number');
            return 1;
        }

        // Verificar que hay órdenes para procesar
        if ($orders->isEmpty()) {
            $this->error('No se encontraron órdenes que coincidan con los criterios especificados.');
            return 1;
        }

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        $success = 0;
        $failures = 0;

        foreach ($orders as $order) {
            try {
                $this->regeneratePdf($order);
                $success++;
            } catch (\Exception $e) {
                $failures++;
                Log::error("Error al regenerar PDF para orden #{$order->id}: " . $e->getMessage());
                $this->error("\nError al regenerar PDF para orden #{$order->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Proceso completado: $success PDFs regenerados exitosamente, $failures fallidos.");

        return 0;
    }

    /**
     * Regenera el PDF para una orden específica
     */
    private function regeneratePdf(PurchaseOrder $order)
    {
        $pdfPath = $this->pdfService->generatePdf($order);
        
        // Actualizar la ruta del PDF en la orden
        $order->file_path = $pdfPath;
        $order->save();
        
        Log::info("PDF regenerado exitosamente para la orden #{$order->id} ({$order->order_number})", [
            'pdf_path' => $pdfPath
        ]);
    }
}
