<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Illuminate\Support\Facades\Log;

class AuditPurchaseOrderTaxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:audit-taxes {--fix : Attempt to fix problematic orders} {--order-id= : Audit specific order ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit purchase orders for incorrect tax calculations based on quotations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fix = $this->option('fix');
        $orderId = $this->option('order-id');
        
        $this->info('🔍 Auditando órdenes de compra para verificar cálculos de impuestos...');
        
        // Query base para órdenes de compra
        $ordersQuery = PurchaseOrder::with(['purchaseRequest.selectedQuotation', 'provider']);
        
        if ($orderId) {
            $ordersQuery->where('id', $orderId);
        } else {
            // Solo órdenes activas de los últimos 6 meses para no sobrecargar
            $ordersQuery->whereNull('deleted_at')
                       ->where('created_at', '>=', now()->subMonths(6));
        }
        
        $orders = $ordersQuery->get();
        
        if ($orders->isEmpty()) {
            $this->info('✅ No se encontraron órdenes para auditar.');
            return 0;
        }
        
        $this->info("📋 Auditando {$orders->count()} órdenes de compra...");
        
        $problematicOrders = [];
        $correctOrders = 0;
        
        foreach ($orders as $order) {
            $issues = $this->auditOrder($order);
            
            if (!empty($issues)) {
                $problematicOrders[] = [
                    'order' => $order,
                    'issues' => $issues
                ];
            } else {
                $correctOrders++;
            }
        }
        
        // Mostrar resultados
        $this->line("");
        $this->info("📊 RESULTADOS DE LA AUDITORÍA:");
        $this->line("   ✅ Órdenes correctas: {$correctOrders}");
        $this->line("   ❌ Órdenes con problemas: " . count($problematicOrders));
        
        if (!empty($problematicOrders)) {
            $this->line("");
            $this->warn("🔴 ÓRDENES CON PROBLEMAS:");
            
            foreach ($problematicOrders as $problematic) {
                $order = $problematic['order'];
                $issues = $problematic['issues'];
                
                $this->line("");
                $this->error("Orden #{$order->order_number} (ID: {$order->id})");
                $this->line("   Proveedor: {$order->provider->nombre}");
                $this->line("   Total: \${$order->total_amount}");
                $this->line("   IVA en orden: \${$order->iva_amount}");
                
                foreach ($issues as $issue) {
                    $this->line("   🔸 {$issue}");
                }
                
                if ($fix) {
                    $this->line("   🔧 Intentando corregir...");
                    $this->fixOrder($order);
                }
            }
            
            if (!$fix) {
                $this->line("");
                $this->info("💡 Para intentar corregir automáticamente las órdenes problemáticas, ejecute:");
                $this->line("   php artisan orders:audit-taxes --fix");
            }
        } else {
            $this->info("🎉 ¡Todas las órdenes auditadas están correctas!");
        }
        
