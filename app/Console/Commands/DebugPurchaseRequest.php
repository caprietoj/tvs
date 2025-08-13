<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

class DebugPurchaseRequest extends Command
{
    protected $signature = 'debug:request {requestNumber}';
    protected $description = 'Debug purchase request by number';

    public function handle()
    {
        $requestNumber = $this->argument('requestNumber');
        
        $request = PurchaseRequest::where('request_number', $requestNumber)->first();
        
        if (!$request) {
            $this->error("Purchase Request {$requestNumber} not found.");
            return 1;
        }

        $this->info("=== PURCHASE REQUEST DEBUG INFO ===");
        $this->info("Request ID: {$request->id}");
        $this->info("Request Number: {$request->request_number}");
        $this->info("Status: {$request->status}");
        $this->info("Selected Quotation ID: " . ($request->selected_quotation_id ?? 'NULL'));
        
        $this->info("\n=== QUOTATIONS ===");
        $quotations = $request->quotations;
        $this->info("Total quotations: {$quotations->count()}");
        
        foreach ($quotations as $quotation) {
            $this->info("- Quotation ID: {$quotation->id}");
            $this->info("  Provider: {$quotation->provider_name}");
            $this->info("  Total: $" . number_format($quotation->total_amount, 2));
            $this->info("  Status: {$quotation->status}");
        }
        
        $this->info("\n=== PURCHASE ORDERS ===");
        $orders = PurchaseOrder::withTrashed()->where('purchase_request_id', $request->id)->get();
        $this->info("Total orders (including deleted): {$orders->count()}");
        
        foreach ($orders as $order) {
            $status = $order->deleted_at ? 'DELETED' : 'ACTIVE';
            $this->info("- Order: {$order->order_number} [{$status}]");
            $this->info("  Provider: " . ($order->provider->nombre ?? 'N/A'));
            $this->info("  Total: $" . number_format($order->total_amount, 2));
            if ($order->deleted_at) {
                $this->info("  Deleted at: {$order->deleted_at}");
            }
        }
        
        $this->info("\n=== MIXED SELECTIONS ===");
        $mixedSelections = $request->quotationItemSelections;
        $this->info("Mixed selections count: {$mixedSelections->count()}");
        
        if ($mixedSelections->count() > 0) {
            foreach ($mixedSelections as $selection) {
                $this->info("- Item: {$selection->item_description}");
                $this->info("  Quotation ID: {$selection->quotation_id}");
                $this->info("  Total: $" . number_format($selection->total_price, 2));
            }
        }
        
        return 0;
    }
}
