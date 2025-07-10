<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DynamicSectionEmailsService;

class TestPurchaseRequestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase:test-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test purchase request email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DE CONFIGURACIÓN DE EMAILS DE SOLICITUDES DE COMPRA ===');
        $this->newLine();

        // Mostrar configuración actual
        $this->info('1. CONFIGURACIÓN ACTUAL:');
        $configSource = DynamicSectionEmailsService::getCurrentConfigSource();
        $this->line('   Fuente de configuración: ' . $configSource);
        $this->line('   Modo de prueba: ' . (DynamicSectionEmailsService::isTestingMode() ? 'SÍ' : 'NO'));
        $this->newLine();

        // Probar directores
        $this->info('2. CONFIGURACIÓN DE DIRECTORES:');
        $directors = config($configSource . '.directors', []);
        
        if (empty($directors)) {
            $this->error('   ❌ No se encontró configuración de directores');
        } else {
            foreach ($directors as $role => $email) {
                $domain = substr(strrchr($email, "@"), 1);
                $icon = $domain === 'test.com' ? '🧪' : ($domain === 'tvs.edu.co' ? '🏢' : '❓');
                $this->line("   {$icon} {$role}: {$email}");
            }
        }
        $this->newLine();

        // Simular envíos por sección
        $this->info('3. SIMULACIÓN DE ENVÍOS POR SECCIÓN:');
        $testSections = [
            'Administración',
            'Pre Escolar', 
            'PEP',
            'Psicología'
        ];

        foreach ($testSections as $section) {
            $this->line("   📧 Sección: {$section}");
            $email = $this->getApprovalEmailForSection($section, $configSource);
            
            if ($email) {
                $domain = substr(strrchr($email, "@"), 1);
                $icon = $domain === 'test.com' ? '🧪' : ($domain === 'tvs.edu.co' ? '🏢' : '❓');
                $this->line("     {$icon} Destinatario: {$email}");
            } else {
                $this->line("     ❌ No se encontró destinatario");
            }
        }
        
        $this->newLine();
        $this->info('=== FIN DE LA PRUEBA ===');
    }

    private function getApprovalEmailForSection($section, $configSource)
    {
        // Simular la lógica del controlador
        switch ($section) {
            case 'Pre Escolar':
            case 'Primaria':
            case 'Bachillerato':
                $academicDirector = config($configSource . '.directors.academic');
                if ($academicDirector) {
                    return $academicDirector;
                } else {
                    return config($configSource . '.sections.Dirección General', 'generaldirector@tvs.edu.co');
                }
                
            case 'PEP':
            case 'PAI':
            case 'Diploma':
                $ibCoordinator = config($configSource . '.directors.ib_coordinator');
                if ($ibCoordinator) {
                    return $ibCoordinator;
                } else {
                    return 'coordinador.ib@tvs.edu.co';
                }
                
            case 'Administración':
            case 'Dirección General':
            case 'Compras':
            case 'Sistemas':
            case 'Mantenimiento':
                $administrativeDirector = config($configSource . '.directors.administrative');
                if ($administrativeDirector) {
                    return $administrativeDirector;
                } else {
                    return config($configSource . '.sections.Administración', 'administrativedirector@tvs.edu.co');
                }
                
            case 'Psicología':
            case 'CAS':
                $wellnessCoordinator = config($configSource . '.directors.wellness_coordinator');
                if ($wellnessCoordinator) {
                    return $wellnessCoordinator;
                } else {
                    return config($configSource . '.sections.Psicología', 'coordinador.bienestar@tvs.edu.co');
                }
                
            default:
                $administrativeDirector = config($configSource . '.directors.administrative');
                if ($administrativeDirector) {
                    return $administrativeDirector;
                } else {
                    return config($configSource . '.sections.Administración', 'administrativedirector@tvs.edu.co');
                }
        }
    }
}
