<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;
use App\Services\DynamicSectionEmailsService;
use App\Services\ProveedorNotificationService;

class VerifyEmailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:verify-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify email configuration is working correctly for all environments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN COMPLETA DE CONFIGURACIÓN DE CORREOS ===');
        $this->newLine();

        // 1. Información del ambiente
        $this->info('1. INFORMACIÓN DEL AMBIENTE:');
        $this->line('   APP_ENV: ' . config('app.env'));
        $this->line('   APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false'));
        $this->line('   APP_URL: ' . config('app.url'));
        $this->newLine();

        // 2. Variables de configuración
        $this->info('2. VARIABLES DE CONFIGURACIÓN:');
        $this->line('   SECTION_EMAILS_CONFIG (env): ' . env('SECTION_EMAILS_CONFIG', 'No definido'));
        $this->line('   EMAIL_TEST_MODE (env): ' . (env('EMAIL_TEST_MODE') ? 'true' : 'false'));
        $this->line('   PURCHASE_REQUEST_TEST_MODE (env): ' . (env('PURCHASE_REQUEST_TEST_MODE') ? 'true' : 'false'));
        $this->newLine();

        // 3. Configuración cacheada
        $this->info('3. CONFIGURACIÓN CACHEADA:');
        $this->line('   config(app.section_emails_config): ' . (config('app.section_emails_config') ?? 'No definido'));
        $this->line('   config(app.email_test_mode): ' . (config('app.email_test_mode') ? 'true' : 'false'));
        $this->newLine();

        // 4. Servicios activos
        $this->info('4. SERVICIOS ACTIVOS:');
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $isTestingMode = DynamicSectionEmailsService::isTestingMode();
        $emailTestEnabled = EmailTestModeService::isTestModeEnabled();
        
        $this->line('   DynamicSectionEmailsService::getCurrentConfigSource(): ' . $configSource);
        $this->line('   DynamicSectionEmailsService::isTestingMode(): ' . ($isTestingMode ? 'true' : 'false'));
        $this->line('   EmailTestModeService::isTestModeEnabled(): ' . ($emailTestEnabled ? 'true' : 'false'));
        $this->newLine();

        // 5. Análisis de configuración
        $this->info('5. ANÁLISIS DE CONFIGURACIÓN:');
        
        if (config('app.env') === 'production') {
            $this->checkProductionConfig($configSource, $isTestingMode, $emailTestEnabled);
        } else {
            $this->checkDevelopmentConfig($configSource, $isTestingMode, $emailTestEnabled);
        }
        
        $this->newLine();

        // 6. Prueba de correos de proveedores
        $this->info('6. PRUEBA DE CORREOS DE PROVEEDORES:');
        $this->testProveedorEmails();
        
        $this->newLine();

        // 7. Prueba de secciones
        $this->info('7. SECCIONES DISPONIBLES:');
        $this->testSections();
        
        $this->newLine();
        $this->info('=== FIN DE LA VERIFICACIÓN ===');
    }

    private function checkProductionConfig($configSource, $isTestingMode, $emailTestEnabled)
    {
        $hasErrors = false;

        if ($configSource !== 'section_emails') {
            $this->error('   ❌ ERROR: En producción debe usar section_emails, actualmente usa: ' . $configSource);
            $hasErrors = true;
        } else {
            $this->line('   ✅ Configuración de secciones correcta para producción');
        }

        if ($isTestingMode) {
            $this->error('   ❌ ERROR: En producción NO debe estar en modo de prueba');
            $hasErrors = true;
        } else {
            $this->line('   ✅ Modo de prueba desactivado correctamente');
        }

        if ($emailTestEnabled) {
            $this->error('   ❌ ERROR: En producción NO debe tener interceptación de correos');
            $hasErrors = true;
        } else {
            $this->line('   ✅ Interceptación de correos desactivada correctamente');
        }

        if (!$hasErrors) {
            $this->info('   🎉 CONFIGURACIÓN DE PRODUCCIÓN CORRECTA');
        } else {
            $this->error('   ⚠️  CONFIGURACIÓN DE PRODUCCIÓN TIENE ERRORES');
        }
    }

    private function checkDevelopmentConfig($configSource, $isTestingMode, $emailTestEnabled)
    {
        $this->line('   📝 Ambiente de desarrollo detectado');
        
        if ($configSource === 'section-mail-test') {
            $this->line('   ✅ Usando correos de prueba (@test.com)');
        } elseif ($configSource === 'section_emails') {
            $this->warn('   ⚠️  Usando correos reales (@tvs.edu.co) en desarrollo');
        }

        $this->line('   📊 Modo de prueba: ' . ($isTestingMode ? 'Activado' : 'Desactivado'));
        $this->line('   📊 Interceptación: ' . ($emailTestEnabled ? 'Activada' : 'Desactivada'));
    }

    private function testProveedorEmails()
    {
        try {
            $service = new ProveedorNotificationService();
            
            // Usar reflexión para acceder al método privado
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('getNotificationRecipients');
            $method->setAccessible(true);
            
            $recipients = $method->invoke($service);
            
            if (empty($recipients)) {
                $this->error('   ❌ No se encontraron destinatarios para proveedores');
            } else {
                $this->line('   ✅ Destinatarios de proveedores:');
                foreach ($recipients as $email) {
                    $domain = substr(strrchr($email, "@"), 1);
                    $icon = $domain === 'test.com' ? '🧪' : ($domain === 'tvs.edu.co' ? '🏢' : '❓');
                    $this->line("     {$icon} {$email}");
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error al obtener destinatarios: ' . $e->getMessage());
        }
    }

    private function testSections()
    {
        $sections = DynamicSectionEmailsService::getSections();
        
        $relevantSections = ['Contabilidad', 'Tesorería', 'Asistente Contabilidad', 'Sistemas', 'Compras'];
        
        foreach ($relevantSections as $section) {
            if (isset($sections[$section])) {
                $email = $sections[$section];
                $domain = is_array($email) ? substr(strrchr($email[0], "@"), 1) : substr(strrchr($email, "@"), 1);
                $icon = $domain === 'test.com' ? '🧪' : ($domain === 'tvs.edu.co' ? '🏢' : '❓');
                $emailDisplay = is_array($email) ? implode(', ', $email) : $email;
                $this->line("   {$icon} {$section}: {$emailDisplay}");
            } else {
                $this->line("   ❌ {$section}: No encontrado");
            }
        }
    }
}
