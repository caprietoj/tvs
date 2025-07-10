<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\QuotationItemSelection;
use App\Http\Controllers\ApprovalController;
use Illuminate\Http\Request;

class TestMixedSelectionApproval extends Command
{
    protected $signature = 'test:mixed-selection';
    protected $description = 'Prueba la aprobación de solicitudes con selección mixta de proveedores';

    public function handle()
    {
        $this->info('Iniciando prueba de selección mixta...');
        
        // Buscar una solicitud con selección mixta
        $purchaseRequest = PurchaseRequest::whereHas('quotationItemSelections')
            ->where('status', 'approved_by_manager')
            ->first();
            
        if (!$purchaseRequest) {
            $this->error('No se encontró ninguna solicitud con selección mixta aprobada por el gerente.');
            return;
        }
        
        $this->info("Procesando solicitud ID: {$purchaseRequest->id}");
        
        // Mostrar información de las selecciones
        $selections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
        $providerGroups = $selections->groupBy('quotation.provider_name');
        
        $this->info("Proveedores encontrados: " . $providerGroups->count());
        
        foreach ($providerGroups as $provider => $items) {
            $this->info("- {$provider}: {$items->count()} items, Total: $" . number_format($items->sum('total_price'), 0));
        }
        
        // Simular aprobación
        $this->info('Simulando aprobación...');
        
        try {
            $controller = new ApprovalController();
            $request = new Request([
                'action' => 'approve',
                'observations' => 'Prueba de selección mixta desde comando'
            ]);
            
            // Usar reflexión para acceder al método privado
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('createMixedSelectionPurchaseOrders');
            $method->setAccessible(true);
            
            $method->invoke($controller, $purchaseRequest);
            
            $this->info('Aprobación simulada exitosamente.');
            
            // Verificar órdenes creadas
            $orders = $purchaseRequest->purchaseOrders()->get();
            $this->info("Órdenes de compra creadas: " . $orders->count());
            
            foreach ($orders as $order) {
                $this->info("- Orden #{$order->id}: {$order->provider->name} - $" . number_format($order->total_amount, 0));
                $this->info("  PDF: " . ($order->file_path !== 'pending_generation' ? 'Generado' : 'Pendiente'));
            }
            
        } catch (\Exception $e) {
            $this->error("Error durante la prueba: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
