<?php
/**
 * Script Completo para Gestión de IVA e Ipoconsumo en Órdenes de Compra
 * 
 * Este script maneja correctamente todos los tipos de impuestos:
 * - IVA 19%
 * - IVA 5%
 * - Ipoconsumo 8%
 * - Ipoconsumo 4%
 * 
 * Uso:
 * php iva_manager_completo.php [comando] [opciones]
 * 
 * Comandos disponibles:
 * check     - Verifica cálculos de todos los impuestos
 * fix       - Corrige cálculos de todos los impuestos
 * test      - Crea órdenes de prueba para cada tipo de impuesto
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
    showMessage("║         GESTOR COMPLETO DE IVA E IPOCONSUMO - TVS            ║", 'info');
    showMessage("║              Maneja todos los tipos de impuestos             ║", 'info');
    showMessage("╚══════════════════════════════════════════════════════════════╝", 'info');
    echo "\n";
}

// Función para mostrar ayuda
function showHelp() {
    showHeader();
    echo "📖 USO:\n";
    echo "  php iva_manager_completo.php [comando] [opciones]\n\n";
    
    echo "🔧 COMANDOS DISPONIBLES:\n";
    echo "  check     Verifica cálculos de todos los impuestos\n";
    echo "  fix       Corrige cálculos de todos los impuestos\n";
    echo "  test      Crea órdenes de prueba para cada tipo de impuesto\n";
    echo "  cleanup   Limpia órdenes de prueba\n";
    echo "  help      Muestra esta ayuda\n\n";
    
    echo "⚙️  OPCIONES:\n";
    echo "  --verbose       Muestra información detallada\n";
    echo "  --order-id=X    Procesa solo la orden con ID X\n";
    echo "  --dry-run       Solo simula, no hace cambios reales\n\n";
    
    echo "💡 TIPOS DE IMPUESTOS SOPORTADOS:\n";
    echo "  • IVA 19%\n";
    echo "  • IVA 5%\n";
    echo "  • Ipoconsumo 8%\n";
    echo "  • Ipoconsumo 4%\n\n";
    
    echo "💡 EJEMPLOS:\n";
    echo "  php iva_manager_completo.php check --verbose\n";
    echo "  php iva_manager_completo.php fix --order-id=5\n";
    echo "  php iva_manager_completo.php test\n";
    echo "  php iva_manager_completo.php cleanup\n\n";
}

// Función para calcular impuestos basado en las cotizaciones
function calculateTaxesFromQuotations($order) {
    $quotations = $order->purchaseRequest->quotations ?? collect();
    
    if ($quotations->isEmpty()) {
        // Si no hay cotizaciones, usar el método legacy (solo IVA 19%)
        return calculateLegacyIva($order->total_amount);
    }
    
    // Usar la cotización seleccionada o la primera disponible
    $quotation = $quotations->first();
    
    $result = [
        'subtotal' => $quotation->subtotal ?? 0,
        'iva_19_amount' => $quotation->iva_19_amount ?? 0,
        'iva_5_amount' => $quotation->iva_5_amount ?? 0,
        'ipoconsumo_8_amount' => $quotation->ipoconsumo_8_amount ?? 0,
        'ipoconsumo_4_amount' => $quotation->ipoconsumo_4_amount ?? 0,
        'includes_iva_19' => $quotation->includes_iva_19 ?? false,
        'includes_iva_5' => $quotation->includes_iva_5 ?? false,
        'includes_ipoconsumo_8' => $quotation->includes_ipoconsumo_8 ?? false,
        'includes_ipoconsumo_4' => $quotation->includes_ipoconsumo_4 ?? false,
    ];
    
    $totalTaxes = $result['iva_19_amount'] + $result['iva_5_amount'] + 
                  $result['ipoconsumo_8_amount'] + $result['ipoconsumo_4_amount'];
    
    $result['total_calculated'] = $result['subtotal'] + $totalTaxes;
    $result['legacy_iva_amount'] = $result['iva_19_amount']; // Para compatibilidad
    
    return $result;
}

// Función legacy para calcular solo IVA 19%
function calculateLegacyIva($totalAmount) {
    $subtotal = round($totalAmount / 1.19, 2);
    $ivaAmount = round($totalAmount - $subtotal, 2);
    
    return [
        'subtotal' => $subtotal,
        'iva_19_amount' => $ivaAmount,
        'iva_5_amount' => 0,
        'ipoconsumo_8_amount' => 0,
        'ipoconsumo_4_amount' => 0,
        'legacy_iva_amount' => $ivaAmount,
        'total_calculated' => $totalAmount,
        'includes_iva_19' => true,
        'includes_iva_5' => false,
        'includes_ipoconsumo_8' => false,
        'includes_ipoconsumo_4' => false,
    ];
}

// Función para verificar si una orden necesita corrección
function needsCorrection($order, $expectedCalculations) {
    $tolerance = 0.01;
    
    $subtotalDiff = abs(($order->subtotal ?? 0) - $expectedCalculations['subtotal']);
    $ivaDiff = abs(($order->iva_amount ?? 0) - $expectedCalculations['legacy_iva_amount']);
    
    return $subtotalDiff > $tolerance || $ivaDiff > $tolerance;
}

// Comando: check - Verificar cálculos
function commandCheck($options) {
    showMessage("🔍 VERIFICANDO CÁLCULOS DE TODOS LOS IMPUESTOS...", 'info');
    echo "\n";
    
    $query = App\Models\PurchaseOrder::with(['purchaseRequest.quotations', 'provider']);
    
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
    $taxTypesFound = [
        'iva_19' => 0,
        'iva_5' => 0,
        'ipoconsumo_8' => 0,
        'ipoconsumo_4' => 0,
        'legacy' => 0
    ];
    
    showMessage("📊 Total de órdenes encontradas: {$totalOrders}", 'info');
    echo "\n";
    
    foreach ($orders as $order) {
        $expectedCalculations = calculateTaxesFromQuotations($order);
        
        // Contar tipos de impuestos
        if ($expectedCalculations['includes_iva_19']) $taxTypesFound['iva_19']++;
        if ($expectedCalculations['includes_iva_5']) $taxTypesFound['iva_5']++;
        if ($expectedCalculations['includes_ipoconsumo_8']) $taxTypesFound['ipoconsumo_8']++;
        if ($expectedCalculations['includes_ipoconsumo_4']) $taxTypesFound['ipoconsumo_4']++;
        if (!$expectedCalculations['includes_iva_19'] && !$expectedCalculations['includes_iva_5'] && 
            !$expectedCalculations['includes_ipoconsumo_8'] && !$expectedCalculations['includes_ipoconsumo_4']) {
            $taxTypesFound['legacy']++;
        }
        
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
            
            if ($expectedCalculations['includes_iva_19']) {
                echo "  🧾 IVA 19%: $" . number_format($expectedCalculations['iva_19_amount'], 2) . "\n";
            }
            if ($expectedCalculations['includes_iva_5']) {
                echo "  🧾 IVA 5%: $" . number_format($expectedCalculations['iva_5_amount'], 2) . "\n";
            }
            if ($expectedCalculations['includes_ipoconsumo_8']) {
                echo "  🧾 Ipoconsumo 8%: $" . number_format($expectedCalculations['ipoconsumo_8_amount'], 2) . "\n";
            }
            if ($expectedCalculations['includes_ipoconsumo_4']) {
                echo "  🧾 Ipoconsumo 4%: $" . number_format($expectedCalculations['ipoconsumo_4_amount'], 2) . "\n";
            }
            
            echo "  🧾 IVA Legacy: $" . number_format($order->iva_amount ?? 0, 2) . 
                 " (Esperado: $" . number_format($expectedCalculations['legacy_iva_amount'], 2) . ")\n";
            echo "\n";
        }
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "📊 Resumen: {$totalOrders} órdenes verificadas, {$ordersWithErrors} con errores\n\n";
    
    echo "📈 Tipos de impuestos encontrados:\n";
    echo "  • IVA 19%: {$taxTypesFound['iva_19']} órdenes\n";
    echo "  • IVA 5%: {$taxTypesFound['iva_5']} órdenes\n";
    echo "  • Ipoconsumo 8%: {$taxTypesFound['ipoconsumo_8']} órdenes\n";
    echo "  • Ipoconsumo 4%: {$taxTypesFound['ipoconsumo_4']} órdenes\n";
    echo "  • Legacy (solo IVA 19%): {$taxTypesFound['legacy']} órdenes\n\n";
    
    if ($ordersWithErrors == 0) {
        showMessage("🎉 ¡Todos los cálculos están correctos!", 'success');
    } else {
        showMessage("⚠️  Se encontraron {$ordersWithErrors} órdenes con errores.", 'warning');
        showMessage("💡 Ejecuta 'php iva_manager_completo.php fix' para corregirlas.", 'info');
    }
}

// Comando: fix - Corregir cálculos
function commandFix($options) {
    $isDryRun = isset($options['dry_run']);
    
    if ($isDryRun) {
        showMessage("🧪 MODO SIMULACIÓN - NO SE HARÁN CAMBIOS REALES", 'warning');
    } else {
        showMessage("🔧 CORRIGIENDO CÁLCULOS DE TODOS LOS IMPUESTOS...", 'warning');
    }
    echo "\n";
    
    $query = App\Models\PurchaseOrder::with(['purchaseRequest.quotations', 'provider']);
    
    if (isset($options['order_id'])) {
        $query->where('id', $options['order_id']);
        showMessage("🎯 Procesando solo la orden ID: {$options['order_id']}", 'info');
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
        $expectedCalculations = calculateTaxesFromQuotations($order);
        
        if (needsCorrection($order, $expectedCalculations)) {
            try {
                if (!$isDryRun) {
                    $order->update([
                        'subtotal' => $expectedCalculations['subtotal'],
                        'iva_amount' => $expectedCalculations['legacy_iva_amount'],
                        'includes_iva' => $expectedCalculations['includes_iva_19'] || $expectedCalculations['includes_iva_5']
                    ]);
                }
                
                $action = $isDryRun ? "SIMULARÍA CORREGIR" : "Corregida";
                showMessage("✅ {$action} - Orden #{$order->id} - {$order->order_number}", 'success');
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
    $action = $isDryRun ? "simularían corrección" : "corregidas";
    echo "📊 Resumen: {$totalOrders} órdenes procesadas, {$ordersFixed} {$action}\n";
    
    if ($ordersFixed > 0) {
        $message = $isDryRun ? 
            "🧪 Simulación completada. {$ordersFixed} órdenes necesitan corrección." :
            "🎉 ¡Se corrigieron {$ordersFixed} órdenes exitosamente!";
        showMessage($message, 'success');
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

// Comando: test - Crear órdenes de prueba para cada tipo de impuesto
function commandTest($options) {
    showMessage("🧪 CREANDO ÓRDENES DE PRUEBA PARA TODOS LOS TIPOS DE IMPUESTOS...", 'info');
    echo "\n";
    
    $testCases = [
        [
            'name' => 'IVA 19%',
            'subtotal' => 100000,
            'includes_iva_19' => true,
            'iva_19_amount' => 19000,
            'total' => 119000
        ],
        [
            'name' => 'IVA 5%',
            'subtotal' => 100000,
            'includes_iva_5' => true,
            'iva_5_amount' => 5000,
            'total' => 105000
        ],
        [
            'name' => 'Ipoconsumo 8%',
            'subtotal' => 100000,
            'includes_ipoconsumo_8' => true,
            'ipoconsumo_8_amount' => 8000,
            'total' => 108000
        ],
        [
            'name' => 'Ipoconsumo 4%',
            'subtotal' => 100000,
            'includes_ipoconsumo_4' => true,
            'ipoconsumo_4_amount' => 4000,
            'total' => 104000
        ],
        [
            'name' => 'IVA 19% + Ipoconsumo 8%',
            'subtotal' => 100000,
            'includes_iva_19' => true,
            'iva_19_amount' => 19000,
            'includes_ipoconsumo_8' => true,
            'ipoconsumo_8_amount' => 8000,
            'total' => 127000
        ]
    ];
    
    try {
        $user = App\Models\User::first();
        if (!$user) {
            throw new Exception("No se encontraron usuarios en el sistema");
        }
        
        foreach ($testCases as $index => $testCase) {
            showMessage("📝 Creando prueba: {$testCase['name']}", 'info');
            
            // Crear proveedor de prueba
            $provider = App\Models\Provider::create([
                'nombre' => 'PROVEEDOR PRUEBA ' . strtoupper(str_replace(['%', ' ', '+'], ['', '_', '_'], $testCase['name'])) . '_' . date('YmdHis'),
                'nit' => '900' . rand(100000, 999999) . '-' . rand(1, 9),
                'telefono' => '300' . rand(1000000, 9999999),
                'email' => 'prueba' . rand(1000, 9999) . '@test.com',
                'direccion' => 'Dirección de prueba',
                'ciudad' => 'Bogotá',
                'persona_contacto' => 'Contacto de Prueba',
                'servicio_producto' => 'Productos de prueba'
            ]);
            
            // Crear solicitud de prueba
            $purchaseRequest = App\Models\PurchaseRequest::create([
                'request_number' => 'TEST-' . ($index + 1) . '-' . date('YmdHis'),
                'user_id' => $user->id,
                'requester' => $user->name,
                'section_area' => 'Sistemas - Prueba',
                'status' => 'aprobada',
                'approved_by' => $user->id,
                'type' => 'purchase',
                'request_date' => now()->format('Y-m-d'),
                'purchase_justification' => 'Prueba automatizada de cálculo de ' . $testCase['name']
            ]);
            
            // Crear cotización de prueba
            $quotation = App\Models\Quotation::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_name' => $provider->nombre,
                'subtotal' => $testCase['subtotal'],
                'total_amount' => $testCase['total'],
                'includes_iva_19' => $testCase['includes_iva_19'] ?? false,
                'iva_19_amount' => $testCase['iva_19_amount'] ?? 0,
                'includes_iva_5' => $testCase['includes_iva_5'] ?? false,
                'iva_5_amount' => $testCase['iva_5_amount'] ?? 0,
                'includes_ipoconsumo_8' => $testCase['includes_ipoconsumo_8'] ?? false,
                'ipoconsumo_8_amount' => $testCase['ipoconsumo_8_amount'] ?? 0,
                'includes_ipoconsumo_4' => $testCase['includes_ipoconsumo_4'] ?? false,
                'ipoconsumo_4_amount' => $testCase['ipoconsumo_4_amount'] ?? 0,
                'delivery_time' => '5 días',
                'payment_method' => 'Contado',
                'validity' => '30 días',
                'warranty' => '1 año',
                'file_path' => 'test/cotizacion_prueba_' . ($index + 1) . '.pdf'
            ]);
            
            // Crear orden de prueba
            $order = App\Models\PurchaseOrder::create([
                'order_number' => 'TEST-ORD-' . ($index + 1) . '-' . date('YmdHis'),
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'total_amount' => $testCase['total'],
                'subtotal' => $testCase['subtotal'],
                'iva_amount' => $testCase['iva_19_amount'] ?? 0, // Para compatibilidad legacy
                'includes_iva' => ($testCase['includes_iva_19'] ?? false) || ($testCase['includes_iva_5'] ?? false),
                'status' => 'pending',
                'payment_terms' => '30 días',
                'delivery_date' => now()->addDays(15),
                'observations' => 'Orden de prueba para ' . $testCase['name'],
                'file_path' => 'test/orden_compra_prueba_' . ($index + 1) . '.pdf',
                'created_by' => $user->id
            ]);
            
            showMessage("✅ Creada: {$order->order_number} - {$testCase['name']}", 'success');
        }
        
        echo "\n";
        showMessage("🎉 ¡Se crearon " . count($testCases) . " órdenes de prueba exitosamente!", 'success');
        showMessage("💡 Para verificar: php iva_manager_completo.php check --verbose", 'info');
        showMessage("💡 Para limpiar: php iva_manager_completo.php cleanup", 'info');
        
    } catch (Exception $e) {
        showMessage("❌ Error: " . $e->getMessage(), 'error');
    }
}

// Comando: cleanup - Limpiar órdenes de prueba
function commandCleanup() {
    showMessage("🧹 LIMPIANDO ÓRDENES DE PRUEBA...", 'warning');
    echo "\n";
    
    try {
        // Usar consultas SQL directas para eliminar todo de una vez
        
        // 1. Eliminar órdenes de compra de prueba
        $deletedOrders = DB::delete("DELETE FROM purchase_orders WHERE order_number LIKE 'TEST-%' OR observations LIKE '%prueba%'");
        
        // 2. Eliminar cotizaciones de prueba
        $deletedQuotations = DB::delete("DELETE FROM quotations WHERE provider_name LIKE 'PROVEEDOR PRUEBA%'");
        
        // 3. Eliminar solicitudes de prueba
        $deletedRequests = DB::delete("DELETE FROM purchase_requests WHERE request_number LIKE 'TEST-%'");
        
        // 4. Finalmente eliminar proveedores de prueba
        $deletedProviders = DB::delete("DELETE FROM proveedors WHERE nombre LIKE 'PROVEEDOR PRUEBA%'");
        
        showMessage("✅ Eliminadas {$deletedOrders} órdenes de prueba", 'success');
        showMessage("✅ Eliminadas {$deletedQuotations} cotizaciones de prueba", 'success');
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
        } elseif ($arg === '--dry-run') {
            $options['dry_run'] = true;
        } elseif (strpos($arg, '--order-id=') === 0) {
            $options['order_id'] = (int) substr($arg, 11);
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