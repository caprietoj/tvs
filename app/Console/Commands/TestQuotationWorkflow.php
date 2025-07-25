<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\Quotation;

class TestQuotationWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:quotation-workflow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar flujo completo de cotización con precios individuales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PROBANDO FLUJO DE COTIZACIÓN ===');
        
        // Buscar una purchase request existente
        $purchaseRequest = PurchaseRequest::first();
        
        if (!$purchaseRequest) {
            $this->error('No hay purchase requests en el sistema');
            return 1;
        }
        
        $this->info("Usando Purchase Request ID: {$purchaseRequest->id}");
        $this->info("Request Number: {$purchaseRequest->request_number}");
        
        // Mostrar items de la solicitud
        if ($purchaseRequest->purchase_items) {
            $items = is_array($purchaseRequest->purchase_items) 
                ? $purchaseRequest->purchase_items 
                : json_decode($purchaseRequest->purchase_items, true);
            
            $this->info("Items de la solicitud:");
            foreach ($items as $index => $item) {
                $this->info("  [$index] {$item['description']} - Cantidad: {$item['quantity']}");
            }
        }
        
        // Crear datos de prueba simulando el problema reportado
        $testData = [
            'purchase_request_id' => $purchaseRequest->id,
            'provider_name' => 'Proveedor de Prueba - Precios Específicos',
            'file_path' => 'public/test/test-quotation.pdf', // Archivo ficticio para la prueba
            'subtotal' => 11500.00, // 10000 + 1500
            'total_amount' => 13685.00, // Con IVA 19%
            'includes_iva' => false,
            'iva_amount' => 0.00,
            'includes_iva_19' => true,
            'iva_19_amount' => 2185.00, // 19% de 11500
            'includes_iva_5' => false,
            'iva_5_amount' => 0.00,
            'includes_ipoconsumo_8' => false,
            'ipoconsumo_8_amount' => 0.00,
            'includes_ipoconsumo_4' => false,
            'ipoconsumo_4_amount' => 0.00,
            'tax_application_mode' => 'per_item',
            'delivery_time' => '5 días',
            'payment_method' => 'Contado',
            'validity' => '30 días',
            'warranty' => '1 año',
            'status' => 'pending',
            // Simulando cuadernos a $10000 y esferos a $1500
            'original_item_prices' => [
                0 => 10000.00, // Cuadernos
                1 => 1500.00   // Esferos
            ],
            'original_item_totals' => [
                0 => 10000.00, // 1 x 10000
                1 => 1500.00   // 1 x 1500
            ],
            'original_item_taxes' => [
                0 => ['iva_19' => true],
                1 => ['iva_19' => true]
            ]
        ];
        
        try {
            // Crear la cotización de prueba
            $quotation = Quotation::create($testData);
            
            $this->info("✓ Cotización de prueba creada con ID: {$quotation->id}");
            
            // Verificar que los datos se guardaron correctamente
            $quotation->refresh();
            
            $this->info("=== DATOS GUARDADOS ===");
            $this->info("Precios Originales: " . json_encode($quotation->original_item_prices, JSON_PRETTY_PRINT));
            $this->info("Totales Originales: " . json_encode($quotation->original_item_totals, JSON_PRETTY_PRINT));
            $this->info("Impuestos por Item: " . json_encode($quotation->original_item_taxes, JSON_PRETTY_PRINT));
            
            // Simular cómo se calcula en la orden de compra
            $this->info("=== SIMULACIÓN ORDEN DE COMPRA ===");
            
            if ($quotation->original_item_prices) {
                foreach ($quotation->original_item_prices as $index => $price) {
                    $total = $quotation->original_item_totals[$index] ?? ($price * 1);
                    $this->info("Item $index: Precio unitario = \$$price, Total = \$$total");
                }
            } else {
                $this->error("ERROR: No se guardaron los precios originales");
            }
            
            // Verificar cálculo total
            $sumaPreciosIndividuales = array_sum($quotation->original_item_totals ?? []);
            $this->info("Suma de precios individuales: \$$sumaPreciosIndividuales");
            $this->info("Subtotal guardado: \${$quotation->subtotal}");
            
            if (abs($sumaPreciosIndividuales - $quotation->subtotal) < 0.01) {
                $this->info("✓ Los cálculos coinciden");
            } else {
                $this->error("✗ DISCREPANCIA en cálculos");
            }
            
        } catch (\Exception $e) {
            $this->error("Error al crear cotización de prueba: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
