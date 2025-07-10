<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;

class TestServiceQuotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:test-quotations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test service quotations display';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DE SERVICIOS QUE REQUIEREN COTIZACIÓN ===');
        $this->newLine();

        // 1. Mostrar solicitudes de servicios
        $this->info('1. SOLICITUDES DE SERVICIOS:');
        $services = PurchaseRequest::where('type', 'services')->get();
        
        if ($services->isEmpty()) {
            $this->line('   📝 No hay solicitudes de servicios');
        } else {
            foreach ($services as $service) {
                $icon = $service->service_type === 'regular' ? '💼' : '⚡';
                $requiresQuotes = $service->requiresQuotations() ? 'SÍ' : 'NO';
                $this->line("   {$icon} #{$service->request_number} - {$service->service_type} - Requiere cotizaciones: {$requiresQuotes}");
            }
        }
        $this->newLine();

        // 2. Mostrar qué aparecería en la lista de cotizaciones
        $this->info('2. SOLICITUDES QUE APARECEN EN COTIZACIONES:');
        
        // Simular la consulta del QuotationController
        $quotationRequests = PurchaseRequest::where(function($query) {
                                // Solicitudes de compra
                                $query->where('type', 'purchase')
                                      // O servicios regulares (que requieren cotización)
                                      ->orWhere(function($q) {
                                          $q->where('type', 'services')
                                            ->where('service_type', 'regular');
                                      });
                            })
                            ->whereIn('status', ['pending', 'En Cotización'])
                            ->get();
        
        if ($quotationRequests->isEmpty()) {
            $this->line('   📝 No hay solicitudes pendientes que requieran cotización');
        } else {
            foreach ($quotationRequests as $request) {
                $icon = $request->type === 'purchase' ? '🛒' : '💼';
                $typeLabel = $request->type === 'purchase' ? 'Compra' : 'Servicio Regular';
                $quotationsCount = $request->quotations()->count();
                $this->line("   {$icon} #{$request->request_number} - {$typeLabel} - Status: {$request->status} - Cotizaciones: {$quotationsCount}");
            }
        }
        $this->newLine();

        // 3. Probar método requiresQuotations()
        $this->info('3. PRUEBA DE MÉTODO requiresQuotations():');
        $allRequests = PurchaseRequest::whereIn('type', ['purchase', 'services'])->get();
        
        foreach ($allRequests as $request) {
            $icon = $request->type === 'purchase' ? '🛒' : ($request->service_type === 'regular' ? '💼' : '⚡');
            $requires = $request->requiresQuotations() ? '✅' : '❌';
            $type = $request->type === 'services' ? "Servicio ({$request->service_type})" : 'Compra';
            $this->line("   {$icon} #{$request->request_number} - {$type} - Requiere: {$requires}");
        }
        
        $this->newLine();
        $this->info('=== FIN DE LA PRUEBA ===');
    }
}
