<?php
/**
 * Script Simple de Limpieza del Sistema de Compras
 * 
 * Elimina todos los datos y resetea IDs a 1 para:
 * - Solicitudes de compra (SC-)
 * - Cotizaciones relacionadas con SC-
 * - Órdenes de compra (OC-) relacionadas con SC-
 * - Selecciones de cotizaciones relacionadas con SC-
 * 
 * PRESERVA:
 * - Solicitudes de servicio (SS-)
 * - Solicitudes de materiales y fotocopias
 * - Cualquier otra solicitud que no sea SC-
 */

// Configurar la ruta base del proyecto
$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 LIMPIEZA DEL SISTEMA DE COMPRAS 🧹\n";
echo "=====================================\n";
echo "📍 Ubicación: Elimina_Ordenes/clean_purchases.php\n\n";

try {
    // Tablas a limpiar en orden correcto
    $tables = [
        'quotation_item_selections',
        'purchase_orders',
        'quotations', 
        'purchase_requests'
    ];
    
    echo "📊 Estado actual:\n";
    $total = 0;
    $purchaseRequestIds = [];
    
    foreach ($tables as $table) {
        $count = DB::table($table)->count();
        echo "   $table: $count registros\n";
        
        if ($table === 'purchase_requests') {
            // Solo contar las solicitudes SC-
            $scCount = DB::table($table)->where('request_number', 'LIKE', 'SC-%')->count();
            $ssCount = DB::table($table)->where('request_number', 'LIKE', 'SS-%')->count();
            $materialsCount = DB::table($table)->where('type', 'materials')->count();
            
            echo "     • SC- (a eliminar): $scCount\n";
            echo "     • SS- (preservar): $ssCount\n";
            echo "     • Materiales (preservar): $materialsCount\n";
            
            $total += $scCount; // Solo contar SC- para eliminación
            $purchaseRequestIds = DB::table($table)->where('request_number', 'LIKE', 'SC-%')->pluck('id')->toArray();
        } else {
            // Para otras tablas, contar solo las relacionadas con SC-
            if ($table === 'quotations' && !empty($purchaseRequestIds)) {
                $relatedCount = DB::table($table)->whereIn('purchase_request_id', $purchaseRequestIds)->count();
                $total += $relatedCount;
                echo "     • Relacionadas con SC-: $relatedCount\n";
            } elseif ($table === 'purchase_orders' && !empty($purchaseRequestIds)) {
                $relatedCount = DB::table($table)->whereIn('purchase_request_id', $purchaseRequestIds)->count();
                $total += $relatedCount;
                echo "     • Relacionadas con SC-: $relatedCount\n";
            } elseif ($table === 'quotation_item_selections' && !empty($purchaseRequestIds)) {
                $relatedCount = DB::table($table)->whereIn('purchase_request_id', $purchaseRequestIds)->count();
                $total += $relatedCount;
                echo "     • Relacionadas con SC-: $relatedCount\n";
            }
        }
    }
    
    echo "\n   TOTAL A ELIMINAR: $total registros relacionados con SC-\n\n";
    
    if ($total == 0) {
        echo "✅ No hay datos de compras (SC-) para eliminar.\n";
        echo "ℹ️  El sistema ya está limpio o no hay solicitudes SC-.\n";
        return;
    }
    
    echo "⚠️  CONFIRMACIÓN REQUERIDA\n";
    echo "═══════════════════════════\n";
    echo "Se eliminarán $total registros relacionados SOLO con solicitudes SC-\n";
    echo "Se PRESERVARÁN todas las solicitudes SS- y materiales\n\n";
    echo "¿Confirma proceder? (y/n): ";
    
    $handle = fopen("php://stdin", "r");
    $confirm = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($confirm) !== 'y') {
        echo "❌ Operación cancelada por el usuario.\n";
        return;
    }
    
    echo "\n🗑️  Iniciando eliminación de datos relacionados con SC-...\n";
    
    DB::beginTransaction();
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    $deletedCounts = [];
    
    // 1. Selecciones de cotizaciones relacionadas con SC-
    if (!empty($purchaseRequestIds)) {
        echo "   Limpiando quotation_item_selections relacionadas... ";
        $deleted = DB::table('quotation_item_selections')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
        $deletedCounts['quotation_item_selections'] = $deleted;
        DB::statement("ALTER TABLE `quotation_item_selections` AUTO_INCREMENT = 1");
        echo "✅ ($deleted eliminadas)\n";
    }
    
    // 2. Órdenes de compra relacionadas con SC-
    if (!empty($purchaseRequestIds)) {
        echo "   Limpiando purchase_orders relacionadas... ";
        $deleted = DB::table('purchase_orders')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
        $deletedCounts['purchase_orders'] = $deleted;
        DB::statement("ALTER TABLE `purchase_orders` AUTO_INCREMENT = 1");
        echo "✅ ($deleted eliminadas)\n";
    }
    
    // 3. Cotizaciones relacionadas con SC-
    if (!empty($purchaseRequestIds)) {
        echo "   Limpiando quotations relacionadas... ";
        $deleted = DB::table('quotations')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
        $deletedCounts['quotations'] = $deleted;
        DB::statement("ALTER TABLE `quotations` AUTO_INCREMENT = 1");
        echo "✅ ($deleted eliminadas)\n";
    }
    
    // 4. Solo solicitudes SC-
    echo "   Limpiando purchase_requests (solo SC-)... ";
    $deleted = DB::table('purchase_requests')->where('request_number', 'LIKE', 'SC-%')->delete();
    $deletedCounts['purchase_requests'] = $deleted;
    
    // Para purchase_requests, solo resetear si no quedan registros
    $remainingCount = DB::table('purchase_requests')->count();
    if ($remainingCount == 0) {
        DB::statement("ALTER TABLE `purchase_requests` AUTO_INCREMENT = 1");
        echo "✅ ($deleted eliminadas, AUTO_INCREMENT reseteado)\n";
    } else {
        echo "✅ ($deleted eliminadas, $remainingCount solicitudes preservadas)\n";
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    DB::commit();
    
    echo "\n🎉 ¡LIMPIEZA COMPLETADA EXITOSAMENTE!\n";
    echo "═══════════════════════════════════════\n";
    echo "✓ Solo se eliminaron datos relacionados con SC-\n";
    echo "✓ Se preservaron solicitudes SS- y materiales\n";
    echo "✓ IDs de cotizaciones y órdenes reseteados a 1\n\n";
    
    echo "📊 RESUMEN DE ELIMINACIÓN:\n";
    foreach ($deletedCounts as $table => $count) {
        echo "   • $table: $count registros eliminados\n";
    }
    
    echo "\n🚀 PRÓXIMOS IDs DISPONIBLES:\n";
    echo "   • Próxima solicitud de compra: SC-1\n";
    echo "   • Próxima cotización: ID 1\n";
    echo "   • Próxima orden de compra: OC-1\n";
    echo "   • Próxima selección: ID 1\n\n";
    
    echo "✅ El sistema de compras está listo para empezar desde cero.\n";
    
} catch (Exception $e) {
    DB::rollBack();
    
    echo "\n💥 ERROR DURANTE LA OPERACIÓN\n";
    echo "═══════════════════════════════\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "🔄 Todas las operaciones han sido revertidas.\n";
    
    // Rehabilitar claves foráneas
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } catch (Exception $fkError) {
        echo "⚠️  Advertencia: " . $fkError->getMessage() . "\n";
    }
}

echo "\n=== FIN DEL SCRIPT ===\n";
echo "📍 Ejecutado desde: Elimina_Ordenes/clean_purchases.php\n";
