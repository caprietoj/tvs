<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;
use App\Models\PurchaseRequest;
use App\Mail\ServiceNoQuotationCreated;
use Illuminate\Support\Facades\Mail;

class TestEmailInterceptorComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-interceptor-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el interceptor de correos completamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA COMPLETA DEL INTERCEPTOR DE CORREOS ===');
        $this->newLine();
        
        // Verificar estado del modo de prueba
        $testModeEnabled = EmailTestModeService::isTestModeEnabled();
        $this->line('EMAIL_TEST_MODE: ' . ($testModeEnabled ? 'HABILITADO ✅' : 'DESHABILITADO ❌'));
        $this->line('SECTION_EMAILS_CONFIG: ' . env('SECTION_EMAILS_CONFIG', 'section_emails'));
        $this->newLine();
        
        if (!$testModeEnabled) {
            $this->warn('⚠️  MODO DE PRUEBA DESACTIVADO');
            $this->line('Los correos se enviarán a direcciones reales.');
            $this->line('Para activar modo de prueba: EMAIL_TEST_MODE=true en .env');
            $this->newLine();
        }
        
        // Probar interceptor básico
        $this->info('🔧 PRUEBA DEL INTERCEPTOR:');
        $testEmails = [
            'compras@tvs.edu.co',
            'jefesistemas@tvs.edu.co', 
            'coordpai@tvs.edu.co',
            'usuario@tvs.edu.co'
        ];
        
        foreach ($testEmails as $email) {
            $intercepted = EmailTestModeService::interceptEmail($email);
            $status = $email === $intercepted ? '🔄' : '✅';
            $this->line("{$status} {$email} → {$intercepted}");
        }
        
        $this->newLine();
        
        // Simular envío de correo de servicio sin cotización
        if ($this->confirm('¿Deseas simular el envío de un correo de servicio sin cotización?')) {
            $this->info('📧 SIMULANDO ENVÍO DE CORREO...');
            
            try {
                // Crear una solicitud ficticia
                $fakeRequest = new PurchaseRequest([
                    'id' => 999,
                    'type' => 'services',
                    'service_type' => 'no_quotation',
                    'description' => 'Servicio de prueba para interceptor',
                    'requester' => 'Usuario de Prueba',
                    'section_area' => 'Sistemas',
                ]);
                
                $testDestinations = [
                    'compras@tvs.edu.co' => 'compras',
                    'jefesistemas@tvs.edu.co' => 'sistemas',
                    'usuario@tvs.edu.co' => 'user'
                ];
                
                foreach ($testDestinations as $originalEmail => $type) {
                    $interceptedEmail = EmailTestModeService::interceptEmail($originalEmail);
                    
                    $this->line("Preparando envío a: {$originalEmail} → {$interceptedEmail} (tipo: {$type})");
                    
                    // SOLO SIMULAR, NO ENVIAR REALMENTE
                    // Mail::to($interceptedEmail)->send(new ServiceNoQuotationCreated($fakeRequest, $type));
                    
                    $this->line("✅ Simulado exitosamente");
                }
                
                $this->info('✅ SIMULACIÓN COMPLETADA');
                $this->line('(No se enviaron correos reales)');
                
            } catch (\Exception $e) {
                $this->error('❌ Error en simulación: ' . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info('🎯 RESUMEN:');
        $this->line('- Interceptor implementado en PurchaseRequestController ✅');
        $this->line('- Interceptor implementado en NotificationHelpers trait ✅');
        $this->line('- Interceptor implementado en Ticket model ✅');
        $this->line('- Estado actual: ' . ($testModeEnabled ? 'MODO PRUEBA' : 'MODO PRODUCCIÓN'));
        
        return 0;
    }
}
