<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class DebugOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:order {order_number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug information for a specific purchase order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        
        $order = PurchaseOrder::where('order_number', $orderNumber)
            ->with([
                'purchaseRequest.quotationItemSelections.quotation',
                'purchaseRequest.selectedQuotation',
                'purchaseRequest.quotations',
                'provider'
            ])
            ->first();
            
        if (!$order) {
            $this->error("Order {$orderNumber} not found.");
            return 1;
        }
        
        $this->info("=== ORDER DEBUG INFO ===");
        $this->info("Order ID: {$order->id}");
        $this->info("Order Number: {$order->order_number}");
        $this->info("Provider: {$order->provider->nombre}");
        $this->info("Total Amount: $" . number_format($order->total_amount, 2));
        $this->info("Created: {$order->created_at}");
        
        $purchaseRequest = $order->purchaseRequest;
        $this->info("\n=== PURCHASE REQUEST INFO ===");
        $this->info("Request ID: {$purchaseRequest->id}");
        $this->info("Request Number: {$purchaseRequest->request_number}");
        $this->info("Type: {$purchaseRequest->type}");
        $this->info("Status: {$purchaseRequest->status}");
        
        // Verificar selecciones mixtas
        $mixedSelections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
        $this->info("\n=== MIXED SELECTIONS DEBUG ===");
        $this->info("QuotationItemSelections count: {$mixedSelections->count()}");
        
        if ($mixedSelections->count() > 0) {
            $this->info("Mixed selections found:");
            $providerGroups = $mixedSelections->groupBy('quotation.provider_name');
            $this->info("Provider groups: {$providerGroups->count()}");
            
            foreach ($providerGroups as $providerName => $selections) {
                $this->info("- Provider: {$providerName}");
                $this->info("  Quotation ID: {$selections->first()->quotation_id}");
                $this->info("  Items: {$selections->count()}");
                $this->info("  Total: $" . number_format($selections->sum('total_price'), 2));
                
                foreach ($selections as $selection) {
                    $this->info("    * {$selection->item_description} - Qty: {$selection->quantity} - Price: $" . number_format($selection->unit_price, 2));
                }
            }
        } else {
            $this->info("No mixed selections found.");
        }
        
        // Verificar cotización seleccionada
        $selectedQuotation = $purchaseRequest->selectedQuotation;
        $this->info("\n=== SELECTED QUOTATION ===");
        if ($selectedQuotation) {
            $this->info("Selected Quotation ID: {$selectedQuotation->id}");
            $this->info("Provider: {$selectedQuotation->provider_name}");
            $this->info("Total: $" . number_format($selectedQuotation->total_amount, 2));
        } else {
            $this->info("No selected quotation found.");
        }
        
        // Verificar todas las cotizaciones
        $quotations = $purchaseRequest->quotations;
        $this->info("\n=== ALL QUOTATIONS ===");
        $this->info("Total quotations: {$quotations->count()}");
        
        foreach ($quotations as $quotation) {
            $this->info("- Quotation ID: {$quotation->id}");
            $this->info("  Provider: {$quotation->provider_name}");
            $this->info("  Selected: " . ($quotation->selected ? 'Yes' : 'No'));
            $this->info("  Total: $" . number_format($quotation->total_amount, 2));
        }
        
        return 0;
    }
}
