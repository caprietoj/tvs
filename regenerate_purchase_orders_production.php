<?php
/**
 * Script de producción para regenerar órdenes de compra con proveedor correcto
 * 
 * Este script identifica y corrige órdenes de compra que fueron generadas con 
 * el proveedor incorrecto (primera entrada de la base de datos en lugar del 
 * proveedor de la cotización seleccionada).
 */

require __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Proveedor;
use App\Models\Quotation;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== REGENERADOR DE ÓRDENES DE COMPRA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// CONFIGURACIÓN PRINCIPAL
$DRY_RUN = false; // Cambiar a true para solo simular
$BACKUP_ENABLED = true; // Crear backup de datos originales
$VERBOSE = true; // Mostrar información detallada

echo "CONFIGURACIÓN:\n";
echo "- Modo simulación: " . ($DRY_RUN ? "SÍ" : "NO") . "\n";
echo "- Backup automático: " . ($BACKUP_ENABLED ? "SÍ" : "NO") . "\n";
echo "- Modo verbose: " . ($VERBOSE ? "SÍ" : "NO") . "\n\n";

if ($DRY_RUN) {
    echo "⚠️  MODO SIMULACIÓN ACTIVADO - No se realizarán cambios reales\n\n";
}

try {
    DB::beginTransaction();
    
    // === PASO 1: Identificar órdenes problemáticas ===
    echo "=== PASO 1: Identificando órdenes problemáticas ===\n";
    
    if ($VERBOSE) {
        echo "Buscando órdenes de compra con proveedores incorrectos...\n";
    }
    
    $problematicOrders = PurchaseOrder::with([
        'purchaseRequest.selectedQuotation', 
        'purchaseRequest.preApprovedQuotation', 
        'provider'
    ])
    ->whereHas('purchaseRequest', function($query) {
        $query->where('type', 'purchase')
              ->where(function($subQuery) {
                  $subQuery->whereNotNull('selected_quotation_id')
                           ->orWhereNotNull('pre_approved_quotation_id');
              });
    })
    ->get()
    ->filter(function($order) use ($VERBOSE) {
        $request = $order->purchaseRequest;
        
        if (!$request) {
            if ($VERBOSE) {
                echo "⚠️  Orden {$order->order_number}: Sin solicitud asociada\n";
            }
            return false;
        }
        
        $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
        
        if (!$quotation) {
            if ($VERBOSE) {
                echo "ℹ️  Orden {$order->order_number}: Sin cotización (posible servicio directo)\n";
            }
            return false;
        }
        
        // Verificar si el proveedor de la orden NO coincide con el de la cotización
        $quotationProviderName = trim(strtolower($quotation->provider_name));
        $orderProviderName = trim(strtolower($order->provider->nombre ?? ''));
        
        $isProblematic = $quotationProviderName !== $orderProviderName;
        
        if ($VERBOSE && $isProblematic) {
            echo "🔍 Problema detectado en orden {$order->order_number}:\n";
            echo "   Proveedor actual: {$order->provider->nombre}\n";
            echo "   Proveedor esperado: {$quotation->provider_name}\n";
        }
        
        return $isProblematic;
    });
    
    echo "📊 RESULTADOS DEL ANÁLISIS:\n";
    echo "   Órdenes problemáticas encontradas: " . $problematicOrders->count() . "\n\n";
    
    if ($problematicOrders->isEmpty()) {
        echo "✅ No se encontraron órdenes problemáticas.\n";
        echo "   Todas las órdenes tienen el proveedor correcto asignado.\n\n";
        DB::rollBack();
        exit(0);
    }
    
    // === PASO 2: Mostrar detalles de órdenes problemáticas ===
    echo "=== PASO 2: Detalles de órdenes problemáticas ===\n";
    
    $totalAmount = 0;
    $ordersByStatus = [];
    
    foreach ($problematicOrders as $order) {
        $request = $order->purchaseRequest;
        $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
        
        echo "📄 ORDEN: {$order->order_number}\n";
        echo "   └── Solicitud: {$request->request_number}\n";
        echo "   └── Solicitante: {$request->requester}\n";
        echo "   └── Área: {$request->section_area}\n";
        echo "   └── Proveedor actual: {$order->provider->nombre}\n";
        echo "   └── Proveedor correcto: {$quotation->provider_name}\n";
        echo "   └── Estado: {$order->status}\n";
        echo "   └── Monto: $" . number_format($order->total_amount, 2) . "\n";
        echo "   └── Creada: {$order->created_at->format('d/m/Y H:i')}\n\n";
        
        $totalAmount += $order->total_amount;
        $ordersByStatus[$order->status] = ($ordersByStatus[$order->status] ?? 0) + 1;
    }
    
    echo "📈 RESUMEN:\n";
    echo "   Total de monto afectado: $" . number_format($totalAmount, 2) . "\n";
    echo "   Órdenes por estado:\n";
    foreach ($ordersByStatus as $status => $count) {
        echo "     - {$status}: {$count} orden(es)\n";
    }
    echo "\n";
    
    // === PASO 3: Crear backup si está habilitado ===
    if ($BACKUP_ENABLED && !$DRY_RUN) {
        echo "=== PASO 3: Creando backup de seguridad ===\n";
        
        $backupTable = 'purchase_orders_backup_' . date('Y_m_d_H_i_s');
        $orderIds = $problematicOrders->pluck('id')->join(',');
        
        DB::statement("CREATE TABLE `{$backupTable}` AS SELECT * FROM `purchase_orders` WHERE id IN ({$orderIds})");
        
        echo "✅ Backup creado en tabla: {$backupTable}\n";
        echo "   Órdenes respaldadas: " . $problematicOrders->count() . "\n\n";
    } elseif ($BACKUP_ENABLED && $DRY_RUN) {
        echo "=== PASO 3: Backup (simulado) ===\n";
        echo "   En ejecución real se crearía backup en: purchase_orders_backup_" . date('Y_m_d_H_i_s') . "\n\n";
    }
    
    // === PASO 4: Procesar correcciones ===
    echo "=== PASO 4: Aplicando correcciones ===\n";
    
    $processedCount = 0;
    $errorCount = 0;
    $providersCreated = [];
    
    foreach ($problematicOrders as $order) {
        try {
            $request = $order->purchaseRequest;
            $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
            
            echo "🔧 Procesando orden {$order->order_number}...\n";
            
            // Buscar o crear el proveedor correcto
            $correctProvider = Proveedor::where('nombre', $quotation->provider_name)->first();
            
            if (!$correctProvider) {
                echo "   📝 Creando proveedor: {$quotation->provider_name}\n";
                
                if (!$DRY_RUN) {
                    $correctProvider = Proveedor::create([
                        'nombre' => $quotation->provider_name,
                        'nit' => 'Por definir',
                        'email' => 'pendiente@proveedor.com',
                        'telefono' => 'Por definir',
                        'direccion' => 'Por definir',
                        'persona_contacto' => 'Por definir',
                        'ciudad' => 'Por definir',
                        'servicio_producto' => 'Productos/Servicios',
                        'segmento_mercado' => 'Otro',
                        'alto_riesgo' => false,
                        'proveedor_critico' => false,
                        // Puntajes por defecto
                        'puntaje_forma_pago' => 60,
                        'puntaje_referencias' => 60,
                        'puntaje_descuento' => 0,
                        'puntaje_cobertura' => 50,
                        'puntaje_valores_agregados' => 0,
                        'puntaje_precios' => 50,
                        'puntaje_criterios_tecnicos' => 60
                    ]);
                    
                    $providersCreated[] = $correctProvider->nombre;
                } else {
                    echo "   (Simulado) Proveedor a crear: {$quotation->provider_name}\n";
                }
            } else {
                echo "   ✅ Proveedor encontrado: {$correctProvider->nombre}\n";
            }
            
            if (!$DRY_RUN) {
                // Registrar información antes del cambio
                $oldProviderId = $order->provider_id;
                $oldProviderName = $order->provider->nombre;
                
                // Actualizar la orden con el proveedor correcto
                $order->update([
                    'provider_id' => $correctProvider->id
                ]);
                
                echo "   ✅ Proveedor actualizado: {$oldProviderName} → {$correctProvider->nombre}\n";
                
                // Regenerar PDF con el proveedor correcto
                try {
                    if (class_exists('App\\Services\\PurchaseOrderPdfService')) {
                        $pdfService = app(PurchaseOrderPdfService::class);
                        $newPdfPath = $pdfService->generatePdf($order);
                        
                        if ($newPdfPath) {
                            $order->update(['file_path' => $newPdfPath]);
                            echo "   ✅ PDF regenerado exitosamente\n";
                        } else {
                            echo "   ⚠️  Advertencia: No se pudo regenerar PDF\n";
                        }
                    } else {
                        echo "   ⚠️  Servicio PDF no disponible\n";
                    }
                } catch (\Exception $e) {
                    echo "   ⚠️  Error generando PDF: " . $e->getMessage() . "\n";
                }
                
                // Registrar en log del sistema
                Log::info('Orden de compra corregida por script de regeneración', [
                    'script' => 'regenerate_purchase_orders_production.php',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'purchase_request_id' => $request->id,
                    'old_provider_id' => $oldProviderId,
                    'new_provider_id' => $correctProvider->id,
                    'old_provider_name' => $oldProviderName,
                    'new_provider_name' => $correctProvider->nombre,
                    'quotation_provider' => $quotation->provider_name,
                    'corrected_at' => now()->toDateTimeString()
                ]);
                
                echo "   ✅ Cambios registrados en log del sistema\n";
            } else {
                echo "   (Simulado) Se actualizaría proveedor y regeneraría PDF\n";
            }
            
            echo "   ✅ Orden corregida exitosamente\n\n";
            $processedCount++;
            
        } catch (\Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n\n";
            $errorCount++;
            
            Log::error('Error corrigiendo orden de compra', [
                'script' => 'regenerate_purchase_orders_production.php',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // === RESUMEN FINAL ===
    echo "=== RESUMEN FINAL ===\n";
    echo "📊 Estadísticas de ejecución:\n";
    echo "   ✅ Órdenes procesadas exitosamente: {$processedCount}\n";
    echo "   ❌ Órdenes con errores: {$errorCount}\n";
    echo "   📄 Total de órdenes analizadas: " . $problematicOrders->count() . "\n";
    
    if (!empty($providersCreated) && !$DRY_RUN) {
        echo "   👥 Proveedores creados:\n";
        foreach ($providersCreated as $providerName) {
            echo "     - {$providerName}\n";
        }
    }
    
    echo "\n";
    
    if ($DRY_RUN) {
        echo "🔒 MODO SIMULACIÓN - No se realizaron cambios reales\n";
        echo "   Para ejecutar realmente, cambiar \$DRY_RUN = false en línea 20\n\n";
        DB::rollBack();
    } else {
        if ($errorCount === 0) {
            echo "✅ EJECUCIÓN COMPLETADA EXITOSAMENTE\n";
            echo "   Todos los cambios se aplicaron correctamente\n\n";
            DB::commit();
        } else {
            echo "⚠️  EJECUCIÓN COMPLETADA CON ERRORES\n";
            echo "   Algunos cambios no se pudieron aplicar\n";
            echo "   Revise los logs del sistema para más detalles\n\n";
            DB::commit();
        }
    }
    
    // Recomendaciones finales
    echo "📋 RECOMENDACIONES POST-EJECUCIÓN:\n";
    echo "1. Verificar que las órdenes corregidas tengan el proveedor correcto\n";
    echo "2. Revisar que los PDFs se hayan regenerado correctamente\n";
    echo "3. Informar a contabilidad sobre las correcciones realizadas\n";
    echo "4. Verificar que no haya órdenes duplicadas o inconsistencias\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n\n";
    
    Log::error('Error crítico en script de regeneración de órdenes', [
        'script' => 'regenerate_purchase_orders_production.php',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    exit(1);
}

echo "=== SCRIPT FINALIZADO ===\n";
echo "Tiempo de ejecución: " . date('Y-m-d H:i:s') . "\n";
