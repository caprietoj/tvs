<?php
/**
 * Script para regenerar órdenes de compra con proveedor correcto
 * 
 * Este script soluciona el problema de órdenes generadas con proveedor "truncado"
 * (usando el primer proveedor de la base de datos en lugar del proveedor de la cotización)
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

echo "=== SCRIPT PARA REGENERAR ÓRDENES DE COMPRA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuraciones
$DRY_RUN = true; // Cambiar a false para ejecutar realmente
$BACKUP_ENABLED = true; // Hacer backup de órdenes originales

echo "Modo: " . ($DRY_RUN ? "SIMULACIÓN (DRY RUN)" : "EJECUCIÓN REAL") . "\n";
echo "Backup: " . ($BACKUP_ENABLED ? "HABILITADO" : "DESHABILITADO") . "\n\n";

try {
    DB::beginTransaction();
    
    // 1. Identificar órdenes problemáticas
    echo "=== PASO 1: Identificando órdenes problemáticas ===\n";
    
    $problematicOrders = PurchaseOrder::with(['purchaseRequest.selectedQuotation', 'purchaseRequest.preApprovedQuotation', 'provider'])
        ->whereHas('purchaseRequest', function($query) {
            $query->where('type', 'purchase')
                  ->whereNotNull('selected_quotation_id');
        })
        ->get()
        ->filter(function($order) {
            $request = $order->purchaseRequest;
            $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
            
            if (!$quotation) {
                return false;
            }
            
            // Verificar si el proveedor de la orden NO coincide con el de la cotización
            $quotationProviderName = trim(strtolower($quotation->provider_name));
            $orderProviderName = trim(strtolower($order->provider->nombre ?? ''));
            
            return $quotationProviderName !== $orderProviderName;
        });
    
    echo "Órdenes problemáticas encontradas: " . $problematicOrders->count() . "\n\n";
    
    if ($problematicOrders->isEmpty()) {
        echo "No se encontraron órdenes problemáticas. Terminando...\n";
        DB::rollBack();
        return;
    }
    
    // 2. Mostrar detalles de órdenes problemáticas
    echo "=== DETALLES DE ÓRDENES PROBLEMÁTICAS ===\n";
    foreach ($problematicOrders as $order) {
        $request = $order->purchaseRequest;
        $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
        
        echo "Orden #{$order->order_number}:\n";
        echo "  - Solicitud: {$request->request_number}\n";
        echo "  - Proveedor actual: {$order->provider->nombre}\n";
        echo "  - Proveedor correcto: {$quotation->provider_name}\n";
        echo "  - Estado: {$order->status}\n";
        echo "  - Monto: $" . number_format($order->total_amount, 2) . "\n\n";
    }
    
    // 3. Crear backup si está habilitado
    if ($BACKUP_ENABLED && !$DRY_RUN) {
        echo "=== PASO 2: Creando backup ===\n";
        $backupTable = 'purchase_orders_backup_' . date('Y_m_d_H_i_s');
        
        DB::statement("CREATE TABLE `{$backupTable}` AS SELECT * FROM `purchase_orders` WHERE id IN (" . 
                     $problematicOrders->pluck('id')->join(',') . ")");
        
        echo "Backup creado en tabla: {$backupTable}\n\n";
    }
    
    // 4. Procesar cada orden problemática
    echo "=== PASO 3: Regenerando órdenes ===\n";
    
    $processed = 0;
    $errors = 0;
    
    foreach ($problematicOrders as $order) {
        try {
            echo "Procesando orden {$order->order_number}...\n";
            
            $request = $order->purchaseRequest;
            $quotation = $request->selectedQuotation ?? $request->preApprovedQuotation;
            
            // Buscar o crear el proveedor correcto
            $correctProvider = Proveedor::where('nombre', $quotation->provider_name)->first();
            
            if (!$correctProvider) {
                echo "  - Creando proveedor: {$quotation->provider_name}\n";
                
                if (!$DRY_RUN) {
                    $correctProvider = Proveedor::create([
                        'nombre' => $quotation->provider_name,
                        'nit' => $quotation->provider_nit ?? 'Por definir',
                        'email' => $quotation->provider_email ?? 'pendiente@proveedor.com',
                        'telefono' => $quotation->provider_contact ?? 'Por definir',
                        'direccion' => $quotation->provider_address ?? 'Por definir',
                        'persona_contacto' => $quotation->provider_contact ?? 'Por definir',
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
                }
            } else {
                echo "  - Proveedor encontrado: {$correctProvider->nombre}\n";
            }
            
            if (!$DRY_RUN) {
                // Actualizar la orden con el proveedor correcto
                $order->update([
                    'provider_id' => $correctProvider->id
                ]);
                
                // Regenerar PDF con el proveedor correcto
                try {
                    $pdfService = app(PurchaseOrderPdfService::class);
                    $newPdfPath = $pdfService->generatePdf($order);
                    
                    if ($newPdfPath) {
                        $order->update(['file_path' => $newPdfPath]);
                        echo "  - PDF regenerado: {$newPdfPath}\n";
                    } else {
                        echo "  - ADVERTENCIA: No se pudo regenerar PDF\n";
                    }
                } catch (\Exception $e) {
                    echo "  - ERROR regenerando PDF: " . $e->getMessage() . "\n";
                }
                
                // Log de la corrección
                Log::info('Orden de compra corregida', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_provider_id' => $order->getOriginal('provider_id'),
                    'new_provider_id' => $correctProvider->id,
                    'old_provider_name' => $order->provider->nombre,
                    'new_provider_name' => $correctProvider->nombre,
                    'script' => 'regenerate_purchase_orders.php'
                ]);
            }
            
            echo "  - ✓ Orden corregida exitosamente\n\n";
            $processed++;
            
        } catch (\Exception $e) {
            echo "  - ✗ ERROR: " . $e->getMessage() . "\n\n";
            $errors++;
        }
    }
    
    // 5. Resumen final
    echo "=== RESUMEN FINAL ===\n";
    echo "Órdenes procesadas: {$processed}\n";
    echo "Errores: {$errors}\n";
    echo "Total encontradas: " . $problematicOrders->count() . "\n\n";
    
    if ($DRY_RUN) {
        echo "MODO SIMULACIÓN - No se realizaron cambios reales\n";
        echo "Para ejecutar realmente, cambiar \$DRY_RUN = false en el script\n";
        DB::rollBack();
    } else {
        echo "Cambios aplicados exitosamente\n";
        DB::commit();
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR GENERAL: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== SCRIPT FINALIZADO ===\n";
