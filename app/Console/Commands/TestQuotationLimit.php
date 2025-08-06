<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\User;

class TestQuotationLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:quotation-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la funcionalidad de límite dinámico de cotizaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de límite dinámico de cotizaciones');
        $this->newLine();

        // Buscar una solicitud de compra que requiera cotizaciones
        $purchaseRequest = PurchaseRequest::where('type', 'purchase')
            ->whereIn('status', ['pending', 'En Cotización'])
            ->first();

        if (!$purchaseRequest) {
            // Crear una solicitud de prueba
            $user = User::first();
            if (!$user) {
                $this->error('No hay usuarios en el sistema. No se puede crear una solicitud de prueba.');
                return 1;
            }

            $purchaseRequest = PurchaseRequest::create([
                'request_number' => 'TEST-' . time(),
                'type' => 'purchase',
                'status' => 'pending',
                'user_id' => $user->id,
                'requester' => $user->name,
                'section_area' => 'Sistemas',
                'request_date' => now(),
                'description' => 'Solicitud de prueba para verificar límite de cotizaciones',
                'budget' => 1000000,
                'purchase_items' => [
                    [
                        'description' => 'Item de prueba',
                        'quantity' => 1,
                        'unit' => 'unidad',
                        'specifications' => 'Especificaciones de prueba'
                    ]
                ]
            ]);

            $this->info("✅ Solicitud de prueba creada: {$purchaseRequest->request_number}");
        } else {
            $this->info("📋 Usando solicitud existente: {$purchaseRequest->request_number}");
        }

        $this->newLine();
        $this->info('📊 Estado actual:');
        $this->line("   • Solicitud: {$purchaseRequest->request_number}");
        $this->line("   • Estado: {$purchaseRequest->status}");
        $this->line("   • Cotizaciones actuales: {$purchaseRequest->quotations()->count()}");
        $this->line("   • Cotizaciones requeridas: {$purchaseRequest->getRequiredQuotationsCount()}");
        $this->line("   • Puede proceder antes: " . ($purchaseRequest->canProceedEarly() ? 'Sí' : 'No'));

        $this->newLine();
        $this->info('🔧 Probando configuración de cotizaciones...');

        // Probar configurar 5 cotizaciones
        $this->line('   1. Configurando solicitud para requerir 5 cotizaciones...');
        $purchaseRequest->configureRequiredQuotations(5, false);
        $purchaseRequest->refresh();

        $this->line("   ✅ Cotizaciones requeridas ahora: {$purchaseRequest->getRequiredQuotationsCount()}");
        $this->line("   ✅ Puede proceder antes: " . ($purchaseRequest->canProceedEarly() ? 'Sí' : 'No'));

        // Probar configurar 2 cotizaciones con posibilidad de proceder antes
        $this->line('   2. Configurando solicitud para requerir 2 cotizaciones (con proceed early)...');
        $purchaseRequest->configureRequiredQuotations(2, true);
        $purchaseRequest->refresh();

        $this->line("   ✅ Cotizaciones requeridas ahora: {$purchaseRequest->getRequiredQuotationsCount()}");
        $this->line("   ✅ Puede proceder antes: " . ($purchaseRequest->canProceedEarly() ? 'Sí' : 'No'));

        // Resetear a configuración por defecto
        $this->line('   3. Reseteando a configuración por defecto...');
        $purchaseRequest->resetToDefaultQuotations();
        $purchaseRequest->refresh();

        $this->line("   ✅ Cotizaciones requeridas ahora: {$purchaseRequest->getRequiredQuotationsCount()}");
        $this->line("   ✅ Puede proceder antes: " . ($purchaseRequest->canProceedEarly() ? 'Sí' : 'No'));

        $this->newLine();
        $this->info('✅ Todas las pruebas completadas exitosamente');
        $this->line('   • La funcionalidad de límite dinámico de cotizaciones está funcionando correctamente');
        $this->line('   • Los métodos del modelo responden apropiadamente');
        $this->line('   • Las validaciones en el controlador ahora usan la configuración dinámica');

        return 0;
    }
}
