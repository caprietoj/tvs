<?php
/**
 * Script para analizar órdenes de compra con proveedores incorrectos
 * 
 * Solo identifica y reporta problemas sin hacer cambios
 */

require __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Proveedor;

echo "=== ANÁLISIS DE ÓRDENES DE COMPRA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Obtener todas las órdenes con sus relaciones
    $orders = PurchaseOrder::with([
        'purchaseRequest.selectedQuotation', 
        'purchaseRequest.preApprovedQuotation', 
        'provider'
    ])->get();
    
    echo "Total de órdenes de compra: " . $orders->count() . "\n\n";
    
    // Analizar cada orden
    $problemsFound = [];
    $correctOrders = 0;
    $ordersWithoutQuotation = 0;
    
    foreach ($orders as $order) {
        $request = $order->purchaseRequest;
        
        if (!$request) {
            $problemsFound[] = [
                'order' => $order->order_number,
                'problem' => 'Orden sin solicitud asociada',
                'details' => 'La orden no tiene purchase_request_id válido'
            ];
            continue;
        }
        
        $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
        
        if (!$quotation) {
            $ordersWithoutQuotation++;
            echo "Orden {$order->order_number}: Sin cotización (posible servicio directo)\n";
            continue;
        }
        
        // Comparar proveedor de la orden vs proveedor de la cotización
        $quotationProviderName = trim($quotation->provider_name);
        $orderProviderName = trim($order->provider->nombre ?? 'PROVEEDOR NO ENCONTRADO');
        
        if (strtolower($quotationProviderName) !== strtolower($orderProviderName)) {
            $problemsFound[] = [
                'order' => $order->order_number,
                'request' => $request->request_number,
                'problem' => 'Proveedor incorrecto',
                'order_provider' => $orderProviderName,
                'quotation_provider' => $quotationProviderName,
                'order_status' => $order->status,
                'amount' => $order->total_amount,
                'created_at' => $order->created_at->format('Y-m-d H:i:s')
            ];
        } else {
            $correctOrders++;
        }
    }
    
    echo "\n=== RESULTADOS DEL ANÁLISIS ===\n";
    echo "Órdenes correctas: {$correctOrders}\n";
    echo "Órdenes sin cotización: {$ordersWithoutQuotation}\n";
    echo "Órdenes problemáticas: " . count($problemsFound) . "\n\n";
    
    if (!empty($problemsFound)) {
        echo "=== DETALLES DE ÓRDENES PROBLEMÁTICAS ===\n";
        foreach ($problemsFound as $problem) {
            echo "ORDEN: {$problem['order']}\n";
            
            if (isset($problem['request'])) {
                echo "  Solicitud: {$problem['request']}\n";
                echo "  Proveedor en orden: {$problem['order_provider']}\n";
                echo "  Proveedor en cotización: {$problem['quotation_provider']}\n";
                echo "  Estado: {$problem['order_status']}\n";
                echo "  Monto: $" . number_format($problem['amount'], 2) . "\n";
                echo "  Creada: {$problem['created_at']}\n";
            } else {
                echo "  Problema: {$problem['problem']}\n";
                echo "  Detalles: {$problem['details']}\n";
            }
            echo "\n";
        }
        
        echo "=== RECOMENDACIONES ===\n";
        echo "1. Ejecutar el script 'regenerate_purchase_orders.php' para corregir automáticamente\n";
        echo "2. O corregir manualmente cada orden editándola en el sistema\n";
        echo "3. Verificar que los proveedores de las cotizaciones existan en la base de datos\n\n";
        
        // Verificar proveedores que necesitan ser creados
        $neededProviders = [];
        foreach ($problemsFound as $problem) {
            if (isset($problem['quotation_provider'])) {
                $providerName = $problem['quotation_provider'];
                $exists = Proveedor::where('nombre', $providerName)->exists();
                if (!$exists && !in_array($providerName, $neededProviders)) {
                    $neededProviders[] = $providerName;
                }
            }
        }
        
        if (!empty($neededProviders)) {
            echo "=== PROVEEDORES QUE NECESITAN SER CREADOS ===\n";
            foreach ($neededProviders as $providerName) {
                echo "- {$providerName}\n";
            }
            echo "\n";
        }
    } else {
        echo "¡Todas las órdenes tienen el proveedor correcto!\n\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "=== ANÁLISIS FINALIZADO ===\n";
