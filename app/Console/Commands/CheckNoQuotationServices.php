<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;

class CheckNoQuotationServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:no-quotation-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check no quotation services in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICANDO SERVICIOS SIN COTIZACIÓN ===');
        
        // Verificar solicitudes específicas
        $specificIds = [4, 6];
        foreach ($specificIds as $id) {
            $request = PurchaseRequest::find($id);
            if ($request) {
                $this->info("Solicitud ID: {$id}");
                $this->info("  Tipo: {$request->type}");
                $this->info("  Tipo de servicio: " . ($request->service_type ?? 'NULL'));
                $this->info("  isNoQuotationService(): " . ($request->isNoQuotationService() ? 'true' : 'false'));
                $this->info("  Estado: {$request->status}");
                $this->info("  Proveedor: " . ($request->provider_name ?? 'NULL'));
                $this->info("  Presupuesto: " . ($request->service_budget ?? 'NULL'));
                $this->info("---");
            } else {
                $this->info("Solicitud ID {$id} no encontrada");
            }
        }
        
        // Buscar todos los servicios sin cotización
        $this->info("=== TODOS LOS SERVICIOS SIN COTIZACIÓN ===");
        $services = PurchaseRequest::where('type', 'services')
            ->where('service_type', 'no_quotation')
            ->get();
            
        if ($services->isEmpty()) {
            $this->info("No se encontraron servicios sin cotización.");
        } else {
            foreach ($services as $service) {
                $this->info("ID: {$service->id} - Estado: {$service->status} - Proveedor: {$service->provider_name}");
            }
        }
        
        return 0;
    }
}
