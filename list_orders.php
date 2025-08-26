<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

echo "=== ÓRDENES DE COMPRA DISPONIBLES ===\n\n";

try {
    $orders = PurchaseOrder::select('id', 'order_number', 'status', 'created_at')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    if ($orders->count() === 0) {
        echo "No hay órdenes de compra en el sistema.\n";
    } else {
        echo "Últimas 10 órdenes:\n";
        foreach ($orders as $order) {
            echo "- {$order->order_number} (Estado: {$order->status}) - {$order->created_at}\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}