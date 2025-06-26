<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;
use App\Services\DynamicSectionEmailsService;

class TestCompleteEmailSystem extends Command
{
    protected $signature = 'test:complete-email-system';
    protected $description = 'Prueba completa del sistema de interceptación y configuración de correos';

    public function handle()
    {
        $this->info('=== PRUEBA COMPLETA DEL SISTEMA DE CORREOS ===');
        
        // 1. Verificar configuración del interceptor
        $emailTestService = new EmailTestModeService();
        $isInterceptorEnabled = EmailTestModeService::isTestModeEnabled();
        $this->info("1. Interceptor de correos: " . ($isInterceptorEnabled ? 'HABILITADO' : 'DESHABILITADO'));
        
        // 2. Verificar configuración de secciones
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $isTestConfig = DynamicSectionEmailsService::isTestingMode();
        $this->info("2. Configuración de secciones: {$configSource} " . ($isTestConfig ? '(MODO PRUEBA)' : '(MODO PRODUCCIÓN)'));
        
        $this->info('');
        $this->info('=== PRUEBA DE FLUJO COMPLETO ===');
        
        // 3. Simular envío de correos por sección
        $testSections = [
            'Sistemas' => 'jefesistemas@test.com',
            'PAI' => ['escuelamedia@test.com', 'coordpai@test.com'],
            'Compras' => 'compras@test.com',
            'Contabilidad' => 'contabilidad@test.com'
        ];
        
        foreach ($testSections as $section => $expectedEmails) {
            $this->info("");
            $this->info("--- Sección: {$section} ---");
            
            // Obtener correos de la configuración de sección
            $sectionEmails = DynamicSectionEmailsService::getConfig("sections.{$section}");
            $this->info("Correos de sección: " . (is_array($sectionEmails) ? implode(', ', $sectionEmails) : $sectionEmails));
            
            // Aplicar interceptor
            if (is_array($sectionEmails)) {
                $interceptedEmails = $emailTestService->interceptEmails($sectionEmails, $section);
                $this->info("Correos interceptados: " . implode(', ', $interceptedEmails));
            } else {
                $interceptedEmail = $emailTestService->interceptEmail($sectionEmails, $section);
                $this->info("Correo interceptado: {$interceptedEmail}");
            }
        }
        
        // 4. Probar correos de aprobación de materiales
        $this->info('');
        $this->info('=== CORREOS DE APROBACIÓN DE MATERIALES ===');
        
        $materialsSections = ['Sistemas', 'PAI', 'Pre Escolar'];
        foreach ($materialsSections as $section) {
            $approvalEmails = DynamicSectionEmailsService::getConfig("materials_approval_emails.{$section}");
            if ($approvalEmails) {
                $this->info("- {$section}: " . (is_array($approvalEmails) ? implode(', ', $approvalEmails) : $approvalEmails));
                
                // Aplicar interceptor
                if (is_array($approvalEmails)) {
                    $interceptedApprovalEmails = $emailTestService->interceptEmails($approvalEmails, $section);
                    $this->info("  Interceptados: " . implode(', ', $interceptedApprovalEmails));
                } else {
                    $interceptedApprovalEmail = $emailTestService->interceptEmail($approvalEmails, $section);
                    $this->info("  Interceptado: {$interceptedApprovalEmail}");
                }
            } else {
                $this->info("- {$section}: No configurado");
            }
        }
        
        // 5. Verificar correo por defecto
        $this->info('');
        $defaultEmail = DynamicSectionEmailsService::getConfig('default');
        $interceptedDefault = $emailTestService->interceptEmail($defaultEmail, 'Compras');
        $this->info("Correo por defecto: {$defaultEmail}");
        $this->info("Interceptado: {$interceptedDefault}");
        
        $this->info('');
        $this->info('=== RESUMEN ===');
        $this->info("✅ Interceptor: " . ($isInterceptorEnabled ? 'ACTIVO' : 'INACTIVO'));
        $this->info("✅ Configuración: " . ($isTestConfig ? 'PRUEBA (@test.com)' : 'PRODUCCIÓN (@tvs.edu.co)'));
        $this->info("✅ El sistema está " . ($isInterceptorEnabled && $isTestConfig ? 'COMPLETAMENTE CONFIGURADO PARA PRUEBAS' : 'NO configurado para pruebas completas'));
        
        if ($isInterceptorEnabled && $isTestConfig) {
            $this->info('');
            $this->info('🎉 SISTEMA FUNCIONANDO CORRECTAMENTE PARA PRUEBAS');
            $this->info('   Todos los correos se redirigirán a direcciones de prueba.');
        } else {
            $this->warn('');
            $this->warn('⚠️  ADVERTENCIA: El sistema no está completamente configurado para pruebas');
            if (!$isInterceptorEnabled) {
                $this->warn('   - Configure EMAIL_TEST_MODE=true en el .env');
            }
            if (!$isTestConfig) {
                $this->warn('   - Configure SECTION_EMAILS_CONFIG=section-mail-test en el .env');
            }
        }
        
        return 0;
    }
}
