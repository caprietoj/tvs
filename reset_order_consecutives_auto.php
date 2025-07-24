<?php

require_once 'vendor/autoload.php';

// Inicializar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

echo "=== RESETEAR CONSECUTIVOS DE ÓRDENES DE COMPRA ===\n\n";

try {
    // Verificar órdenes existentes
    echo "1. Verificando órdenes existentes...\n";
    $orders = PurchaseOrder::select('id', 'order_number', 'created_at')
        ->orderBy('id')
        ->get();

    if ($orders->isEmpty()) {
        echo "   No hay órdenes de compra en la base de datos.\n";
        exit(0);
    }

    echo "   Órdenes encontradas:\n";
    foreach ($orders as $order) {
        echo "   - ID: {$order->id} | Número: {$order->order_number} | Creado: {$order->created_at}\n";
    }

    echo "\n2. Reseteando consecutivos a formato ORD-XXXX...\n";

    DB::beginTransaction();

    $counter = 1;
    foreach ($orders as $order) {
        $newOrderNumber = 'ORD-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
        
        echo "   Actualizando orden ID {$order->id}: {$order->order_number} -> {$newOrderNumber}\n";
        
        PurchaseOrder::where('id', $order->id)->update([
            'order_number' => $newOrderNumber
        ]);
        
        $counter++;
    }

    DB::commit();
    
    echo "\n✅ Consecutivos reseteados exitosamente!\n";
    echo "   Total de órdenes actualizadas: " . $orders->count() . "\n";
    echo "   Nuevo rango: ORD-0001 a ORD-" . str_pad($orders->count(), 4, '0', STR_PAD_LEFT) . "\n";

    // Verificar el resultado
    echo "\n3. Verificando resultado...\n";
    $updatedOrders = PurchaseOrder::select('id', 'order_number')
        ->orderBy('id')
        ->get();
    
    foreach ($updatedOrders as $order) {
        echo "   - ID: {$order->id} | Nuevo número: {$order->order_number}\n";
    }

    echo "\n4. Información importante:\n";
    echo "   - Los consecutivos han sido reseteados correctamente\n";
    echo "   - Las próximas órdenes continuarán desde el siguiente número disponible\n";
    echo "   - El sistema automáticamente generará ORD-0002, ORD-0003, etc.\n";

} catch (Exception $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    echo "\n❌ Error al resetear consecutivos: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== SCRIPT COMPLETADO EXITOSAMENTE ===\n";