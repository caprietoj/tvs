<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class CheckOrderType extends Command
{
    protected $signature = 'check:order-type {order_id}';
    protected $description = 'Check the type of a purchase order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = PurchaseOrder::with('purchaseRequest')->find($orderId);

        if ($order) {
            $this->info("Order Number: " . $order->order_number);
            $this->info("Order Type: " . ($order->purchaseRequest->isNoQuotationService() ? 'Service' : 'Normal'));
            $this->info("Has Custom Data: " . (!empty($order->pdf_custom_data) ? 'Yes' : 'No'));
            
            if (!empty($order->pdf_custom_data)) {
                $customData = json_decode($order->pdf_custom_data, true);
                $this->info("Custom provider name: " . ($customData['provider_name'] ?? 'Not set'));
            }
        } else {
            $this->error("Order {$orderId} not found");
        }
    }
}
