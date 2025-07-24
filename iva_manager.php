<?php
/**
 * Script Maestro para Gestión de IVA en Órdenes de Compra
 * 
 * Este script proporciona múltiples funcionalidades para gestionar
 * y verificar los cálculos de IVA en el sistema de órdenes de compra.
 * 
 * Uso:
 * php iva_manager.php [comando] [opciones]
 * 
 * Comandos disponibles:
 * check     - Verifica cálculos de IVA en órdenes existentes
 * fix       - Corrige cálculos de IVA en órdenes existentes
 * test      - Crea una orden de prueba para verificar cálculos
 * cleanup   - Limpia órdenes de prueba
 * help      - Muestra esta ayuda
 */

// Configuración inicial
ini_set('memory_limit', '512M');
set_time_limit(300);

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    die("❌ Error: Este script debe ejecutarse desde el directorio raíz del proyecto Laravel.\n");
}

// Cargar Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Función para mostrar mensajes con colores
function showMessage($message, $type = 'info') {
    $colors = [
        'info' => "\033[36m",    // Cyan
        'success' => "\033[32m", // Green
        'warning' => "\033[33m", // Yellow
        'error' => "\033[31m",   // Red
        'reset' => "\033[0m"     // Reset
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    echo $color . $message . $colors['reset'] . "\n";
}

// Función para mostrar el encabezado
function showHeader() {
    showMessage("╔══════════════════════════════════════════════════════════════╗", 'info');
    showMessage("║              GESTOR DE IVA - ÓRDENES DE COMPRA              ║", 'info');
    showMessage("║                        Sistema TVS                           ║", 'info');
    showMessage("╚══════════════════════════════════════════════════════════════╝", 'info');
    echo "\n";
}

// Función para mostrar ayuda
function showHelp() {
    showHeader();
    echo "📖 USO:\n";
    echo "  php iva_manager.php [comando] [opciones]\n\n";
    
    echo "🔧 COMANDOS DISPONIBLES:\n";
    echo "  check     Verifica cálculos de IVA en órdenes existentes\n";
    echo "  fix       Corrige cálculos de IVA en órdenes existentes\n";
    echo "  test      Crea una orden de prueba para verificar cálculos\n";
    echo "  cleanup   Limpia órdenes de prueba\n";
    echo "  help      Muestra esta ayuda\n\n";
    
    echo "⚙️  OPCIONES PARA 'check' y 'fix':\n";
    echo "  --verbose       Muestra información detallada\n";
    echo "  --order-id=X    Procesa solo la orden con ID X\n\n";
    
    echo "⚙️  OPCIONES PARA 'test':\n";
    echo "  --total=AMOUNT  Monto total para la orden de prueba (default: 119000)\n\n";
    
    echo "💡 EJEMPLOS:\n";
    echo "  php iva_manager.php check --verbose\n";
    echo "  php iva_manager.php fix --order-id=5\n";
    echo "  php iva_manager.php test --total=50000\n";
    echo "  php iva_manager.php cleanup\n\n";
}

// Función para calcular IVA correctamente
function calculateIva($totalAmount) {
    $subtotal = round($totalAmount / 1.19, 2);
    $ivaAmount = round($totalAmount - $subtotal, 2);
    
    return [
        'subtotal' => $subtotal,
        'iva_amount' => $ivaAmount,
        'total_calculated' => $subtotal + $ivaAmount
    ];
}

// Función para verificar si una orden necesita corrección
function needsCorrection($order, $expectedCalculations) {
    $subtotalDiff = abs(($order->subtotal ?? 0) - $expectedCalculations['subtotal']);
    $ivaDiff = abs(($order->iva_amount ?? 0) - $expectedCalculations['iva_amount']);
    
    return $subtotalDiff > 0.01 || $ivaDiff > 0.01;
}

// Comando: check - Verificar cálculos
function commandCheck($options) {
    showMessage("🔍 VERIFICANDO CÁLCULOS DE IVA...", 'info');
    echo "\n";
    
    $query = App\Models\PurchaseOrder::with(['purchaseRequest', 'provider']);
    
    if (isset($options['order_id'])) {
        $query->where('id', $options['order_id']);
        showMessage("🎯 Verificando solo la orden ID: {$options['order_id']}", 'info');
    }
    
    $orders = $query->get();
    
    if ($orders->isEmpty()) {
        showMessage("⚠️  No se encontraron órdenes de compra.", 'warning');
        return;
    }
    
    $totalOrders = $orders->count();
    $ordersWithErrors = 0;
    
    showMessage("📊 Total de órdenes encontradas: {$totalOrders}", 'info');
    echo "\n";
    
    foreach ($orders as $index => $order) {
        $expectedCalculations = calculateIva($order->total_amount);
        
        if (needsCorrection($order, $expectedCalculations)) {
            $ordersWithErrors++;
            showMessage("❌ ERROR en Orden #{$order->id} - {$order->order_number}", 'error');
        } else {
            showMessage("✅ CORRECTO - Orden #{$order->id} - {$order->order_number}", 'success');
        }
        
        if (isset($options['verbose']) || needsCorrection($order, $expectedCalculations)) {
            echo "  🏢 Proveedor: " . ($order->provider->nombre ?? 'N/A') . "\n";
            echo "  💰 Total: $" . number_format($order->total_amount, 2) . "\n";
            echo "  📊 Subtotal: $" . number_format($order->subtotal ?? 0, 2) . 
                 " (Esperado: $" . number_format($expectedCalculations['subtotal'], 2) . ")\n";
            echo "  🧾 IVA: $" . number_format($order->iva_amount ?? 0, 2) . 
                 " (Esperado: $" . number_format($expectedCalculations['iva_amount'], 2) . ")\n";
            echo "\n";
        }
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "📊 Resumen: {$totalOrders} órdenes verificadas, {$ordersWithErrors} con errores\n";
    
    if ($ordersWithErrors == 0) {
        showMessage("🎉 ¡Todos los cálculos están correctos!", 'success');
    } else {
        showMessage("⚠️  Se encontraron {$ordersWithErrors} órdenes con errores.", 'warning');
        showMessage("💡 Ejecuta 'php iva_manager.php fix' para corregirlas.", 'info');
    }
}

// Comando: fix - Corregir cálculos
function commandFix($options) {
    showMessage("🔧 CORRIGIENDO CÁLCULOS DE IVA...", 'warning');
    echo "\n";
    
    $query = App\Models\PurchaseOrder::with(['purchaseRequest', 'provider']);
    
    if (isset($options['order_id'])) {
        $query->where('id', $options['order_id']);
        showMessage("🎯 Corrigiendo solo la orden ID: {$options['order_id']}", 'info');
    }
    
    $orders = $query->get();
    
    if ($orders->isEmpty()) {
        showMessage("⚠️  No se encontraron órdenes de compra.", 'warning');
        return;
    }
    
    $totalOrders = $orders->count();
    $ordersFixed = 0;
    $errors = [];
    
    foreach ($orders as $order) {
        $expectedCalculations = calculateIva($order->total_amount);
        
        if (needsCorrection($order, $expectedCalculations)) {
            try {
                $order->update([
                    'subtotal' => $expectedCalculations['subtotal'],
                    'iva_amount' => $expectedCalculations['iva_amount'],
                    'includes_iva' => true
                ]);
                
                showMessage("✅ Corregida - Orden #{$order->id} - {$order->order_number}", 'success');
                $ordersFixed++;
                
            } catch (Exception $e) {
                $errorMsg = "Error en orden #{$order->id}: " . $e->getMessage();
                showMessage("❌ " . $errorMsg, 'error');
                $errors[] = $errorMsg;
            }
        } else {
            if (isset($options['verbose'])) {
                showMessage("✅ Ya correcta - Orden #{$order->id} - {$order->order_number}", 'info');
            }
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "📊 Resumen: {$totalOrders} órdenes procesadas, {$ordersFixed} corregidas\n";
    
    if ($ordersFixed > 0) {
        showMessage("🎉 ¡Se corrigieron {$ordersFixed} órdenes exitosamente!", 'success');
    } else {
        showMessage("ℹ️  No se encontraron órdenes que requieran corrección.", 'info');
    }
    
    if (!empty($errors)) {
        echo "\n";
        showMessage("❌ Errores encontrados:", 'error');
        foreach ($errors as $error) {
            echo "  • {$error}\n";
        }
    }
}

// Comando: test - Crear orden de prueba
function commandTest($options) {
    $totalAmount = $options['total'] ?? 119000;
    
    showMessage("🧪 CREANDO ORDEN DE PRUEBA...", 'info');
    showMessage("💰 Monto total: $" . number_format($totalAmount, 2), 'info');
    echo "\n";
    
    try {
        // Crear usuario de prueba si no existe
        $user = App\Models\User::first();
        if (!$user) {
            throw new Exception("No se encontraron usuarios en el sistema");
        }
        
        // Crear proveedor de prueba
        $provider = App\Models\Provider::create([
            'nombre' => 'PROVEEDOR PRUEBA IVA ' . date('YmdHis'),
            'nit' => '900' . rand(100000, 999999) . '-' . rand(1, 9),
            'telefono' => '300' . rand(1000000, 9999999),
            'email' => 'prueba' . rand(1000, 9999) . '@test.com',
            'direccion' => 'Dirección de prueba',
            'ciudad' => 'Bogotá',
            'contacto' => 'Contacto de Prueba',
            'estado' => 'activo'
        ]);
        
        // Crear solicitud de prueba
        $purchaseRequest = App\Models\PurchaseRequest::create([
            'request_number' => 'TEST-' . date('YmdHis'),
            'user_id' => $user->id,
            'department' => 'Sistemas',
            'description' => 'Solicitud de prueba para verificar cálculos de IVA',
            'justification' => 'Prueba automatizada del sistema',
            'priority' => 'media',
            'status' => 'aprobada',
            'approved_at' => now(),
            'approved_by' => $user->id,
            'requires_quotations' => false,
            'type' => 'purchase'
        ]);
        
        // Calcular IVA
        $calculations = calculateIva($totalAmount);
        
        // Crear orden de prueba
        $order = App\Models\PurchaseOrder::create([
            'order_number' => 'TEST-ORD-' . date('YmdHis'),
            'purchase_request_id' => $purchaseRequest->id,
            'provider_id' => $provider->id,
            'total_amount' => $totalAmount,
            'subtotal' => $calculations['subtotal'],
            'iva_amount' => $calculations['iva_amount'],
            'includes_iva' => true,
            'status' => 'pendiente',
            'payment_terms' => '30 días',
            'delivery_date' => now()->addDays(15),
            'observations' => 'Orden de prueba para verificar cálculos de IVA'
        ]);
        
        showMessage("✅ Orden de prueba creada: {$order->order_number}", 'success');
        
        // Verificar cálculos
        echo "\n";
        showMessage("🔍 VERIFICANDO CÁLCULOS:", 'info');
        echo "  📋 Orden: {$order->order_number}\n";
        echo "  💰 Total: $" . number_format($order->total_amount, 2) . "\n";
        echo "  📊 Subtotal: $" . number_format($order->subtotal, 2) . "\n";
        echo "  🧾 IVA: $" . number_format($order->iva_amount, 2) . "\n";
        
        $verification = calculateIva($order->total_amount);
        $isCorrect = abs($order->subtotal - $verification['subtotal']) < 0.01 &&
                    abs($order->iva_amount - $verification['iva_amount']) < 0.01;
        
        echo "\n";
        if ($isCorrect) {
            showMessage("🎉 ¡Los cálculos son correctos!", 'success');
        } else {
            showMessage("❌ Los cálculos tienen errores.", 'error');
        }
        
        echo "\n";
        showMessage("💡 Para limpiar órdenes de prueba: php iva_manager.php cleanup", 'info');
        
    } catch (Exception $e) {
        showMessage("❌ Error: " . $e->getMessage(), 'error');
    }
}

// Comando: cleanup - Limpiar órdenes de prueba
function commandCleanup() {
    showMessage("🧹 LIMPIANDO ÓRDENES DE PRUEBA...", 'warning');
    echo "\n";
    
    try {
        $deletedOrders = App\Models\PurchaseOrder::where('order_number', 'LIKE', 'TEST-%')->delete();
        $deletedRequests = App\Models\PurchaseRequest::where('request_number', 'LIKE', 'TEST-%')->delete();
        $deletedProviders = App\Models\Provider::where('nombre', 'LIKE', 'PROVEEDOR PRUEBA IVA%')->delete();
        
        showMessage("✅ Eliminadas {$deletedOrders} órdenes de prueba", 'success');
        showMessage("✅ Eliminadas {$deletedRequests} solicitudes de prueba", 'success');
        showMessage("✅ Eliminados {$deletedProviders} proveedores de prueba", 'success');
        
        echo "\n";
        showMessage("🎉 Limpieza completada exitosamente.", 'success');
        
    } catch (Exception $e) {
        showMessage("❌ Error durante la limpieza: " . $e->getMessage(), 'error');
    }
}

// Función principal
function main($argv) {
    // Procesar argumentos
    $command = $argv[1] ?? 'help';
    $options = [];
    
    for ($i = 2; $i < count($argv); $i++) {
        $arg = $argv[$i];
        
        if ($arg === '--verbose') {
            $options['verbose'] = true;
        } elseif (strpos($arg, '--order-id=') === 0) {
            $options['order_id'] = (int) substr($arg, 11);
        } elseif (strpos($arg, '--total=') === 0) {
            $options['total'] = (float) substr($arg, 8);
        }
    }
    
    // Ejecutar comando
    switch ($command) {
        case 'check':
            showHeader();
            commandCheck($options);
            break;
            
        case 'fix':
            showHeader();
            commandFix($options);
            break;
            
        case 'test':
            showHeader();
            commandTest($options);
            break;
            
        case 'cleanup':
            showHeader();
            commandCleanup();
            break;
            
        case 'help':
        default:
            showHelp();
            break;
    }
    
    echo "\n";
    showMessage("✨ Operación completada.", 'success');
}

// Ejecutar script
main($argv);