        return 0;
    }
    
    /**
     * Auditar una orden específica
     */
    private function auditOrder($order)
    {
        $issues = [];
        
        // Verificar si tiene cotización asociada
        $quotation = $order->purchaseRequest->selectedQuotation ?? null;
        
        if (!$quotation) {
            // Verificar si es servicio sin cotización
            if ($order->purchaseRequest->type === 'services' && 
                $order->purchaseRequest->service_type === 'no_quotation') {
                return []; // Los servicios sin cotización son válidos
            }
            
            $issues[] = "No tiene cotización asociada";
            return $issues;
        }
        
        // Detectar impuestos que deberían aplicarse según la cotización
        $expectedTaxes = $this->getExpectedTaxes($quotation);
        
        // Verificar coherencia entre cotización y orden
        $orderHasTaxes = $order->iva_amount > 0 || $order->tax_amount_19 > 0 || 
                        $order->tax_amount_5 > 0 || $order->tax_amount_8 > 0 || 
                        $order->tax_amount_4 > 0;
        
        $quotationHasTaxes = !empty($expectedTaxes);
        
        if ($orderHasTaxes && !$quotationHasTaxes) {
            $issues[] = "La orden tiene IVA (\${$order->iva_amount}) pero la cotización no tiene impuestos configurados";
        }
        
        if (!$orderHasTaxes && $quotationHasTaxes) {
            $issues[] = "La cotización tiene impuestos configurados (" . implode(', ', $expectedTaxes) . ") pero la orden no los incluye";
        }
        
        // Verificar montos específicos si ambos tienen impuestos
        if ($orderHasTaxes && $quotationHasTaxes) {
            $expectedIvaAmount = $this->calculateExpectedTaxAmount($quotation, $expectedTaxes);
            
            // Usar solo los campos específicos de impuestos, no sumar iva_amount
            $actualIvaAmount = $order->tax_amount_19 + $order->tax_amount_5 + 
                             $order->tax_amount_8 + $order->tax_amount_4;
            
            // Si los campos específicos están vacíos, usar iva_amount como fallback
            if ($actualIvaAmount == 0) {
                $actualIvaAmount = $order->iva_amount ?? 0;
            }
            
            $difference = abs($expectedIvaAmount - $actualIvaAmount);
            
            if ($difference > 1.0) { // Tolerancia de $1 por redondeos
                $issues[] = "Monto de impuestos incorrecto. Esperado: \${$expectedIvaAmount}, Actual: \${$actualIvaAmount}";
            }
        }
        
        // Verificar coherencia de subtotal
        if ($quotation->subtotal && $quotation->subtotal > 0) {
            $expectedSubtotal = $quotation->subtotal;
            $actualSubtotal = $order->subtotal ?? ($order->total_amount - ($order->iva_amount ?? 0));
            
            $subtotalDifference = abs($expectedSubtotal - $actualSubtotal);
            
            if ($subtotalDifference > 1.0) {
                $issues[] = "Subtotal incorrecto. Esperado: \${$expectedSubtotal}, Actual: \${$actualSubtotal}";
            }
        }
        
        return $issues;
    }
    
    /**
     * Obtener impuestos esperados de una cotización
     */
    private function getExpectedTaxes($quotation)
    {
        $expectedTaxes = [];
        
        if ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) {
            $expectedTaxes[] = 'iva_19';
        }
        
        if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
            $expectedTaxes[] = 'iva_5';
        }
        
        if ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) {
            $expectedTaxes[] = 'consumo_8';
        }
        
        if ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0) {
            $expectedTaxes[] = 'consumo_4';
        }
        
        // Fallback para cotizaciones legacy
        if (empty($expectedTaxes) && $quotation->includes_iva && $quotation->iva_amount > 0) {
            $subtotal = $quotation->subtotal ?? ($quotation->total_amount - $quotation->iva_amount);
            if ($subtotal > 0) {
                $percentage = ($quotation->iva_amount / $subtotal) * 100;
                if ($percentage >= 18 && $percentage <= 20) {
                    $expectedTaxes[] = 'iva_19_legacy';
                } elseif ($percentage >= 4 && $percentage <= 6) {
                    $expectedTaxes[] = 'iva_5_legacy';
                }
            }
        }
        
        return $expectedTaxes;
    }
    
    /**
     * Calcular monto esperado de impuestos
     */
    private function calculateExpectedTaxAmount($quotation, $expectedTaxes)
    {
        $totalTaxAmount = 0;
        
        foreach ($expectedTaxes as $tax) {
            switch ($tax) {
                case 'iva_19':
                    $totalTaxAmount += $quotation->iva_19_amount;
                    break;
                case 'iva_5':
                    $totalTaxAmount += $quotation->iva_5_amount;
                    break;
                case 'consumo_8':
                    $totalTaxAmount += $quotation->ipoconsumo_8_amount;
                    break;
                case 'consumo_4':
                    $totalTaxAmount += $quotation->ipoconsumo_4_amount;
                    break;
                case 'iva_19_legacy':
                case 'iva_5_legacy':
                    $totalTaxAmount += $quotation->iva_amount;
                    break;
            }
        }
        
        return $totalTaxAmount;
    }
    
    /**
     * Intentar corregir una orden problemática
     */
    private function fixOrder($order)
    {
        try {
            $quotation = $order->purchaseRequest->selectedQuotation;
            
            if (!$quotation) {
                $this->line("     ❌ No se puede corregir: sin cotización asociada");
                return;
            }
            
            $expectedTaxes = $this->getExpectedTaxes($quotation);
            
            // Calcular valores correctos
            if (!empty($expectedTaxes)) {
                $subtotal = $quotation->subtotal ?? 0;
                $taxAmount = $this->calculateExpectedTaxAmount($quotation, $expectedTaxes);
                
                if ($subtotal <= 0) {
                    $subtotal = $quotation->total_amount - $taxAmount;
                }
                
                $totalAmount = $subtotal + $taxAmount;
            } else {
                $subtotal = $quotation->total_amount;
                $taxAmount = 0;
                $totalAmount = $subtotal;
            }
            
            // Actualizar la orden - resetear todos los campos de impuestos primero
            $updateData = [
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'includes_iva' => $taxAmount > 0,
                'applied_taxes' => $expectedTaxes,
                // Resetear todos los campos de impuestos
                'iva_amount' => 0,
                'tax_amount_19' => 0,
                'tax_amount_5' => 0,
                'tax_amount_8' => 0,
                'tax_amount_4' => 0
            ];
            
            // Asignar impuestos a los campos específicos según el tipo
            foreach ($expectedTaxes as $tax) {
                switch ($tax) {
                    case 'iva_19':
                        $updateData['tax_amount_19'] = $quotation->iva_19_amount;
                        $updateData['iva_amount'] += $quotation->iva_19_amount;
                        break;
                    case 'iva_5':
                        $updateData['tax_amount_5'] = $quotation->iva_5_amount;
                        $updateData['iva_amount'] += $quotation->iva_5_amount;
                        break;
                    case 'consumo_8':
                        $updateData['tax_amount_8'] = $quotation->ipoconsumo_8_amount;
                        $updateData['iva_amount'] += $quotation->ipoconsumo_8_amount;
                        break;
                    case 'consumo_4':
                        $updateData['tax_amount_4'] = $quotation->ipoconsumo_4_amount;
                        $updateData['iva_amount'] += $quotation->ipoconsumo_4_amount;
                        break;
                    case 'iva_19_legacy':
                    case 'iva_5_legacy':
                        $updateData['iva_amount'] = $quotation->iva_amount;
                        // Para legacy, asignar al campo apropiado según el porcentaje
                        $subtotalCalc = $quotation->subtotal ?? ($quotation->total_amount - $quotation->iva_amount);
                        if ($subtotalCalc > 0) {
                            $percentage = ($quotation->iva_amount / $subtotalCalc) * 100;
                            if ($percentage >= 18 && $percentage <= 20) {
                                $updateData['tax_amount_19'] = $quotation->iva_amount;
                            } elseif ($percentage >= 4 && $percentage <= 6) {
                                $updateData['tax_amount_5'] = $quotation->iva_amount;
                            }
                        }
                        break;
                }
            }
            
            $order->update($updateData);
            
            $this->line("     ✅ Orden corregida exitosamente");
            $this->line("        Nuevo subtotal: \${$updateData['subtotal']}");
            $this->line("        Nuevo IVA: \${$updateData['iva_amount']}");
            $this->line("        Nuevo total: \${$updateData['total_amount']}");
            
            Log::info('Orden de compra corregida por auditoría', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_total' => $order->getOriginal('total_amount'),
                'new_total' => $updateData['total_amount'],
                'old_iva' => $order->getOriginal('iva_amount'),
                'new_iva' => $updateData['iva_amount'],
                'expected_taxes' => $expectedTaxes
            ]);
            
        } catch (\Exception $e) {
            $this->line("     ❌ Error al corregir: " . $e->getMessage());
        }
    }
}