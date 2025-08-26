<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class VerifyIvaCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:verify-iva {--fix : Corregir cálculos incorrectos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar y opcionalmente corregir los cálculos de IVA en las órdenes de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Verificación de Cálculos de IVA ===\n");
        
        $orders = PurchaseOrder::whereNull('deleted_at')->get();
        $this->info("Total de órdenes a verificar: {$orders->count()}\n");
        
        $incorrectCount = 0;
        $correctedCount = 0;
        
        foreach ($orders as $order) {
            $this->line("Verificando orden {$order->order_number}:");
            
            // Verificar cálculo actual
            $currentSubtotal = $order->subtotal;
            $currentIva = $order->iva_amount;
            $currentTotal = $order->total_amount;
            $includesIva = $order->includes_iva;
            
            $this->line("  Valores actuales:");
            $this->line("    Subtotal: $" . number_format($currentSubtotal, 2));
            $this->line("    IVA: $" . number_format($currentIva, 2));
            $this->line("    Total: $" . number_format($currentTotal, 2));
            $this->line("    Incluye IVA: " . ($includesIva ? 'Sí' : 'No'));
            
            // Calcular valores correctos
            if ($includesIva) {
                // Si incluye IVA, el total debe ser correcto y calculamos subtotal
                $correctSubtotal = round($currentTotal / 1.19, 2);
                $correctIva = round($currentTotal - $correctSubtotal, 2);
                $correctTotal = $currentTotal;
            } else {
                // Si no incluye IVA, el subtotal debe ser correcto
                $correctSubtotal = $currentSubtotal;
                $correctIva = round($correctSubtotal * 0.19, 2);
                $correctTotal = $correctSubtotal + $correctIva;
            }
            
            // Verificar si hay diferencias (tolerancia de 0.01)
            $subtotalDiff = abs($currentSubtotal - $correctSubtotal);
            $ivaDiff = abs($currentIva - $correctIva);
            $totalDiff = abs($currentTotal - $correctTotal);
            
            $hasErrors = $subtotalDiff > 0.01 || $ivaDiff > 0.01 || $totalDiff > 0.01;
            
            if ($hasErrors) {
                $incorrectCount++;
                $this->error("  ❌ CÁLCULO INCORRECTO");
                $this->line("  Valores correctos:");
                $this->line("    Subtotal: $" . number_format($correctSubtotal, 2) . " (diff: " . number_format($subtotalDiff, 2) . ")");
                $this->line("    IVA: $" . number_format($correctIva, 2) . " (diff: " . number_format($ivaDiff, 2) . ")");
                $this->line("    Total: $" . number_format($correctTotal, 2) . " (diff: " . number_format($totalDiff, 2) . ")");
                
                if ($this->option('fix')) {
                    $order->update([
                        'subtotal' => $correctSubtotal,
                        'iva_amount' => $correctIva,
                        'total_amount' => $correctTotal
                    ]);
                    $this->info("  ✅ CORREGIDO");
                    $correctedCount++;
                }
            } else {
                $this->info("  ✅ Cálculo correcto");
            }
            
            $this->line("");
        }
        
        $this->info("=== Resumen ===");
        $this->line("Total verificadas: {$orders->count()}");
        $this->line("Correctas: " . ($orders->count() - $incorrectCount));
        $this->error("Incorrectas: {$incorrectCount}");
        
        if ($this->option('fix')) {
            $this->info("Corregidas: {$correctedCount}");
        } else if ($incorrectCount > 0) {
            $this->warn("\nPara corregir automáticamente, ejecuta:");
            $this->warn("php artisan purchase-orders:verify-iva --fix");
        }
    }
}
