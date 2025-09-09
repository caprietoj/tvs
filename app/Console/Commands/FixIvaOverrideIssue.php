<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class FixIvaOverrideIssue extends Command
{
    protected $signature = 'fix:iva-override {order_number?}';
    protected $description = 'Corrige órdenes que tienen IVA agregado automáticamente cuando la cotización no tenía IVA';

    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        
        if ($orderNumber) {
            // Corregir una orden específica
            $order = PurchaseOrder::where('order_number', $orderNumber)->first();
            if (!$order) {
                $this->error("Orden {$orderNumber} no encontrada");
                return 1;
            }
            $this->fixOrder($order);
        } else {
            // Buscar todas las órdenes con problemas
            $this->info('Buscando órdenes con problemas de IVA automático...');
            $problematicOrders = $this->findProblematicOrders();
            
            if ($problematicOrders->isEmpty()) {
                $this->info('No se encontraron órdenes con problemas de IVA automático');
                return 0;
            }
            
            $this->info("Se encontraron {$problematicOrders->count()} órdenes con problemas:");
            foreach ($problematicOrders as $order) {
                $this->line("- {$order->order_number}");
            }
            
            if ($this->confirm('¿Desea corregir todas estas órdenes?')) {
                foreach ($problematicOrders as $order) {
                    $this->fixOrder($order);
                }
            }
        }
        
        return 0;
    }
    
    private function findProblematicOrders()
    {
        // Buscar órdenes que:
        // 1. Tienen IVA > 0 en la orden
        // 2. Pero la cotización asociada NO tiene IVA específico habilitado
        
        return PurchaseOrder::whereHas('purchaseRequest.selectedQuotation', function($query) {
            $query->where(function($q) {
                // Cotización tiene includes_iva = true PERO no tiene impuestos específicos
                $q->where('includes_iva', true)
                  ->where(function($subq) {
                      $subq->where('includes_iva_19', false)
                           ->orWhereNull('includes_iva_19');
                  })
                  ->where(function($subq) {
                      $subq->where('includes_iva_5', false)
                           ->orWhereNull('includes_iva_5');
                  })
                  ->where(function($subq) {
                      $subq->where('iva_19_amount', '<=', 0)
                           ->orWhereNull('iva_19_amount');
                  })
                  ->where(function($subq) {
                      $subq->where('iva_5_amount', '<=', 0)
                           ->orWhereNull('iva_5_amount');
                  });
            });
        })
        ->where('iva_amount', '>', 0) // Pero la orden SÍ tiene IVA
        ->get();
    }
    
    private function fixOrder(PurchaseOrder $order)
    {
        $this->info("Corrigiendo orden {$order->order_number}...");
        
        $quotation = $order->purchaseRequest->selectedQuotation ?? null;
        if (!$quotation) {
            $this->warn("  - La orden no tiene cotización asociada");
            return;
        }
        
        // Verificar si realmente tiene el problema
        $hasIvaSpecific = ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) ||
                         ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0);
        
        if ($hasIvaSpecific) {
            $this->info("  - La cotización SÍ tiene IVA específico configurado. No necesita corrección.");
            return;
        }
        
        if ($order->iva_amount <= 0 && $order->tax_amount_19 <= 0 && $order->tax_amount_8 <= 0 && 
            $order->tax_amount_5 <= 0 && $order->tax_amount_4 <= 0) {
            $this->info("  - La orden no tiene IVA aplicado. No necesita corrección.");
            return;
        }
        
        $this->warn("  - PROBLEMA DETECTADO:");
        $this->warn("    · Cotización: includes_iva={$quotation->includes_iva}, iva_19_amount={$quotation->iva_19_amount}, iva_5_amount={$quotation->iva_5_amount}");
        $this->warn("    · Orden: iva_amount={$order->iva_amount}");
        
        // Crear backup de los datos actuales
        $originalData = [
            'iva_amount' => $order->iva_amount,
            'total_amount' => $order->total_amount,
            'includes_iva' => $order->includes_iva,
            'pdf_custom_data' => $order->pdf_custom_data
        ];
        
        // Calcular nuevo total sin IVA
        $newSubtotal = $order->subtotal ?? ($order->total_amount - $order->iva_amount);
        $newTotal = $newSubtotal; // Sin IVA
        
        // Actualizar la orden
        $order->iva_amount = 0;
        $order->tax_amount_19 = 0;
        $order->tax_amount_8 = 0;
        $order->tax_amount_5 = 0;
        $order->tax_amount_4 = 0;
        $order->total_amount = $newTotal;
        $order->includes_iva = false;
        
        // Actualizar customData si existe
        if ($order->pdf_custom_data) {
            $customData = json_decode($order->pdf_custom_data, true);
            if (is_array($customData)) {
                $customData['iva_amount'] = 0;
                $customData['iva_rate'] = '0%';
                $customData['total'] = $newTotal;
                $customData['subtotal'] = $newSubtotal;
                $order->pdf_custom_data = json_encode($customData);
            }
        }
        
        $order->save();
        
        $this->info("  ✅ CORREGIDO:");
        $this->info("    · IVA: {$originalData['iva_amount']} → 0");
        $this->info("    · Total: {$originalData['total_amount']} → {$newTotal}");
        $this->info("    · Includes IVA: " . ($originalData['includes_iva'] ? 'true' : 'false') . " → false");
        
        Log::info("Orden {$order->order_number} corregida - IVA automático removido", [
            'original_data' => $originalData,
            'new_iva_amount' => 0,
            'new_total' => $newTotal
        ]);
    }
}
