<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class CheckOrderItems extends Command
{
    protected $signature = 'check:order-items {order_id}';
    protected $description = 'Check items data for a purchase order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = PurchaseOrder::with([
            'purchaseRequest.quotations',
            'purchaseRequest.selectedQuotation.quotationItemSelections',
            'purchaseRequest.quotationItemSelections'
        ])->find($orderId);

        if ($order) {
            $this->info("Order Number: " . $order->order_number);
            $this->info("Purchase Request ID: " . ($order->purchaseRequest->id ?? 'N/A'));
            
            if ($order->purchaseRequest) {
                $this->info("\n--- Purchase Request Quotations ---");
                $quotations = $order->purchaseRequest->quotations;
                $this->info("Total Quotations: " . $quotations->count());
                
                foreach ($quotations as $quotation) {
                    $this->info("Quotation ID: {$quotation->id} - Provider: {$quotation->provider_name} - Selected: " . ($quotation->selected ? 'Yes' : 'No'));
                    $this->info("QuotationItemSelections count: " . $quotation->quotationItemSelections->count());
                    
                    foreach ($quotation->quotationItemSelections as $item) {
                        $this->info("  - Item: {$item->item_description} | Qty: {$item->quantity} | Price: {$item->unit_price} | Total: {$item->total_price}");
                    }
                }
                
                $this->info("\n--- Direct QuotationItemSelections ---");
                $directItems = $order->purchaseRequest->quotationItemSelections;
                $this->info("Direct items count: " . $directItems->count());
                
                foreach ($directItems as $item) {
                    $this->info("  - Item: {$item->item_description} | Qty: {$item->quantity} | Price: {$item->unit_price} | Total: {$item->total_price}");
                }
                
                $selectedQuotation = $order->purchaseRequest->quotations->where('selected', 1)->first();
                if ($selectedQuotation) {
                    $this->info("\n--- Selected Quotation ---");
                    $this->info("Selected Quotation ID: {$selectedQuotation->id}");
                    $this->info("Items in selected quotation: " . $selectedQuotation->quotationItemSelections->count());
                } else {
                    $this->info("\n--- No Selected Quotation Found ---");
                    $firstQuotation = $order->purchaseRequest->quotations->first();
                    if ($firstQuotation) {
                        $this->info("First Quotation ID: {$firstQuotation->id}");
                        $this->info("Items in first quotation: " . $firstQuotation->quotationItemSelections->count());
                    }
                }
            } else {
                $this->info("No purchase request found");
            }
            
            // Check additional_items
            if (!empty($order->additional_items)) {
                $this->info("\n--- Additional Items ---");
                $additionalItems = is_string($order->additional_items) ? json_decode($order->additional_items, true) : $order->additional_items;
                foreach ($additionalItems as $index => $item) {
                    $this->info("Additional Item {$index}: " . ($item['description'] ?? 'N/A') . " | Qty: " . ($item['quantity'] ?? 0) . " | Price: " . ($item['price'] ?? 0));
                }
            } else {
                $this->info("\n--- No Additional Items ---");
            }
            
        } else {
            $this->error("Order {$orderId} not found");
        }
    }
}
