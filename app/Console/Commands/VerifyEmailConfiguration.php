<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DynamicSectionEmailsService;

class VerifyEmailConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:email-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar la configuración actual de correos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE CONFIGURACIÓN DE CORREOS ===');
        $this->newLine();
        
        // Verificar configuraciones del .env
        $this->info('📧 CONFIGURACIONES DEL .ENV:');
        $this->line('EMAIL_TEST_MODE: ' . (env('EMAIL_TEST_MODE', 'false') ? 'true' : 'false'));
        $this->line('PURCHASE_REQUEST_TEST_MODE: ' . (env('PURCHASE_REQUEST_TEST_MODE', 'false') ? 'true' : 'false'));
        $this->line('SECTION_EMAILS_CONFIG: ' . env('SECTION_EMAILS_CONFIG', 'section_emails'));
        $this->newLine();
        
        // Verificar el servicio dinámico
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $this->info('🔧 SERVICIO DINÁMICO:');
        $this->line('Config Source: ' . $configSource);
        $this->line('Is Testing Mode: ' . (DynamicSectionEmailsService::isTestingMode() ? 'YES' : 'NO'));
        $this->newLine();
        
        // Verificar algunos correos específicos
        $this->info('📮 CORREOS CONFIGURADOS:');
        
        $sections = DynamicSectionEmailsService::getSections();
        $comprasEmail = $sections['Compras'] ?? 'NO CONFIGURADO';
        $this->line('Compras: ' . $comprasEmail);
        
        $sistemasEmail = $sections['Sistemas'] ?? 'NO CONFIGURADO';
        $this->line('Sistemas: ' . $sistemasEmail);
        
        $paiEmails = $sections['PAI'] ?? 'NO CONFIGURADO';
        if (is_array($paiEmails)) {
            $this->line('PAI: ' . implode(', ', $paiEmails));
        } else {
            $this->line('PAI: ' . $paiEmails);
        }
        
        $this->newLine();
        
        // Verificar always_notify
        $alwaysNotify = DynamicSectionEmailsService::getAlwaysNotify();
        $this->info('🔔 SIEMPRE NOTIFICAR:');
        $this->line(implode(', ', $alwaysNotify));
        $this->newLine();
        
        // Verificar correo por defecto
        $defaultEmail = DynamicSectionEmailsService::getDefault();
        $this->info('🎯 CORREO POR DEFECTO:');
        $this->line($defaultEmail);
        $this->newLine();
        
        // Análisis final
        $this->info('🎯 ANÁLISIS:');
        if ($configSource === 'section_emails') {
            $this->info('✅ CONFIGURACIÓN CORRECTA - Usando correos de producción (@tvs.edu.co)');
        } else {
            $this->warn('⚠️  ATENCIÓN - Usando correos de prueba (@test.com)');
            $this->line('Para producción, cambiar SECTION_EMAILS_CONFIG=section_emails en el .env');
        }
        
        if (str_contains($comprasEmail, '@tvs.edu.co')) {
            $this->info('✅ Correos de producción activos');
        } else {
            $this->warn('⚠️  Correos de prueba activos');
        }
        
        return 0;
    }
}
