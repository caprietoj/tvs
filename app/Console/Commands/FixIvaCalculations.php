<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;

class FixIvaCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:fix-iva {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica y corrige los cálculos del IVA en las órdenes de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== VERIFICACIÓN DE CÁLCULOS DE IVA EN ÓRDENES DE COMPRA ===');
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('MODO DRY-RUN: Solo se mostrarán los cambios, no se aplicarán.');
            $this->newLine();
        }

        // Obtener todas las órdenes de compra
        $orders = PurchaseOrder::with(['purchaseRequest', 'provider'])->get();

        $totalOrders = $orders->count();
        $ordersWithErrors = 0;
        $ordersFixed = 0;

        $this->info("Total de órdenes encontradas: {$totalOrders}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($totalOrders);
        $progressBar->start();

        foreach ($orders as $order) {
            // Verificar si los cálculos son correctos
            $expectedSubtotal = round($order->total_amount / 1.19, 2);
            $expectedIva = round($order->total_amount - $expectedSubtotal, 2);
            
            // Verificar si hay discrepancias
            $subtotalDiff = abs(($order->subtotal ?? 0) - $expectedSubtotal);
            $ivaDiff = abs(($order->iva_amount ?? 0) - $expectedIva);
            
            if ($subtotalDiff > 0.01 || $ivaDiff > 0.01) {
                $ordersWithErrors++;
                
                $this->newLine();
                $this->error("❌ ERROR en Orden #{$order->id} - {$order->order_number}");
                $this->line("  Proveedor: " . ($order->provider->nombre ?? 'N/A'));
                $this->line("  Total: $" . number_format($order->total_amount, 2));
                $this->line("  Subtotal actual: $" . number_format($order->subtotal ?? 0, 2) . " → Esperado: $" . number_format($expectedSubtotal, 2));
                $this->line("  IVA actual: $" . number_format($order->iva_amount ?? 0, 2) . " → Esperado: $" . number_format($expectedIva, 2));
                
                if (!$dryRun) {
                    try {
                        $order->update([
                            'subtotal' => $expectedSubtotal,
                            'iva_amount' => $expectedIva,
                            'includes_iva' => true
                        ]);
                        
                        $this->info("  ✅ Orden corregida exitosamente");
                        $ordersFixed++;
                    } catch (\Exception $e) {
                        $this->error("  ❌ Error al corregir: " . $e->getMessage());
                    }
                } else {
                    $this->info("  🔧 Se corregiría en modo real");
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('=== RESUMEN ===');
        $this->line("Total de órdenes verificadas: {$totalOrders}");
        $this->line("Órdenes con errores encontradas: {$ordersWithErrors}");
        
        if (!$dryRun) {
            $this->line("Órdenes corregidas: {$ordersFixed}");
        }

        if ($ordersWithErrors == 0) {
            $this->info("🎉 ¡Todos los cálculos de IVA están correctos!");
        } else {
            if ($dryRun) {
                $this->warn("⚠️  Se encontraron {$ordersWithErrors} órdenes con cálculos incorrectos.");
                $this->info("Ejecuta el comando sin --dry-run para corregirlas.");
            } else {
                $this->info("⚠️  Se corrigieron {$ordersFixed} de {$ordersWithErrors} órdenes con cálculos incorrectos.");
            }
        }

        $this->newLine();
        $this->info('=== EJEMPLO DE CÁLCULO CORRECTO ===');
        $this->line('- Total con IVA: $119,000');
        $this->line('- Subtotal (sin IVA): $119,000 ÷ 1.19 = $100,000');
        $this->line('- IVA (19%): $119,000 - $100,000 = $19,000');
        $this->line('- Verificación: $100,000 + $19,000 = $119,000 ✅');

        return Command::SUCCESS;
    }
}