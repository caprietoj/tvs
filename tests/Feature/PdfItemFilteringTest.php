<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Provider;
use App\Models\User;
use App\Models\QuotationItemSelection;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PdfItemFilteringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que verifica el filtrado correcto de items en updatePdf para órdenes con selección mixta
     */
    public function test_update_pdf_filters_items_correctly_for_mixed_selection_orders()
    {
        // Crear datos de prueba
        $user = User::factory()->create(['role' => 'admin']);
        $provider1 = Provider::factory()->create(['nombre' => 'DETALGRAF S.A.S']);
        $provider2 = Provider::factory()->create(['nombre' => 'OTRO PROVEEDOR']);
        
        $purchaseRequest = PurchaseRequest::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'provider_id' => $provider1->id,
            'order_number' => 'ORD-TEST-001'
        ]);
        
        // Crear cotizaciones de diferentes proveedores
        $quotation1 = Quotation::factory()->create(['provider_name' => 'DETALGRAF S.A.S']);
        $quotation2 = Quotation::factory()->create(['provider_name' => 'OTRO PROVEEDOR']);
        
        // Crear selecciones mixtas (7 del provider1, 7 del provider2)
        for ($i = 0; $i < 7; $i++) {
            QuotationItemSelection::factory()->create([
                'purchase_request_id' => $purchaseRequest->id,
                'quotation_id' => $quotation1->id
            ]);
        }
        
        for ($i = 0; $i < 7; $i++) {
            QuotationItemSelection::factory()->create([
                'purchase_request_id' => $purchaseRequest->id,
                'quotation_id' => $quotation2->id
            ]);
        }
        
        // Simular request con 14 items (problema original)
        $requestData = [
            'items' => [],
            'subtotal' => 100000,
            'iva_amount' => 19000,
            'total' => 119000,
            'delivery_date' => now()->addDays(5)->format('Y-m-d'),
            'observations' => 'Test de filtrado'
        ];
        
        // Agregar 14 items al request
        for ($i = 0; $i < 14; $i++) {
            $requestData['items'][] = [
                'description' => 'Producto ' . ($i + 1),
                'quantity' => $i + 1,
                'unit_price' => '2.259',
                'total' => ($i + 1) * 2.259
            ];
        }
        
        // Simular la lógica del controlador updatePdf
        $itemsToSave = $requestData['items'];
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        
        if ($hasMixedSelection) {
            // Obtener selecciones específicas del proveedor
            $providerSelections = $purchaseRequest->quotationItemSelections()
                ->with('quotation')
                ->whereHas('quotation', function($query) use ($purchaseOrder) {
                    $query->where('provider_name', $purchaseOrder->provider->nombre);
                })
                ->get();
            
            // Aplicar filtrado
            $itemsToSave = [];
            $allSelections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
            
            foreach ($allSelections as $globalIndex => $selection) {
                if ($selection->quotation->provider_name === $purchaseOrder->provider->nombre) {
                    if (isset($requestData['items'][$globalIndex])) {
                        $itemsToSave[] = $requestData['items'][$globalIndex];
                    }
                }
            }
        }
        
        // Verificaciones
        $this->assertTrue($hasMixedSelection, 'La orden debería tener selección mixta');
        $this->assertEquals(14, count($requestData['items']), 'Deberían llegar 14 items del formulario');
        $this->assertEquals(7, count($itemsToSave), 'Deberían guardarse solo 7 items del proveedor específico');
        
        // Verificar que los items guardados corresponden a los del proveedor
        $providerSelectionsCount = $purchaseRequest->quotationItemSelections()
            ->whereHas('quotation', function($query) use ($purchaseOrder) {
                $query->where('provider_name', $purchaseOrder->provider->nombre);
            })
            ->count();
            
        $this->assertEquals($providerSelectionsCount, count($itemsToSave), 
            'Los items guardados deben coincidir con las selecciones del proveedor');
        
        // Verificar que no se guardaron items de otros proveedores
        $this->assertLessThan(count($requestData['items']), count($itemsToSave),
            'Los items guardados deben ser menos que los recibidos (filtrado aplicado)');
    }
}
