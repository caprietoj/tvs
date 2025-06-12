<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Support\Facades\Storage;

class CreateTestQuotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-quotation {--request-id=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear una cotización de prueba para una solicitud';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requestId = $this->option('request-id');
        
        $purchaseRequest = PurchaseRequest::find($requestId);
        if (!$purchaseRequest) {
            $this->error("No se encontró la solicitud con ID: $requestId");
            return 1;
        }

        $this->info("Creando cotización de prueba para solicitud #{$purchaseRequest->id}");
        $this->line("Solicitud: {$purchaseRequest->request_number}");
        $this->line("Sección: {$purchaseRequest->section_area}");
        $this->newLine();

        // Crear archivo de prueba
        $testContent = "COTIZACIÓN DE PRUEBA\n\nProveedor: Test Provider S.A.S.\nMonto: $500,000\nTiempo de entrega: 15 días\nForma de pago: Contado";
        $filename = 'test_quotation_' . time() . '.txt';
        $filePath = 'quotations/' . $filename;
        
        Storage::put($filePath, $testContent);

        // Crear la cotización
        $quotation = Quotation::create([
            'purchase_request_id' => $purchaseRequest->id,
            'provider_name' => 'Test Provider S.A.S.',
            'total_amount' => 500000,
            'file_path' => $filePath,
            'delivery_time' => '15 días',
            'payment_method' => 'Contado',
            'uploaded_by' => 1, // Usuario por defecto
            'status' => 'uploaded',
        ]);

        $this->info("✓ Cotización creada exitosamente:");
        $this->line("  - ID: {$quotation->id}");
        $this->line("  - Proveedor: {$quotation->provider_name}");
        $this->line("  - Monto: $" . number_format($quotation->total_amount, 0, ',', '.'));
        $this->line("  - Archivo: {$quotation->file_path}");
        $this->newLine();

        $this->info("Ahora puedes:");
        $this->line("1. Acceder a la interfaz web de pre-aprobaciones");
        $this->line("2. Buscar la solicitud #{$purchaseRequest->id}");
        $this->line("3. Pre-aprobar la cotización de 'Test Provider S.A.S.'");
        $this->line("4. Verificar que se envíen las notificaciones");

        return 0;
    }
}
