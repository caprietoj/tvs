<?php

require_once 'vendor/autoload.php';

// Inicializar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

echo "=== SCRIPT PARA RESETEAR CONSECUTIVOS DE ÓRDENES DE COMPRA ===\n\n";

// Verificar órdenes existentes
echo "1. Verificando órdenes existentes...\n";
$orders = PurchaseOrder::select('id', 'order_number', 'created_at')
    ->orderBy('id')
    ->get();

if ($orders->isEmpty()) {
    echo "   No hay órdenes de compra en la base de datos.\n";
} else {
    echo "   Órdenes encontradas:\n";
    foreach ($orders as $order) {
        echo "   - ID: {$order->id} | Número: {$order->order_number} | Creado: {$order->created_at}\n";
    }
}

echo "\n2. Opciones disponibles:\n";
echo "   [1] Solo mostrar información (no hacer cambios)\n";
echo "   [2] Resetear consecutivos empezando desde ORD-0001\n";
echo "   [3] Resetear consecutivos empezando desde OC-0001 (formato del modelo)\n";
echo "   [4] Salir\n\n";

echo "Seleccione una opción (1-4): ";
$handle = fopen("php://stdin", "r");
$option = trim(fgets($handle));
fclose($handle);

switch ($option) {
    case '1':
        echo "\n=== INFORMACIÓN ACTUAL ===\n";
        if ($orders->isEmpty()) {
            echo "No hay órdenes para mostrar.\n";
        } else {
            echo "Total de órdenes: " . $orders->count() . "\n";
            echo "Última orden ID: " . $orders->last()->id . "\n";
            echo "Último número: " . $orders->last()->order_number . "\n";
        }
        break;

    case '2':
        echo "\n=== RESETEANDO CONSECUTIVOS A FORMATO ORD-XXXX ===\n";
        resetConsecutives('ORD-', $orders);
        break;

    case '3':
        echo "\n=== RESETEANDO CONSECUTIVOS A FORMATO OC-XXXX ===\n";
        resetConsecutives('OC-', $orders);
        break;

    case '4':
        echo "Saliendo...\n";
        exit(0);

    default:
        echo "Opción no válida.\n";
        exit(1);
}

function resetConsecutives($prefix, $orders)
{
    if ($orders->isEmpty()) {
        echo "No hay órdenes para resetear.\n";
        return;
    }

    echo "¿Está seguro de que desea resetear los consecutivos? (s/N): ";
    $handle = fopen("php://stdin", "r");
    $confirm = trim(fgets($handle));
    fclose($handle);

    if (strtolower($confirm) !== 's') {
        echo "Operación cancelada.\n";
        return;
    }

    try {
        DB::beginTransaction();

        $counter = 1;
        foreach ($orders as $order) {
            $newOrderNumber = $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
            
            echo "   Actualizando orden ID {$order->id}: {$order->order_number} -> {$newOrderNumber}\n";
            
            PurchaseOrder::where('id', $order->id)->update([
                'order_number' => $newOrderNumber
            ]);
            
            $counter++;
        }

        DB::commit();
        
        echo "\n✅ Consecutivos reseteados exitosamente!\n";
        echo "   Total de órdenes actualizadas: " . $orders->count() . "\n";
        echo "   Nuevo rango: {$prefix}0001 a {$prefix}" . str_pad($orders->count(), 4, '0', STR_PAD_LEFT) . "\n";

        // Verificar el resultado
        echo "\n3. Verificando resultado...\n";
        $updatedOrders = PurchaseOrder::select('id', 'order_number')
            ->orderBy('id')
            ->get();
        
        foreach ($updatedOrders as $order) {
            echo "   - ID: {$order->id} | Nuevo número: {$order->order_number}\n";
        }

    } catch (Exception $e) {
        DB::rollBack();
        echo "\n❌ Error al resetear consecutivos: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SCRIPT COMPLETADO ===\n";