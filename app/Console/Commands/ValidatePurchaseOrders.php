<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class ValidatePurchaseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:validate
                            {--status=pending : Estado de órdenes a validar}
                            {--show-correct : Mostrar también las órdenes correctas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida que las órdenes de compra con selección mixta tengan los artículos correctos por proveedor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        $showCorrect = $this->option('show-correct');
        
        $this->info("🔍 Validando órdenes de compra con estado: {$status}");

        // Obtener órdenes con selección mixta
        $orders = PurchaseOrder::with([
            'purchaseRequest.quotationItemSelections.quotation',
            'provider'
        ])
        ->where('status', $status)
        ->whereHas('purchaseRequest.quotationItemSelections')
        ->get();

        $this->info("📊 Encontradas {$orders->count()} órdenes con selección mixta");

        if ($orders->isEmpty()) {
            $this->info('✅ No hay órdenes con selección mixta para validar');
            return 0;
        }

        $correct = 0;
        $incorrect = 0;
        $issues = [];

        foreach ($orders as $order) {
            $validation = $this->validateOrder($order);
            
            if ($validation['is_correct']) {
                $correct++;
                if ($showCorrect) {
                    $this->line("✅ #{$order->order_number} - {$order->provider->nombre} - CORRECTO");
                }
            } else {
                $incorrect++;
                $issues[] = $validation;
                $this->error("❌ #{$order->order_number} - {$order->provider->nombre} - PROBLEMAS DETECTADOS:");
                
                foreach ($validation['issues'] as $issue) {
                    $this->line("   • {$issue}");
                }
                
                if (!empty($validation['wrong_items'])) {
                    $this->warn("   📋 Artículos incorrectos:");
                    foreach ($validation['wrong_items'] as $item) {
                        $this->line("     - {$item['description']} (Proveedor: {$item['actual_provider']}, Esperado: {$order->provider->nombre})");
                    }
                }
                
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('📊 Resumen de validación:');
        $this->line("  ✅ Correctas: {$correct}");
        $this->line("  ❌ Incorrectas: {$incorrect}");
        $this->line("  📊 Total: " . ($correct + $incorrect));

        if ($incorrect > 0) {
            $this->newLine();
            $this->warn('⚠️  Se encontraron órdenes con problemas. Ejecuta el comando de regeneración:');
            $this->line('   php artisan purchase-orders:regenerate-pending --force');
        } else {
            $this->newLine();
            $this->info('🎉 ¡Todas las órdenes están correctas!');
        }

        return $incorrect > 0 ? 1 : 0;
    }

    /**
     * Valida una orden individual
     */
    private function validateOrder(PurchaseOrder $order)
    {
        $issues = [];
        $wrongItems = [];
        
        // Obtener todas las selecciones de la solicitud
        $allSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
        
        // Obtener selecciones que deberían estar en esta orden (del mismo proveedor)
        $expectedSelections = $allSelections->filter(function($selection) use ($order) {
            return $selection->quotation && 
                   $selection->quotation->provider_name === $order->provider->nombre;
        });
        
        // Obtener selecciones que NO deberían estar en esta orden (de otros proveedores)
        $wrongSelections = $allSelections->filter(function($selection) use ($order) {
            return $selection->quotation && 
                   $selection->quotation->provider_name !== $order->provider->nombre;
        });

        // Calcular totales esperados
        $expectedTotal = $expectedSelections->sum('total_price');
        $expectedSubtotal = round($expectedTotal / 1.19, 2);
        $expectedIva = round($expectedTotal - $expectedSubtotal, 2);

        // Validar totales
        $totalDifference = abs($order->total_amount - $expectedTotal);
        if ($totalDifference > 1.0) { // Tolerancia de $1 por redondeos
            $issues[] = "Total incorrecto: \${$order->total_amount} (esperado: \${$expectedTotal})";
        }

        $subtotalDifference = abs($order->subtotal - $expectedSubtotal);
        if ($subtotalDifference > 1.0) {
            $issues[] = "Subtotal incorrecto: \${$order->subtotal} (esperado: \${$expectedSubtotal})";
        }

        $ivaDifference = abs($order->iva_amount - $expectedIva);
        if ($ivaDifference > 1.0) {
            $issues[] = "IVA incorrecto: \${$order->iva_amount} (esperado: \${$expectedIva})";
        }

        // Verificar si hay artículos de otros proveedores (esto sería un error mayor)
        if ($wrongSelections->isNotEmpty()) {
            $issues[] = "Contiene {$wrongSelections->count()} artículos de otros proveedores";
            
            foreach ($wrongSelections as $wrongSelection) {
                $wrongItems[] = [
                    'description' => $wrongSelection->item_description,
                    'actual_provider' => $wrongSelection->quotation->provider_name,
                ];
            }
        }

        // Verificar que tenga todos los artículos que debería tener
        if ($expectedSelections->isEmpty()) {
            $issues[] = "No tiene artículos del proveedor asignado";
        }

        return [
            'is_correct' => empty($issues),
            'issues' => $issues,
            'wrong_items' => $wrongItems,
            'expected_total' => $expectedTotal,
            'actual_total' => $order->total_amount,
            'expected_items_count' => $expectedSelections->count(),
            'wrong_items_count' => $wrongSelections->count()
        ];
    }
}
