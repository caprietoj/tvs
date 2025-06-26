<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;
use App\Services\DynamicSectionEmailsService;

class DemoEmailSystem extends Command
{
    protected $signature = 'demo:email-system';
    protected $description = 'Demostración del sistema de correos funcionando correctamente';

    public function handle()
    {
        $this->info('=== DEMOSTRACIÓN DEL SISTEMA DE CORREOS ===');
        
        // Estado actual
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $isTestConfig = DynamicSectionEmailsService::isTestingMode();
        $isInterceptorEnabled = EmailTestModeService::isTestModeEnabled();
        
        $this->info("Configuración actual: {$configSource}");
        $this->info("¿Es configuración de prueba?: " . ($isTestConfig ? 'SÍ' : 'NO'));
        $this->info("¿Interceptor activo?: " . ($isInterceptorEnabled ? 'SÍ' : 'NO'));
        
        $this->info('');
        $this->info('=== CORREOS QUE SE ENVIARÍAN ===');
        
        // Simular correos de diferentes secciones
        $sections = ['Compras', 'Sistemas', 'PAI', 'Contabilidad'];
        
        foreach ($sections as $section) {
            // Obtener correo de la configuración de sección
            $sectionEmail = DynamicSectionEmailsService::getConfig("sections.{$section}");
            
            if (is_array($sectionEmail)) {
                $this->info("--- {$section} ---");
                foreach ($sectionEmail as $email) {
                    $finalEmail = EmailTestModeService::interceptEmail($email, $section);
                    $this->info("  {$email} → {$finalEmail}");
                }
            } else {
                $finalEmail = EmailTestModeService::interceptEmail($sectionEmail, $section);
                $this->info("{$section}: {$sectionEmail} → {$finalEmail}");
            }
        }
        
        $this->info('');
        $this->info('=== ANÁLISIS ===');
        
        if ($isTestConfig && !$isInterceptorEnabled) {
            $this->info('✅ CONFIGURACIÓN ÓPTIMA:');
            $this->info('   - Usando section-mail-test.php (correos @test.com)');
            $this->info('   - Interceptor desactivado automáticamente (no es necesario)');
            $this->info('   - Los correos van directamente a direcciones de prueba');
        } elseif (!$isTestConfig && $isInterceptorEnabled) {
            $this->info('✅ CONFIGURACIÓN DE RESPALDO:');
            $this->info('   - Usando section_emails.php (correos @tvs.edu.co)');
            $this->info('   - Interceptor activo para protección');
            $this->info('   - Los correos se redirigen a direcciones de prueba');
        } elseif (!$isTestConfig && !$isInterceptorEnabled) {
            $this->error('⚠️ PELIGRO - MODO PRODUCCIÓN:');
            $this->error('   - Usando section_emails.php (correos @tvs.edu.co)');
            $this->error('   - Interceptor DESACTIVADO');
            $this->error('   - Los correos VAN A PRODUCCIÓN');
        } else {
            $this->warn('⚡ CONFIGURACIÓN REDUNDANTE:');
            $this->warn('   - Usando section-mail-test.php + Interceptor activo');
            $this->warn('   - Doble protección (funciona pero es innecesaria)');
        }
        
        return 0;
    }
}
