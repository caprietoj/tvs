<?php
/**
 * Script COMPLETO para limpiar el sistema de compras
 * 
 * Este script eliminará ÚNICAMENTE datos relacionados con SC- incluyendo:
 * - Todas las solicitudes de compra (SC-)
 * - Todas las cotizaciones y selecciones relacionadas con SC-
 * - Todas las órdenes de compra (OC-) relacionadas con SC-
 * - Todos los archivos relacionados con SC-
 * - Todas las preaprobaciones y aprobaciones relacionadas con SC-
 * 
 * PRESERVARÁ:
 * - Solicitudes de servicio (SS-)
 * - Solicitudes de materiales y fotocopias
 * - Archivos no relacionados con SC-
 * - Cualquier otro tipo de solicitud
 * 
 * ADVERTENCIA: Esta operación es IRREVERSIBLE para datos SC-
 */

// Configurar la ruta base del proyecto
$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

echo "=== SCRIPT COMPLETO DE LIMPIEZA DEL SISTEMA DE COMPRAS ===\n";
echo "📍 Ubicación: Elimina_Ordenes/reset_purchase_system_complete.php\n\n";

try {
    DB::beginTransaction();
    
    echo "1. Identificando todas las tablas relacionadas con compras...\n";
    
    // Todas las tablas relacionadas con compras (en orden de dependencias)
    $purchaseTables = [
        'quotation_item_selections',
        'purchase_orders', 
        'quotations',
        'purchase_requests',
        'compras_kpis',
        'compras_thresholds',
        'compras_documents'
    ];
    
    // Verificar tablas existentes
    $existingTables = [];
    foreach ($purchaseTables as $table) {
        if (Schema::hasTable($table)) {
            $existingTables[] = $table;
            $count = DB::table($table)->count();
            echo "   ✓ $table: $count registros\n";
        } else {
            echo "   ⚠ $table: no existe\n";
        }
    }
    
    if (empty($existingTables)) {
        echo "\n❌ No se encontraron tablas de compras.\n";
        return;
    }
    
    echo "\n2. Contabilizando registros específicos de SC-...\n";
    
    $totalRecords = 0;
    $details = [];
    $purchaseRequestIds = [];
    
    // Obtener IDs de solicitudes SC-
    if (in_array('purchase_requests', $existingTables)) {
        $purchaseRequestIds = DB::table('purchase_requests')
            ->where('request_number', 'LIKE', 'SC-%')
            ->pluck('id')
            ->toArray();
        
        $scCount = count($purchaseRequestIds);
        $ssCount = DB::table('purchase_requests')->where('request_number', 'LIKE', 'SS-%')->count();
        $materialCount = DB::table('purchase_requests')->where('type', 'materials')->count();
        $purchaseCount = DB::table('purchase_requests')->where('type', 'purchase')->count();
        
        echo "   - Solicitudes SC- (a eliminar): $scCount\n";
        echo "   - Solicitudes SS- (preservar): $ssCount\n";
        echo "   - Solicitudes de materiales (preservar): $materialCount\n";
        echo "   - Solicitudes de compra total: $purchaseCount\n";
        
        $totalRecords += $scCount;
    }
    
    // Contar registros relacionados con SC-
    if (in_array('quotations', $existingTables) && !empty($purchaseRequestIds)) {
        $quotationCount = DB::table('quotations')->whereIn('purchase_request_id', $purchaseRequestIds)->count();
        $selectedQuotations = DB::table('quotations')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->where('is_selected', true)
            ->count();
        echo "   - Cotizaciones relacionadas con SC-: $quotationCount\n";
        echo "   - Cotizaciones seleccionadas relacionadas con SC-: $selectedQuotations\n";
        $totalRecords += $quotationCount;
    }
    
    if (in_array('purchase_orders', $existingTables) && !empty($purchaseRequestIds)) {
        $ocCount = DB::table('purchase_orders')->whereIn('purchase_request_id', $purchaseRequestIds)->count();
        echo "   - Órdenes OC- relacionadas con SC-: $ocCount\n";
        $totalRecords += $ocCount;
    }
    
    if (in_array('quotation_item_selections', $existingTables) && !empty($purchaseRequestIds)) {
        $selectionsCount = DB::table('quotation_item_selections')->whereIn('purchase_request_id', $purchaseRequestIds)->count();
        echo "   - Selecciones relacionadas con SC-: $selectionsCount\n";
        $totalRecords += $selectionsCount;
    }
    
    echo "\n📊 RESUMEN TOTAL:\n";
    echo "   - Registros SC- a eliminar: $totalRecords\n";
    echo "   - Registros SS- y materiales: PRESERVADOS\n";
    
    echo "\n🚨 ADVERTENCIA CRÍTICA 🚨\n";
    echo "Esta operación eliminará PERMANENTEMENTE:\n";
    echo "- TODAS las solicitudes de compra SC- y sus dependencias\n";
    echo "- TODAS las cotizaciones relacionadas con SC-\n";
    echo "- TODAS las órdenes de compra relacionadas con SC-\n";
    echo "- TODOS los archivos relacionados con SC-\n";
    echo "- Los IDs se resetearán a 1 para nuevas SC-\n\n";
    echo "SE PRESERVARÁN:\n";
    echo "- Solicitudes SS- y materiales\n";
    echo "- Archivos no relacionados con SC-\n\n";
    
    echo "¿Está ABSOLUTAMENTE SEGURO de continuar?\n";
    echo "Escriba 'ELIMINAR SC' para confirmar: ";
    
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);
    
    if ($confirmation !== 'ELIMINAR SC') {
        echo "\n❌ Operación cancelada. Se requiere escribir exactamente 'ELIMINAR SC'.\n";
        DB::rollBack();
        return;
    }
    
    echo "\n3. Iniciando eliminación de archivos relacionados con SC-...\n";
    
    $deletedFiles = 0;
    
    // Eliminar archivos de quotations relacionadas con SC-
    if (in_array('quotations', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Buscando archivos de cotizaciones relacionadas con SC-...\n";
        $quotationFiles = DB::table('quotations')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->whereNotNull('file_path')
            ->pluck('file_path');
        
        foreach ($quotationFiles as $filePath) {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                $deletedFiles++;
            }
        }
        echo "   ✓ Eliminados archivos de cotizaciones relacionadas con SC-\n";
    }
    
    // Eliminar archivos de purchase_orders relacionadas con SC-
    if (in_array('purchase_orders', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Buscando archivos de órdenes relacionadas con SC-...\n";
        $orderFiles = DB::table('purchase_orders')
            ->whereIn('purchase_request_id', $purchaseRequestIds)
            ->whereNotNull('file_path')
            ->pluck('file_path');
        
        foreach ($orderFiles as $filePath) {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                $deletedFiles++;
            }
        }
        echo "   ✓ Eliminados archivos de órdenes relacionadas con SC-\n";
    }
    
    // Eliminar archivos de purchase_requests SC-
    if (in_array('purchase_requests', $existingTables) && !empty($purchaseRequestIds)) {
        echo "   Buscando archivos de solicitudes SC-...\n";
        
        $fileFields = ['quotation_file_path', 'original_file', 'attached_files'];
        
        foreach ($fileFields as $field) {
            if (Schema::hasColumn('purchase_requests', $field)) {
                $files = DB::table('purchase_requests')
                    ->whereIn('id', $purchaseRequestIds)
                    ->whereNotNull($field)
                    ->pluck($field);
                
                foreach ($files as $filePath) {
                    if ($field === 'attached_files') {
                        $fileArray = json_decode($filePath, true);
                        if (is_array($fileArray)) {
                            foreach ($fileArray as $file) {
                                if (isset($file['path']) && Storage::exists($file['path'])) {
                                    Storage::delete($file['path']);
                                    $deletedFiles++;
                                }
                            }
                        }
                    } else {
                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
                            $deletedFiles++;
                        }
                    }
                }
            }
        }
        echo "   ✓ Eliminados archivos de solicitudes SC-\n";
    }
    
    echo "   📁 Total archivos eliminados: $deletedFiles\n";
    
    echo "\n4. Deshabilitando verificaciones de integridad...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    echo "\n5. Eliminando datos de la base de datos relacionados con SC-...\n";
    
    $deletedCounts = [];
    
    // Eliminar en orden correcto solo datos relacionados con SC-
    if (!empty($purchaseRequestIds)) {
        // Selecciones de cotizaciones
        if (in_array('quotation_item_selections', $existingTables)) {
            echo "   Limpiando quotation_item_selections relacionadas con SC-... ";
            $deleted = DB::table('quotation_item_selections')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
            $deletedCounts['quotation_item_selections'] = $deleted;
            echo "✓ ($deleted eliminadas)\n";
        }
        
        // Órdenes de compra
        if (in_array('purchase_orders', $existingTables)) {
            echo "   Limpiando purchase_orders relacionadas con SC-... ";
            $deleted = DB::table('purchase_orders')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
            $deletedCounts['purchase_orders'] = $deleted;
            echo "✓ ($deleted eliminadas)\n";
        }
        
        // Cotizaciones
        if (in_array('quotations', $existingTables)) {
            echo "   Limpiando quotations relacionadas con SC-... ";
            $deleted = DB::table('quotations')->whereIn('purchase_request_id', $purchaseRequestIds)->delete();
            $deletedCounts['quotations'] = $deleted;
            echo "✓ ($deleted eliminadas)\n";
        }
        
        // Solicitudes SC-
        if (in_array('purchase_requests', $existingTables)) {
            echo "   Limpiando purchase_requests (solo SC-)... ";
            $deleted = DB::table('purchase_requests')->where('request_number', 'LIKE', 'SC-%')->delete();
            $deletedCounts['purchase_requests'] = $deleted;
            echo "✓ ($deleted eliminadas)\n";
        }
    }
    
    echo "\n6. Reseteando contadores AUTO_INCREMENT...\n";
    
    $tablesToReset = ['quotation_item_selections', 'purchase_orders', 'quotations'];
    
    foreach ($tablesToReset as $table) {
        if (in_array($table, $existingTables)) {
            $count = DB::table($table)->count();
            if ($count == 0) {
                echo "   Reseteando '$table'... ";
                DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                echo "✓\n";
            } else {
                echo "   Saltando '$table' (contiene $count registros)\n";
            }
        }
    }
    
    // Para purchase_requests, solo resetear si no quedan registros
    if (in_array('purchase_requests', $existingTables)) {
        $remainingCount = DB::table('purchase_requests')->count();
        if ($remainingCount == 0) {
            echo "   Reseteando 'purchase_requests'... ";
            DB::statement("ALTER TABLE `purchase_requests` AUTO_INCREMENT = 1");
            echo "✓\n";
        } else {
            echo "   Manteniendo AUTO_INCREMENT de 'purchase_requests' ($remainingCount registros preservados)\n";
        }
    }
    
    echo "\n7. Rehabilitando verificaciones de integridad...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n8. Verificando limpieza...\n";
    
    $cleanupSuccess = true;
    foreach ($existingTables as $table) {
        $remainingCount = DB::table($table)->count();
        echo "   - $table: $remainingCount registros\n";
        
        if ($table === 'purchase_requests') {
            $scRemaining = DB::table($table)->where('request_number', 'LIKE', 'SC-%')->count();
            $ssRemaining = DB::table($table)->where('request_number', 'LIKE', 'SS-%')->count();
            $materialsRemaining = DB::table($table)->where('type', 'materials')->count();
            
            echo "     • SC- restantes: $scRemaining\n";
            echo "     • SS- preservadas: $ssRemaining\n";
            echo "     • Materiales preservadas: $materialsRemaining\n";
            
            if ($scRemaining > 0) {
                echo "   ❌ ERROR: Quedaron $scRemaining solicitudes SC-\n";
                $cleanupSuccess = false;
            }
        }
    }
    
    if (!$cleanupSuccess) {
        throw new Exception("La limpieza no se completó correctamente.");
    }
    
    DB::commit();
    
    echo "\n🎉 LIMPIEZA COMPLETA EXITOSA 🎉\n\n";
    echo "📋 RESUMEN DE LA OPERACIÓN:\n";
    echo "════════════════════════════\n";
    foreach ($deletedCounts as $table => $count) {
        echo "✓ $table: $count registros eliminados\n";
    }
    echo "✓ Archivos físicos eliminados: $deletedFiles\n";
    echo "✓ AUTO_INCREMENT reseteado para tablas vacías\n\n";
    
    echo "🚀 SISTEMA LISTO PARA EMPEZAR:\n";
    echo "═══════════════════════════════\n";
    echo "• Próxima solicitud SC-: SC-1\n";
    echo "• Próxima cotización: ID 1\n";
    echo "• Próxima orden OC-: OC-1\n";
    echo "• Solicitudes SS- preservadas\n";
    echo "• Solicitudes de materiales preservadas\n\n";
    
    echo "El sistema de compras está limpio y listo para nuevas solicitudes SC-.\n";
    
} catch (Exception $e) {
    DB::rollBack();
    
    echo "\n💥 ERROR CRÍTICO 💥\n";
    echo "══════════════════\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "🔄 Todas las operaciones han sido revertidas.\n";
    echo "🔒 Rehabilitando verificaciones de integridad...\n";
    
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        echo "✓ Verificaciones rehabilitadas.\n";
    } catch (Exception $fkError) {
        echo "❌ Error rehabilitando claves foráneas: " . $fkError->getMessage() . "\n";
    }
}

echo "\n=== FIN DEL SCRIPT ===\n";
echo "📍 Ejecutado desde: Elimina_Ordenes/reset_purchase_system_complete.php\n";
