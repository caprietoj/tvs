<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use App\Models\PurchaseOrder;

class DebugQuotationPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:quotation-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug precios de cotizaciones y órdenes de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ANÁLISIS DE PRECIOS COTIZACIONES/ÓRDENES ===');
        
        // Obtener todas las cotizaciones recientes
        $quotations = Quotation::orderBy('created_at', 'desc')->take(5)->get();
        
        foreach ($quotations as $quotation) {
            $this->info("--- Cotización ID: {$quotation->id} ---");
            $this->info("Proveedor: {$quotation->provider_name}");
            $this->info("Total Amount: {$quotation->total_amount}");
            $this->info("Subtotal: {$quotation->subtotal}");
            
            // Mostrar precios originales si existen
            if ($quotation->original_item_prices) {
                $this->info("Precios Originales: " . json_encode($quotation->original_item_prices, JSON_PRETTY_PRINT));
            } else {
                $this->warn("Sin precios originales guardados");
            }
            
            if ($quotation->original_item_totals) {
                $this->info("Totales Originales: " . json_encode($quotation->original_item_totals, JSON_PRETTY_PRINT));
            }
            
            // Verificar purchase request asociado
            $purchaseRequest = $quotation->purchaseRequest;
            if ($purchaseRequest) {
                $this->info("Purchase Request ID: {$purchaseRequest->id}");
                $this->info("Request Number: {$purchaseRequest->request_number}");
                
                // Buscar órdenes de compra por purchase_request_id
                $purchaseOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
                if ($purchaseOrder) {
                    $this->info("Orden de Compra ID: {$purchaseOrder->id}");
                    $this->info("Total Orden: {$purchaseOrder->total_amount}");
                    
                    // Buscar discrepancias
                    if (abs($quotation->total_amount - $purchaseOrder->total_amount) > 0.01) {
                        $this->error("DISCREPANCIA: Total cotización ({$quotation->total_amount}) vs orden ({$purchaseOrder->total_amount})");
                    } else {
                        $this->info("✓ Totales coinciden");
                    }
                } else {
                    $this->comment("Sin orden de compra asociada");
                }
            }
            
            $this->info('---');
        }
        
        // Mostrar estadísticas
        $totalQuotations = Quotation::count();
        $quotationsWithPrices = Quotation::whereNotNull('original_item_prices')->count();
        $quotationsWithOrders = \DB::table('quotations')
            ->join('purchase_orders', 'quotations.purchase_request_id', '=', 'purchase_orders.purchase_request_id')
            ->count();
        
        $this->info("=== ESTADÍSTICAS ===");
        $this->info("Total Cotizaciones: {$totalQuotations}");
        $this->info("Con Precios Originales: {$quotationsWithPrices}");
        $this->info("Con Órdenes de Compra: {$quotationsWithOrders}");
        
        return 0;
    }
}
