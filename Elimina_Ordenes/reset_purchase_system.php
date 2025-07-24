<?php
/**
 * Script Estándar de Limpieza del Sistema de Compras
 * 
 * Este script eliminará ÚNICAMENTE:
 * - Todas las solicitudes de compra (SC-)
 * - Todas las cotizaciones relacionadas con SC-
 * - Todas las preaprobaciones de órdenes de compra relacionadas con SC-
 * - Todas las aprobaciones de órdenes de compra relacionadas con SC-
 * - Todas las órdenes de compra generadas por el sistema (OC-) relacionadas con SC-
 * 
 * PRESERVARÁ:
 * - Solicitudes de servicio (SS-)
 * - Solicitudes de materiales y fotocopias
 * - Cualquier otro tipo de solicitud
 * 
 * Y reseteará los auto_increment a 1 para empezar desde el ID 1
 */

// Configurar la ruta base del proyecto
$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== SCRIPT ESTÁNDAR DE LIMPIEZA DE COMPRAS ===\n";
echo "📍 Ubicación: Elimina_Ordenes/reset_purchase_system.php\n\n";

try {
    // Iniciar transacción para asegurar integridad
    DB::beginTransaction();
    
    echo "1. Verificando tablas existentes...\n";
    
    // Lista de tablas relacionadas con compras (en orden de dependencias)
    $tables = [
        'quotation_item_selections',  // Depende de quotations
        'purchase_orders',           // Depende de purchase_requests y proveedors
        'quotations',               // Depende de purchase_requests
        'purchase_requests'         // Tabla principal
    ];
    
    // Verificar qué tablas existen
    $existingTables = [];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $existingTables[] = $table;
            echo "   ✓ Tabla '$table' encontrada\n";
        } else {
            echo "   ⚠ Tabla '$table' no existe\n";
        }
    }
    
    if (empty($existingTables)) {
        echo "\n❌ No se encontraron tablas de compras. El script terminará.\n";
        return;
    }
    
    echo "\n2. Contando registros antes de la eliminación...\n";
    
    // Contar registros actuales
    $counts = [];
    foreach ($existingTables as $table) {
        $count = DB::table($table)->count();
        $counts[$table] = $count;
        echo "   - $table: $count registros\n";
    }
    
    // Mostrar información específica de solicitudes y órdenes
    if (in_array('purchase_requests', $existingTables)) {
        $scCount = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SC-%')
            ->count();
        $ssCount = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SS-%')
            ->count();
        $materialsCount = DB::table('purchase_requests')
            ->where('type', 'materials')
            ->count();
        
        echo "\n   Detalle de solicitudes:\n";
        echo "   - Solicitudes SC- (a eliminar): $scCount registros\n";
        echo "   - Solicitudes SS- (preservar): $ssCount registros\n";
        echo "   - Solicitudes materiales (preservar): $materialsCount registros\n";
    }
    
    if (in_array('purchase_orders', $existingTables)) {
        $ocCount = DB::table('purchase_orders')
            ->where('order_number', 'LIKE', 'OC-%')
            ->count();
        echo "   - Órdenes OC- (relacionadas): $ocCount registros\n";
    }
    
    echo "\n⚠ ADVERTENCIA IMPORTANTE\n";
    echo "═══════════════════════════\n";
    echo "Esta operación eliminará ÚNICAMENTE datos relacionados con SC-\n";
    echo "Se PRESERVARÁN todas las solicitudes SS- y materiales\n";
    echo "¿Está seguro de continuar? (escriba 'SI' para confirmar): ";
    
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);
    
    if (strtoupper($confirmation) !== 'SI') {
        echo "\n❌ Operación cancelada por el usuario.\n";
        DB::rollBack();
        return;
    }
    
    echo "\n3. Deshabilitando verificación de claves foráneas...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    echo "\n4. Eliminando datos específicos de las tablas...\n";
    
    // Primero eliminar datos dependientes que están relacionados con solicitudes SC-
    echo "   Identificando solicitudes de compra (SC-) a eliminar...\n";
    
    $purchaseRequestIds = [];
    if (in_array('purchase_requests', $existingTables)) {
        $purchaseRequestIds = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SC-%')
            ->pluck('id')
            ->toArray();
        
        echo "   Encontradas " . count($purchaseRequestIds) . " solicitudes SC- para eliminar\n";
    }
    
    // Eliminar cotizaciones relacionadas con solicitudes SC-
    if (in_array('quotations', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Eliminando cotizaciones relacionadas con SC-... ";
        $deletedQuotations = DB::table('quotations')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->delete();
        echo "✓ $deletedQuotations cotizaciones eliminadas\n";
    }
    
    // Eliminar selecciones de cotizaciones relacionadas
    if (in_array('quotation_item_selections', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Eliminando selecciones de cotizaciones... ";
        $deletedSelections = DB::table('quotation_item_selections')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->delete();
        echo "✓ $deletedSelections selecciones eliminadas\n";
    }
    
    // Eliminar órdenes de compra relacionadas con solicitudes SC-
    if (in_array('purchase_orders', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Eliminando órdenes de compra (OC-) relacionadas... ";
        $deletedOrders = DB::table('purchase_orders')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->delete();
        echo "✓ $deletedOrders órdenes eliminadas\n";
    }
    
    // Finalmente eliminar solo las solicitudes de compra (SC-)
    if (in_array('purchase_requests', $existingTables)) {
        echo "   Eliminando solicitudes de compra (SC-)... ";
        $deletedRequests = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SC-%')
            ->delete();
        echo "✓ $deletedRequests solicitudes SC- eliminadas\n";
        
        // Verificar qué solicitudes quedan (SS-, materiales, fotocopias)
        $remainingRequests = DB::table('purchase_requests')->count();
        $ssRequests = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SS-%')
            ->count();
        $materialsRequests = DB::table('purchase_requests')
            ->where('type', 'materials')
            ->count();
        
        echo "   ✓ Solicitudes preservadas: $remainingRequests total ($ssRequests SS-, $materialsRequests materiales)\n";
    }
    
    echo "\n5. Reseteando AUTO_INCREMENT para tablas completamente vaciadas...\n";
    
    // Solo resetear AUTO_INCREMENT para tablas que quedaron completamente vacías
    $tablesToReset = ['quotations', 'quotation_item_selections', 'purchase_orders'];
    
    foreach ($tablesToReset as $table) {
        if (in_array($table, $existingTables)) {
            $count = DB::table($table)->count();
            if ($count == 0) {
                echo "   Reseteando AUTO_INCREMENT de '$table'... ";
                DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                echo "✓ Completado\n";
            } else {
                echo "   Saltando '$table' (contiene $count registros)\n";
            }
        }
    }
    
    // Para purchase_requests, necesitamos calcular el próximo ID basado en los registros restantes
    if (in_array('purchase_requests', $existingTables)) {
        $maxId = DB::table('purchase_requests')->max('id');
        if ($maxId === null) {
            // Si no hay registros, resetear a 1
            echo "   Reseteando AUTO_INCREMENT de 'purchase_requests'... ";
            DB::statement("ALTER TABLE `purchase_requests` AUTO_INCREMENT = 1");
            echo "✓ Completado\n";
        } else {
            // Si hay registros, mantener el AUTO_INCREMENT actual
            echo "   Manteniendo AUTO_INCREMENT de 'purchase_requests' (próximo ID: " . ($maxId + 1) . ")\n";
        }
    }
    
    echo "\n6. Habilitando verificación de claves foráneas...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n7. Verificando resultados...\n";
    
    // Verificar el estado final de las tablas
    foreach ($existingTables as $table) {
        $count = DB::table($table)->count();
        echo "   - $table: $count registros\n";
    }
    
    // Mostrar detalles específicos de purchase_requests
    if (in_array('purchase_requests', $existingTables)) {
        $scCount = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SC-%')
            ->count();
        $ssCount = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SS-%')
            ->count();
        $materialsCount = DB::table('purchase_requests')
            ->where('type', 'materials')
            ->count();
        
        echo "\n   Detalle final de purchase_requests:\n";
        echo "     • Solicitudes SC- (eliminadas): $scCount\n";
        echo "     • Solicitudes SS- (preservadas): $ssCount\n";
        echo "     • Solicitudes de materiales (preservadas): $materialsCount\n";
        
        if ($scCount > 0) {
            echo "   ⚠️ ADVERTENCIA: Aún hay solicitudes SC- en la base de datos\n";
        }
    }
    
    // Confirmar transacción
    DB::commit();
    
    echo "\n✅ OPERACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "\nResumen de la operación:\n";
    echo "- Tablas procesadas: " . count($existingTables) . "\n";
    echo "- Total de registros eliminados: " . array_sum($counts) . "\n";
    echo "- Solo se eliminaron solicitudes SC- y sus dependencias\n";
    echo "- Se preservaron solicitudes SS- y de materiales/fotocopias\n";
    echo "\nElementos eliminados:\n";
    echo "- ✓ Solicitudes de compra (SC-*)\n";
    echo "- ✓ Cotizaciones relacionadas con SC-\n";
    echo "- ✓ Órdenes de compra (OC-*) relacionadas con SC-\n";
    echo "- ✓ Selecciones de cotizaciones relacionadas\n";
    echo "\nElementos preservados:\n";
    echo "- ✓ Solicitudes de servicio (SS-*)\n";
    echo "- ✓ Solicitudes de materiales y fotocopias\n";
    echo "- ✓ Cualquier otro tipo de solicitud\n";
    echo "\nPróximos IDs para nuevas solicitudes de compra:\n";
    echo "- Solicitudes de compra: SC-1 (AUTO_INCREMENT según registros existentes)\n";
    echo "- Cotizaciones: ID 1\n";
    echo "- Órdenes de compra: OC-1\n\n";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    DB::rollBack();
    
    echo "\n❌ ERROR DURANTE LA OPERACIÓN\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "La operación ha sido revertida.\n\n";
    
    // Rehabilitar claves foráneas si es necesario
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } catch (Exception $fkError) {
        echo "Advertencia: No se pudo rehabilitar las claves foráneas: " . $fkError->getMessage() . "\n";
    }
}

echo "=== FIN DEL SCRIPT ===\n";
echo "📍 Ejecutado desde: Elimina_Ordenes/reset_purchase_system.php\n";
