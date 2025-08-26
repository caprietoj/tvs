<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class DiagnoseMixedSelectionOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:diagnose-mixed-selection {order_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnosticar órdenes de selección mixta para verificar coherencia de productos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        
        if ($orderNumber) {
            $this->diagnoseSingleOrder($orderNumber);
        } else {
            $this->diagnoseAllMixedOrders();
        }
    }
    
    private function diagnoseSingleOrder($orderNumber)
    {
        $order = PurchaseOrder::where('order_number', $orderNumber)
            ->with(['purchaseRequest', 'provider'])
            ->first();
            
        if (!$order) {
            $this->error("Orden {$orderNumber} no encontrada");
            return;
        }
        
        $this->info("=== Diagnóstico de Orden {$orderNumber} ===");
        $this->line("Proveedor: {$order->provider->nombre}");
        $this->line("Solicitud: {$order->purchaseRequest->request_number}");
        $this->line("Total: $" . number_format($order->total_amount, 2));
        
        // Verificar si es selección mixta
        $mixedSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
        $isMixedSelection = $mixedSelections->count() > 0;
        
        $this->line("Es selección mixta: " . ($isMixedSelection ? 'Sí' : 'No'));
        
        if ($isMixedSelection) {
            $this->info("\nAnálisis de selecciones mixtas:");
            $this->line("Total de selecciones en la solicitud: {$mixedSelections->count()}");
            
            // Agrupar por proveedor
            $providerGroups = $mixedSelections->groupBy('quotation.provider_name');
            $this->line("Proveedores involucrados: {$providerGroups->count()}");
            
            foreach ($providerGroups as $providerName => $selections) {
                $this->line("  - {$providerName}: {$selections->count()} items");
            }
            
            // Obtener selecciones específicas para esta orden
            $providerSpecificSelections = $mixedSelections->filter(function ($selection) use ($order) {
                return $selection->quotation->provider_name === $order->provider->nombre;
            });
            
            $this->info("\nItems específicos para {$order->provider->nombre}:");
            $this->line("Total items para este proveedor: {$providerSpecificSelections->count()}");
            
            if ($providerSpecificSelections->count() > 0) {
                $this->table(
                    ['#', 'Descripción', 'Cantidad', 'Precio Unit.', 'Total'],
                    $providerSpecificSelections->map(function ($item, $index) {
                        return [
                            $index + 1,
                            $item->item_description,
                            $item->quantity,
                            '$' . number_format($item->unit_price, 0),
                            '$' . number_format($item->total_price, 0)
                        ];
                    })->toArray()
                );
                
                $calculatedTotal = $providerSpecificSelections->sum('total_price');
                $this->line("Total calculado: $" . number_format($calculatedTotal, 2));
                $this->line("Total en orden:  $" . number_format($order->total_amount, 2));
                $difference = abs($calculatedTotal - $order->total_amount);
                
                if ($difference > 0.01) {
                    $this->error("⚠️  DIFERENCIA DETECTADA: $" . number_format($difference, 2));
                } else {
                    $this->info("✅ Totales coinciden");
                }
            } else {
                $this->error("❌ No se encontraron items para este proveedor");
            }
        } else {
            $this->line("Orden tradicional (no selección mixta)");
        }
    }
    
    private function diagnoseAllMixedOrders()
    {
        $this->info("=== Diagnóstico de Todas las Órdenes de Selección Mixta ===\n");
        
        // Encontrar todas las órdenes de selección mixta
        $orders = PurchaseOrder::whereHas('purchaseRequest.quotationItemSelections')
            ->with(['purchaseRequest', 'provider'])
            ->whereNull('deleted_at')
            ->get();
            
        $this->info("Total de órdenes de selección mixta: {$orders->count()}\n");
        
        $problemOrders = 0;
        
        foreach ($orders as $order) {
            $mixedSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
            $providerSpecificSelections = $mixedSelections->filter(function ($selection) use ($order) {
                return $selection->quotation->provider_name === $order->provider->nombre;
            });
            
            $calculatedTotal = $providerSpecificSelections->sum('total_price');
            $difference = abs($calculatedTotal - $order->total_amount);
            $hasProblems = $difference > 0.01 || $providerSpecificSelections->count() == 0;
            
            if ($hasProblems) {
                $problemOrders++;
                $this->error("❌ {$order->order_number} - {$order->provider->nombre}");
                $this->line("   Items: {$providerSpecificSelections->count()} | Diferencia: $" . number_format($difference, 2));
            } else {
                $this->line("✅ {$order->order_number} - {$order->provider->nombre} ({$providerSpecificSelections->count()} items)");
            }
        }
        
        $this->info("\n=== Resumen ===");
        $this->line("Órdenes correctas: " . ($orders->count() - $problemOrders));
        $this->error("Órdenes con problemas: {$problemOrders}");
        
        if ($problemOrders > 0) {
            $this->warn("\nPara diagnosticar una orden específica:");
            $this->warn("php artisan purchase-orders:diagnose-mixed-selection ORD-XXXX");
        }
    }
}
