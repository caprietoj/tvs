<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SectionClassifierService;

class VerifyCASNotifications extends Command
{
    protected $signature = 'verify:cas-notifications {--test-amounts=100000,600000 : Montos a probar separados por comas}';
    protected $description = 'Verifica que las notificaciones de CAS solo se envíen a administrativedirector@tvs.edu.co';

    public function handle()
    {
        $this->info('🔍 VERIFICACIÓN: Notificaciones exclusivas CAS → Director Administrativo');
        $this->newLine();

        $service = new SectionClassifierService();
        $testAmounts = array_map('floatval', explode(',', $this->option('test-amounts')));
        
        $this->verifyCASRouting($service, $testAmounts);
        $this->verifyOtherSectionsNotAffected($service, $testAmounts);
        
        $this->newLine();
        $this->info('✅ Verificación CAS completada');
    }

    private function verifyCASRouting($service, $testAmounts)
    {
        $this->line('📋 1. Verificando enrutamiento específico para CAS...');
        
        $casVariations = ['CAS', 'cas', 'Cas', ' CAS ', ' cas '];
        
        foreach ($casVariations as $casSection) {
            $this->line("   🎯 Probando sección: '{$casSection}'");
            
            foreach ($testAmounts as $amount) {
                $this->line("     💰 Monto: $" . number_format($amount, 0));
                
                // Probar getDirectorEmail
                $directorEmail = $service->getDirectorEmail($casSection, $amount);
                if ($directorEmail === 'administrativedirector@tvs.edu.co') {
                    $this->info("       ✅ Director email: {$directorEmail}");
                } else {
                    $this->error("       ❌ Director email incorrecto: {$directorEmail} (esperado: administrativedirector@tvs.edu.co)");
                }
                
                // Probar getSectionEmails
                $sectionEmails = $service->getSectionEmails($casSection, $amount);
                if (count($sectionEmails) === 1 && $sectionEmails[0] === 'administrativedirector@tvs.edu.co') {
                    $this->info("       ✅ Section emails: [" . implode(', ', $sectionEmails) . "]");
                } else {
                    $this->error("       ❌ Section emails incorrecto: [" . implode(', ', $sectionEmails) . "] (esperado solo: administrativedirector@tvs.edu.co)");
                }
            }
        }
    }
    
    private function verifyOtherSectionsNotAffected($service, $testAmounts)
    {
        $this->line('📋 2. Verificando que otras secciones no se vean afectadas...');
        
        $otherSections = ['Académica', 'Primaria', 'PAI', 'Administración'];
        
        foreach ($otherSections as $section) {
            $this->line("   📚 Sección: {$section}");
            
            $amount = $testAmounts[0]; // Usar el primer monto para probar
            
            // Verificar que no se aplique la lógica de CAS
            $directorEmail = $service->getDirectorEmail($section, $amount);
            $sectionEmails = $service->getSectionEmails($section, $amount);
            
            if ($directorEmail !== 'administrativedirector@tvs.edu.co' || count($sectionEmails) !== 1 || $sectionEmails[0] !== 'administrativedirector@tvs.edu.co') {
                $this->info("     ✅ Comportamiento normal (no afectado por lógica CAS)");
                $this->line("       Director: {$directorEmail}");
                $this->line("       Section emails: [" . implode(', ', $sectionEmails) . "]");
            } else {
                $this->warn("     ⚠️  Esta sección también está siendo dirigida al director administrativo exclusivamente");
            }
        }
    }
}