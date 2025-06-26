<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DynamicSectionEmailsService;

class TestSectionEmailConfig extends Command
{
    protected $signature = 'test:section-email-config';
    protected $description = 'Verifica que se esté usando la configuración correcta de correos por sección';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE CONFIGURACIÓN DE CORREOS POR SECCIÓN ===');
        
        // 1. Verificar fuente de configuración
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $this->info("Fuente de configuración actual: {$configSource}");
        
        $isTestingMode = DynamicSectionEmailsService::isTestingMode();
        $this->info("¿Está en modo de prueba?: " . ($isTestingMode ? 'SÍ' : 'NO'));
        
        // 2. Verificar que el archivo existe
        $configPath = config_path("{$configSource}.php");
        $this->info("Archivo de configuración: {$configPath}");
        $this->info("¿Archivo existe?: " . (file_exists($configPath) ? 'SÍ' : 'NO'));
        
        // 3. Probar algunas secciones específicas
        $this->info('');
        $this->info('=== CORREOS POR SECCIÓN ===');
        
        $testSections = ['Sistemas', 'PAI', 'Compras', 'Contabilidad', 'Pre Escolar'];
        
        foreach ($testSections as $section) {
            $email = DynamicSectionEmailsService::getConfig("sections.{$section}");
            $this->info("- {$section}: " . (is_array($email) ? implode(', ', $email) : $email));
        }
        
        // 4. Verificar correo por defecto
        $this->info('');
        $defaultEmail = DynamicSectionEmailsService::getConfig('default');
        $this->info("Correo por defecto: {$defaultEmail}");
        
        // 5. Verificar correos de aprobación de materiales
        $this->info('');
        $this->info('=== CORREOS DE APROBACIÓN DE MATERIALES ===');
        $materialsApprovalSistemas = DynamicSectionEmailsService::getConfig('materials_approval_emails.Sistemas');
        $this->info("- Sistemas: " . (is_array($materialsApprovalSistemas) ? implode(', ', $materialsApprovalSistemas) : $materialsApprovalSistemas));
        
        $materialsApprovalPAI = DynamicSectionEmailsService::getConfig('materials_approval_emails.PAI');
        $this->info("- PAI: " . (is_array($materialsApprovalPAI) ? implode(', ', $materialsApprovalPAI) : ($materialsApprovalPAI ?: 'No configurado')));
        
        // 6. Verificar configuración del env
        $this->info('');
        $this->info('=== CONFIGURACIÓN DEL ARCHIVO .ENV ===');
        $envValue = env('SECTION_EMAILS_CONFIG', 'No configurado');
        $this->info("SECTION_EMAILS_CONFIG = {$envValue}");
        
        $this->info('');
        $this->info('=== VERIFICACIÓN COMPLETADA ===');
        
        return 0;
    }
}
