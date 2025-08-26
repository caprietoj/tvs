<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VerifyTaxConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:verify-tax-consistency 
                            {--order-id= : ID específico de la orden a verificar}
                            {--order-number= : Número específico de la orden a verificar}
                            {--all : Verificar todas las órdenes}
                            {--fix : Corregir las inconsistencias detectadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la consistencia en los cálculos de impuestos de órdenes de compra';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando verificación de consistencia de impuestos en órdenes de compra...');

        // Determinar qué órdenes procesar
        if ($orderId = $this->option('order-id')) {
            $orders = PurchaseOrder::where('id', $orderId)->get();
            $this->info("Verificando orden con ID: $orderId");
        } elseif ($orderNumber = $this->option('order-number')) {
            $orders = PurchaseOrder::where('order_number', $orderNumber)->get();
            $this->info("Verificando orden con número: $orderNumber");
        } elseif ($this->option('all')) {
            $orders = PurchaseOrder::all();
            $this->info("Verificando todas las órdenes: " . $orders->count() . " encontradas");
        } else {
            $this->error('Debe especificar --all, --order-id o --order-number');
            return 1;
        }

        if ($orders->isEmpty()) {
            $this->error('No se encontraron órdenes que coincidan con los criterios especificados.');
            return 1;
        }

        $fixMode = $this->option('fix');
        if ($fixMode) {
            $this->warn('MODO CORRECCIÓN ACTIVADO: Se actualizarán las inconsistencias detectadas.');
            if (!$this->confirm('¿Estás seguro de continuar?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $inconsistentOrders = [];
        $fixedOrders = 0;
        $correctOrders = 0;
        $customDataOrders = 0;

        foreach ($orders as $order) {
            $this->info("Analizando orden #{$order->id} ({$order->order_number}):");
            
            // Verificar si tiene datos personalizados
            $hasCustomData = !empty($order->pdf_custom_data);
            if ($hasCustomData) {
                $customDataOrders++;
                $customData = json_decode($order->pdf_custom_data, true);
                
                $subtotal = $customData['subtotal'] ?? $order->subtotal;
                $ivaAmount = $customData['iva_amount'] ?? $order->iva_amount;
                $total = $customData['total'] ?? $order->total_amount;
                
                $this->line("  - Datos personalizados: Subtotal = " . number_format($subtotal, 2) . 
                            ", IVA = " . number_format($ivaAmount, 2) . 
                            ", Total = " . number_format($total, 2));
            } else {
                $subtotal = $order->subtotal;
                $ivaAmount = $order->iva_amount;
                $total = $order->total_amount;
                
                $this->line("  - Datos de BD: Subtotal = " . number_format($subtotal, 2) . 
                            ", IVA = " . number_format($ivaAmount, 2) . 
                            ", Total = " . number_format($total, 2));
            }
            
            // Calcular el total correcto
            $calculatedTotal = $subtotal + $ivaAmount;
            
            // Verificar consistencia
            $isConsistent = abs($calculatedTotal - $total) < 0.01;
            
            if (!$isConsistent) {
                $this->warn("  ❌ INCONSISTENCIA DETECTADA: Total calculado ($calculatedTotal) ≠ Total guardado ($total)");
                $inconsistentOrders[] = $order->id;
                
                if ($fixMode) {
                    // Actualizar el total para que sea consistente
                    if ($hasCustomData) {
                        $customData['total'] = $calculatedTotal;
                        $order->pdf_custom_data = json_encode($customData);
                        $order->total_amount = $calculatedTotal;
                    } else {
                        $order->total_amount = $calculatedTotal;
                    }
                    
                    $order->save();
                    $this->info("  ✅ CORREGIDO: Total actualizado a " . number_format($calculatedTotal, 2));
                    $fixedOrders++;
                }
            } else {
                $this->info("  ✅ CORRECTO: Los valores son consistentes.");
                $correctOrders++;
            }
        }

        $this->newLine();
        $this->info("=== RESUMEN DE VERIFICACIÓN ===");
        $this->info("Total de órdenes verificadas: " . $orders->count());
        $this->info("Órdenes con datos personalizados: $customDataOrders");
        $this->info("Órdenes correctas: $correctOrders");
        $this->info("Órdenes con inconsistencias: " . count($inconsistentOrders));
        
        if ($fixMode) {
            $this->info("Órdenes corregidas: $fixedOrders");
        } elseif (count($inconsistentOrders) > 0) {
            $this->info("Para corregir estas inconsistencias, ejecute el comando con la opción --fix");
            $this->line("IDs de órdenes con inconsistencias: " . implode(', ', $inconsistentOrders));
        }

        return 0;
    }
}
