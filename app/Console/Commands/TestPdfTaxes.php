<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;

class TestPdfTaxes extends Command
{
    protected $signature = 'test:pdf-taxes';
    protected $description = 'Test PDF generation with tax information';

    public function handle()
    {
        $this->info('Probando generación de PDF con información de impuestos...');
        
        // Buscar una orden de compra con cotización
        $order = PurchaseOrder::with(['purchaseRequest.selectedQuotation', 'provider'])->first();
        
        if (!$order) {
            $this->error('No se encontró ninguna orden de compra.');
            return;
        }
        
        $this->info("Orden encontrada - ID: {$order->id}");
        
        if (!$order->purchaseRequest) {
            $this->error('La orden no tiene solicitud de compra asociada.');
            return;
        }
        
        $this->info("Solicitud encontrada - ID: {$order->purchaseRequest->id}");
        
        if (!$order->purchaseRequest->selectedQuotation) {
            $this->warn('La solicitud no tiene cotización seleccionada.');
            $this->info('Creando datos de prueba para los impuestos...');
            
            // Crear una cotización de prueba si no existe
            $quotation = new \App\Models\Quotation([
                'purchase_request_id' => $order->purchaseRequest->id,
                'provider_name' => 'Proveedor de Prueba',
                'subtotal' => 100000,
                'includes_iva_19' => true,
                'iva_19_amount' => 19000,
                'includes_ipoconsumo_8' => true,
                'ipoconsumo_8_amount' => 8000,
                'total_amount' => 127000,
                'delivery_time' => '5 días',
                'payment_method' => 'Contado',
                'validity' => '30 días',
                'warranty' => '6 meses',
                'status' => 'selected'
            ]);
            $quotation->save();
            
            // Asociar la cotización a la solicitud
            $order->purchaseRequest->selected_quotation_id = $quotation->id;
            $order->purchaseRequest->save();
            
            $this->info("Cotización de prueba creada - ID: {$quotation->id}");
        } else {
            $quotation = $order->purchaseRequest->selectedQuotation;
            $this->info("Cotización encontrada - ID: {$quotation->id}");
        }
        
        // Mostrar información de impuestos
        $this->table(['Impuesto', 'Aplica', 'Monto'], [
            ['IVA 19%', $quotation->includes_iva_19 ? 'Sí' : 'No', '$' . number_format($quotation->iva_19_amount ?? 0, 0, ',', '.')],
            ['IVA 5%', $quotation->includes_iva_5 ? 'Sí' : 'No', '$' . number_format($quotation->iva_5_amount ?? 0, 0, ',', '.')],
            ['Ipoconsumo 8%', $quotation->includes_ipoconsumo_8 ? 'Sí' : 'No', '$' . number_format($quotation->ipoconsumo_8_amount ?? 0, 0, ',', '.')],
            ['Ipoconsumo 4%', $quotation->includes_ipoconsumo_4 ? 'Sí' : 'No', '$' . number_format($quotation->ipoconsumo_4_amount ?? 0, 0, ',', '.')],
        ]);
        
        // Generar PDF de prueba
        try {
            $pdfService = new PurchaseOrderPdfService();
            $path = $pdfService->generatePdf($order);
            $this->info("PDF generado exitosamente en: {$path}");
        } catch (\Exception $e) {
            $this->error("Error al generar PDF: {$e->getMessage()}");
        }
        
        return 0;
    }
}
