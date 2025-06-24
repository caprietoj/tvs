<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class ListOrders extends Command
{
    protected $signature = 'list:orders';
    protected $description = 'List all purchase orders';

    public function handle()
    {
        $orders = PurchaseOrder::all();
        
        if ($orders->isEmpty()) {
            $this->info('No hay órdenes de compra registradas.');
            return;
        }
        
        $this->info('Órdenes de compra existentes:');
        $this->newLine();
        
        foreach ($orders as $order) {
            $this->line("ID: {$order->id} - Número: {$order->order_number} - Estado: {$order->status}");
        }
        
        return 0;
    }
}
