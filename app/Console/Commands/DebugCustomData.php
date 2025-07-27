<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class DebugCustomData extends Command
{
    protected $signature = 'debug:custom-data {order_id}';
    protected $description = 'Debug custom data structure for a purchase order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = PurchaseOrder::find($orderId);

        if ($order && $order->pdf_custom_data) {
            $data = json_decode($order->pdf_custom_data, true);
            
            $this->info("=== FULL CUSTOM DATA STRUCTURE ===");
            $this->info(json_encode($data, JSON_PRETTY_PRINT));
            
        } else {
            $this->info("No custom data found for order {$orderId}");
        }
    }
}
