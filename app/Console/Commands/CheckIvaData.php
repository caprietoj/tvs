<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class CheckIvaData extends Command
{
    protected $signature = 'check:iva-data {order_id}';
    protected $description = 'Check IVA data for a purchase order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = PurchaseOrder::find($orderId);

        if ($order) {
            $this->info("Order Number: " . $order->order_number);
            $this->info("Subtotal: " . $order->subtotal);
            $this->info("IVA Amount: " . ($order->iva_amount ?? 0));
            $this->info("Tax Amount: " . ($order->tax_amount ?? 0));
            $this->info("Total Amount: " . $order->total_amount);
            $this->info("Includes IVA: " . ($order->includes_iva ? 'Yes' : 'No'));
            
            if (!empty($order->pdf_custom_data)) {
                $customData = json_decode($order->pdf_custom_data, true);
                $this->info("\n--- Custom Data ---");
                $this->info("Custom Subtotal: " . ($customData['subtotal'] ?? 'Not set'));
                $this->info("Custom IVA Amount: " . ($customData['iva_amount'] ?? 'Not set'));
                $this->info("Custom IVA Rate: " . ($customData['iva_rate'] ?? 'Not set') . '%');
                $this->info("Custom Total: " . ($customData['total'] ?? 'Not set'));
            } else {
                $this->info("No custom data found");
            }
        } else {
            $this->error("Order {$orderId} not found");
        }
    }
}
