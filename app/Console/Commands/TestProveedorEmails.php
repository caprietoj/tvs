<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProveedorNotificationService;
use App\Services\DynamicSectionEmailsService;

class TestProveedorEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proveedor:test-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test proveedor email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DE CONFIGURACIÓN DE EMAILS DE PROVEEDORES ===');
        $this->newLine();

        // Mostrar configuración actual
        $this->info('1. CONFIGURACIÓN ACTUAL:');
        $this->line('   SECTION_EMAILS_CONFIG: ' . env('SECTION_EMAILS_CONFIG', 'section_emails'));
        $this->line('   Modo de prueba: ' . (DynamicSectionEmailsService::isTestingMode() ? 'SÍ' : 'NO'));
        $this->line('   Fuente de configuración: ' . DynamicSectionEmailsService::getCurrentConfigSource());
        $this->newLine();

        // Simular servicio de notificaciones
        $this->info('2. DESTINATARIOS DE PROVEEDORES:');
        
        $service = new ProveedorNotificationService();
        
        // Usar reflexión para acceder al método privado
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getNotificationRecipients');
        $method->setAccessible(true);
        
        $recipients = $method->invoke($service);
        
        if (empty($recipients)) {
            $this->error('   ❌ No se encontraron destinatarios');
        } else {
            $this->line('   ✅ Destinatarios encontrados:');
            foreach ($recipients as $email) {
                $this->line('     - ' . $email);
            }
        }
        
        $this->newLine();
        
        // Mostrar secciones disponibles
        $this->info('3. SECCIONES DISPONIBLES EN LA CONFIGURACIÓN:');
        $sections = DynamicSectionEmailsService::getSections();
        
        $relevantSections = ['Contabilidad', 'Tesorería', 'Asistente Contabilidad'];
        
        foreach ($relevantSections as $section) {
            if (isset($sections[$section])) {
                $this->line("   ✅ {$section}: " . $sections[$section]);
            } else {
                $this->line("   ❌ {$section}: No encontrado");
            }
        }
        
        $this->newLine();
        $this->info('=== FIN DE LA PRUEBA ===');
    }
}
