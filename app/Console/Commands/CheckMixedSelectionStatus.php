<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;

class CheckMixedSelectionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-requests:check-mixed-selection {request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar el estado de las selecciones mixtas y órdenes pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requestId = $this->argument('request_id');
        
        if ($requestId) {
            $this->checkSingleRequest($requestId);
        } else {
            $this->checkAllMixedSelections();
        }
    }
    
    private function checkSingleRequest($requestId)
    {
        $request = PurchaseRequest::with(['quotationItemSelections.quotation', 'purchaseOrders.provider'])->find($requestId);
        
        if (!$request) {
            $this->error("Solicitud {$requestId} no encontrada");
            return;
        }
        
        $this->info("=== Solicitud {$request->request_number} ===");
        $this->line("Estado: {$request->status}");
        $this->line("Tipo: {$request->type}");
        
        $hasMixedSelection = $request->quotationItemSelections()->exists();
        $this->line("Tiene selección mixta: " . ($hasMixedSelection ? 'Sí' : 'No'));
        
        if ($hasMixedSelection) {
            // Proveedores con selecciones
            $selections = $request->quotationItemSelections()->with('quotation')->get();
            $providersWithSelections = $selections->groupBy('quotation.provider_name');
            
            $this->info("\nProveedores con items seleccionados:");
            foreach ($providersWithSelections as $providerName => $items) {
                $this->line("  - {$providerName}: {$items->count()} items");
            }
            
            // Órdenes existentes
            $orders = $request->purchaseOrders()->whereNull('deleted_at')->with('provider')->get();
            $this->info("\nÓrdenes creadas: {$orders->count()}");
            foreach ($orders as $order) {
                $providerName = $order->provider ? $order->provider->nombre : 'N/A';
                $this->line("  - {$order->order_number}: {$providerName} ({$order->status})");
            }
            
            // Proveedores pendientes
            $providersWithOrders = $orders->pluck('provider.nombre')->filter();
            $pendingProviders = $providersWithSelections->keys()->diff($providersWithOrders);
            
            $this->info("\nProveedores pendientes: {$pendingProviders->count()}");
            foreach ($pendingProviders as $provider) {
                $this->line("  - {$provider}");
            }
            
            $shouldShowInList = $pendingProviders->isNotEmpty();
            $status = $shouldShowInList ? 'PENDIENTE' : 'COMPLETO';
            $this->info("\nEstado de la solicitud: {$status}");
        }
    }
    
    private function checkAllMixedSelections()
    {
        $this->info("=== Todas las Selecciones Mixtas ===");
        
        $requests = PurchaseRequest::whereHas('quotationItemSelections')
            ->whereIn('status', ['approved', 'in_process'])
            ->with(['quotationItemSelections.quotation', 'purchaseOrders.provider'])
            ->get();
            
        $this->info("Total de solicitudes con selección mixta: {$requests->count()}");
        
        $pendingCount = 0;
        
        foreach ($requests as $request) {
            $selections = $request->quotationItemSelections()->with('quotation')->get();
            $providersWithSelections = $selections->groupBy('quotation.provider_name')->keys();
            
            $providersWithOrders = $request->purchaseOrders()
                ->whereNull('deleted_at')
                ->whereHas('provider')
                ->get()
                ->pluck('provider.nombre')
                ->filter();
                
            $pendingProviders = $providersWithSelections->diff($providersWithOrders);
            $isPending = $pendingProviders->isNotEmpty();
            
            if ($isPending) {
                $pendingCount++;
                $this->line("\n{$request->request_number}:");
                $this->line("  Proveedores totales: {$providersWithSelections->count()}");
                $this->line("  Órdenes creadas: {$providersWithOrders->count()}");
                $this->line("  Pendientes: {$pendingProviders->count()} - " . $pendingProviders->implode(', '));
            }
        }
        
        $this->info("\nSolicitudes pendientes: {$pendingCount}");
        $this->info("Solicitudes completas: " . ($requests->count() - $pendingCount));
    }
}
