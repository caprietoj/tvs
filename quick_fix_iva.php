#!/usr/bin/env php
<?php
/**
 * Script Rápido para Corregir IVA
 * 
 * Uso simple: php quick_fix_iva.php
 * 
 * Este script automáticamente:
 * 1. Verifica todas las órdenes de compra
 * 2. Corrige las que tengan errores de IVA
 * 3. Muestra un resumen final
 */

echo "🚀 Iniciando corrección rápida de IVA...\n\n";

// Verificar directorio
if (!file_exists('artisan')) {
    die("❌ Error: Ejecuta este script desde el directorio raíz del proyecto Laravel.\n");
}

// Cargar Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "🔍 Verificando órdenes de compra...\n";
    
    $orders = App\Models\PurchaseOrder::with(['provider'])->get();
    $totalOrders = $orders->count();
    $ordersFixed = 0;
    $errors = [];
    
    echo "📊 Encontradas {$totalOrders} órdenes de compra\n\n";
    
    foreach ($orders as $order) {
        // Calcular valores correctos
        $expectedSubtotal = round($order->total_amount / 1.19, 2);
        $expectedIva = round($order->total_amount - $expectedSubtotal, 2);
        
        // Verificar si necesita corrección
        $subtotalDiff = abs(($order->subtotal ?? 0) - $expectedSubtotal);
        $ivaDiff = abs(($order->iva_amount ?? 0) - $expectedIva);
        
        if ($subtotalDiff > 0.01 || $ivaDiff > 0.01) {
            try {
                $order->update([
                    'subtotal' => $expectedSubtotal,
                    'iva_amount' => $expectedIva,
                    'includes_iva' => true
                ]);
                
                echo "✅ Corregida: {$order->order_number} - " . 
                     ($order->provider->nombre ?? 'Sin proveedor') . "\n";
                $ordersFixed++;
                
            } catch (Exception $e) {
                $errors[] = "Error en {$order->order_number}: " . $e->getMessage();
                echo "❌ Error: {$order->order_number}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📋 RESUMEN FINAL:\n";
    echo "📊 Total órdenes: {$totalOrders}\n";
    echo "🔧 Órdenes corregidas: {$ordersFixed}\n";
    echo "❌ Errores: " . count($errors) . "\n";
    
    if ($ordersFixed > 0) {
        echo "\n🎉 ¡Corrección completada exitosamente!\n";
    } else {
        echo "\n✅ ¡Todas las órdenes ya tenían cálculos correctos!\n";
    }
    
    if (!empty($errors)) {
        echo "\n⚠️  Errores encontrados:\n";
        foreach ($errors as $error) {
            echo "  • {$error}\n";
        }
    }
    
    echo "\n💡 Para verificar: php iva_manager.php check\n";
    
} catch (Exception $e) {
    echo "❌ Error crítico: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Script completado.\n";