<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\Quotation;

class DiagnoseQuotationCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:diagnose {request_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnosticar problemas de cálculo en cotizaciones';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requestNumber = $this->argument('request_number');
        
        if ($requestNumber) {
            $this->diagnoseSingleRequest($requestNumber);
        } else {
            $this->diagnoseRecentErrors();
        }
        
        return 0;
    }
    
    private function diagnoseSingleRequest($requestNumber)
    {
        $request = PurchaseRequest::where('request_number', $requestNumber)->first();
        
        if (!$request) {
            $this->error("Solicitud {$requestNumber} no encontrada.");
            return;
        }
        
        $this->info("=== Diagnóstico de Solicitud {$requestNumber} ===");
        $this->info("Estado: {$request->status}");
        $this->info("Solicitante: {$request->requester}");
        $this->info("Sección: {$request->section_area}");
        
        $this->info("\n--- Items de la solicitud ---");
        $items = $request->items();
        if ($items->isNotEmpty()) {
            foreach ($items as $index => $item) {
                if (is_array($item)) {
                    $description = $item['description'] ?? 'Sin descripción';
                    $quantity = $item['quantity'] ?? 'N/A';
                    $unit = $item['unit'] ?? '';
                } else {
                    $description = $item->description ?? 'Sin descripción';
                    $quantity = $item->quantity ?? 'N/A';
                    $unit = $item->unit ?? '';
                }
                $this->info("Item {$index}: {$description}");
                $this->info("  Cantidad: {$quantity} {$unit}");
            }
        } else {
            $this->info("No hay items definidos o no se pueden acceder.");
        }
        
        $this->info("\n--- Cotizaciones ---");
        foreach ($request->quotations as $quotation) {
            $this->info("Cotización ID {$quotation->id}:");
            $this->info("  Proveedor: {$quotation->provider_name}");
            $this->info("  Subtotal: " . number_format($quotation->subtotal, 2));
            $this->info("  Total: " . number_format($quotation->total_amount, 2));
            $this->info("  IVA 19%: " . number_format($quotation->iva_19_amount, 2));
            $this->info("  IVA 5%: " . number_format($quotation->iva_5_amount, 2));
            $this->info("  Ipoconsumo 8%: " . number_format($quotation->ipoconsumo_8_amount, 2));
            $this->info("  Ipoconsumo 4%: " . number_format($quotation->ipoconsumo_4_amount, 2));
            $this->info("  Modo impuestos: {$quotation->tax_application_mode}");
            
            // Verificar cálculos
            $calculatedTotal = $quotation->subtotal + $quotation->iva_19_amount + 
                             $quotation->iva_5_amount + $quotation->ipoconsumo_8_amount + 
                             $quotation->ipoconsumo_4_amount;
            $difference = abs($calculatedTotal - $quotation->total_amount);
            
            if ($difference > 0.01) {
                $this->warn("  ⚠️  Discrepancia en total: Calculado {$calculatedTotal}, Guardado {$quotation->total_amount}, Diferencia: {$difference}");
            } else {
                $this->info("  ✅ Cálculos correctos");
            }
            
            if ($quotation->additional_items) {
                $this->info("  Items adicionales: " . count($quotation->additional_items));
            }
            
            if ($quotation->original_item_totals) {
                $originalSum = array_sum($quotation->original_item_totals);
                $this->info("  Suma items originales: " . number_format($originalSum, 2));
                
                if (abs($originalSum - $quotation->subtotal) > 0.01) {
                    $this->warn("  ⚠️  Discrepancia en subtotal vs items originales: " . number_format(abs($originalSum - $quotation->subtotal), 2));
                }
            }
        }
    }
    
    private function diagnoseRecentErrors()
    {
        $this->info("=== Diagnóstico de Errores Recientes ===");
        
        // Buscar solicitudes con cotizaciones que tengan discrepancias
        $problematicQuotations = Quotation::whereRaw('ABS(
            (subtotal + iva_19_amount + iva_5_amount + ipoconsumo_8_amount + ipoconsumo_4_amount) - total_amount
        ) > 0.01')
        ->with('purchaseRequest')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
        
        if ($problematicQuotations->count() > 0) {
            $this->warn("Encontradas {$problematicQuotations->count()} cotizaciones con discrepancias:");
            
            foreach ($problematicQuotations as $quotation) {
                $calculatedTotal = $quotation->subtotal + $quotation->iva_19_amount + 
                                 $quotation->iva_5_amount + $quotation->ipoconsumo_8_amount + 
                                 $quotation->ipoconsumo_4_amount;
                $difference = abs($calculatedTotal - $quotation->total_amount);
                
                $this->info("- {$quotation->purchaseRequest->request_number}: Diferencia de " . number_format($difference, 2));
            }
        } else {
            $this->info("✅ No se encontraron cotizaciones con discrepancias en cálculos.");
        }
        
        // Mostrar estadísticas generales
        $totalQuotations = Quotation::count();
        $recentQuotations = Quotation::where('created_at', '>=', now()->subDays(7))->count();
        
        $this->info("\n--- Estadísticas ---");
        $this->info("Total cotizaciones: {$totalQuotations}");
        $this->info("Cotizaciones últimos 7 días: {$recentQuotations}");
    }
}
