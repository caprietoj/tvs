<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class CheckCustomData extends Command
{
    protected $signature = 'check:custom-data {order_id}';
    protected $description = 'Check custom data for a purchase order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = PurchaseOrder::find($orderId);

        if ($order && $order->pdf_custom_data) {
            $data = json_decode($order->pdf_custom_data, true);
            $this->info("Custom Data Keys: " . implode(', ', array_keys($data)));
            
            if (isset($data['items'])) {
                $this->info("Items in custom data: " . count($data['items']));
                foreach ($data['items'] as $i => $item) {
                    $this->info("Item {$i}: " . ($item['description'] ?? 'N/A') . " | Qty: " . ($item['quantity'] ?? 0) . " | Price: " . ($item['unit_price'] ?? 0));
                }
            } else {
                $this->info("No items in custom data");
            }
            
            if (isset($data['additional_items'])) {
                $this->info("Additional items in custom data: " . count($data['additional_items']));
                foreach ($data['additional_items'] as $i => $item) {
                    if (!empty($item['description'])) {
                        $this->info("Additional Item {$i}: " . ($item['description'] ?? 'N/A') . " | Qty: " . ($item['quantity'] ?? 0) . " | Price: " . ($item['unit_price'] ?? 0));
                    }
                }
            } else {
                $this->info("No additional items in custom data");
            }
        } else {
            $this->info("No custom data found for order {$orderId}");
        }
    }
}
