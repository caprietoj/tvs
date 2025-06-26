<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;

class TestEmailInterceptor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-interceptor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el interceptor de correos en modo de prueba';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DEL INTERCEPTOR DE CORREOS ===');
        $this->newLine();
        
        // Verificar estado del modo de prueba
        $testModeEnabled = EmailTestModeService::isTestModeEnabled();
        $this->line('EMAIL_TEST_MODE: ' . ($testModeEnabled ? 'HABILITADO' : 'DESHABILITADO'));
        $this->newLine();
        
        // Correos reales de producción para probar
        $realEmails = [
            'compras@tvs.edu.co',
            'jefesistemas@tvs.edu.co',
            'coordpai@tvs.edu.co',
            'escuelamedia@tvs.edu.co',
            'administrativedirector@tvs.edu.co',
            'generaldirector@tvs.edu.co',
            'usuario@tvs.edu.co'
        ];
        
        $this->info('RESULTADO DEL INTERCEPTOR:');
        $this->line('');
        
        foreach ($realEmails as $realEmail) {
            $interceptedEmail = EmailTestModeService::interceptEmail($realEmail);
            
            $status = ($realEmail === $interceptedEmail) ? '🔄 SIN CAMBIO' : '✅ INTERCEPTADO';
            
            $this->line("{$status}: {$realEmail} → {$interceptedEmail}");
        }
        
        $this->newLine();
        
        if ($testModeEnabled) {
            $this->info('✅ MODO DE PRUEBA ACTIVO - Los correos están siendo interceptados');
            $this->line('Los correos se enviarán a direcciones de prueba en lugar de las reales');
        } else {
            $this->warn('⚠️  MODO DE PRODUCCIÓN - Los correos se envían a direcciones reales');
            $this->line('Para activar el modo de prueba: EMAIL_TEST_MODE=true en .env');
        }
        
        $this->newLine();
        $this->info('🔧 CONFIGURACIÓN ACTUAL:');
        $this->line('SECTION_EMAILS_CONFIG: ' . env('SECTION_EMAILS_CONFIG', 'section_emails'));
        $this->line('EMAIL_TEST_SISTEMAS: ' . env('EMAIL_TEST_SISTEMAS', 'N/A'));
        $this->line('EMAIL_TEST_PAI: ' . env('EMAIL_TEST_PAI', 'N/A'));
        
        return 0;
    }
}
