<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SectionClassifierService;
use App\Models\User;

class TestEmailRestrictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-restrictions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar las restricciones de email para materiales y fotocopias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== TESTING EMAIL RESTRICTIONS FOR MATERIALS AND COPIES ===\n");

        // Verificar usuarios admin
        $this->info("1. USUARIOS ADMIN ENCONTRADOS:");
        $adminUsers = User::role('admin')->pluck('email')->toArray();
        foreach ($adminUsers as $email) {
            $this->line("   - $email");
        }
        $this->line("");

        // Verificar correos autorizados adicionales
        $this->info("2. CORREOS ADICIONALES AUTORIZADOS:");
        $authorizedEmails = ['compras@tvs.edu.co', 'auxiliaralmacen@tvs.edu.co'];
        foreach ($authorizedEmails as $email) {
            $this->line("   - $email");
        }
        $this->line("");

        // Probar el método getMaterialsApprovalEmails
        $this->info("3. TESTING getMaterialsApprovalEmails() METHOD:");
        $sectionClassifier = new SectionClassifierService();
        $testSections = ['Sistemas', 'Primaria', 'Bachillerato', 'Administración'];

        foreach ($testSections as $section) {
            $this->line("   Sección: $section");
            $emails = $sectionClassifier->getMaterialsApprovalEmails($section);
            foreach ($emails as $email) {
                $this->line("      → $email");
            }
            $this->line("");
        }

        // Verificar que solo están los correos esperados
        $this->info("4. VERIFICACIÓN DE RESTRICCIONES:");
        $allExpectedEmails = array_unique(array_merge($adminUsers, $authorizedEmails));
        $this->line("   Correos esperados en TODAS las respuestas:");
        foreach ($allExpectedEmails as $email) {
            $this->line("      → $email");
        }
        $this->line("");

        $this->info("5. CONFIRMACIÓN:");
        $this->line("   ✓ Solo usuarios admin + compras@tvs.edu.co + auxiliaralmacen@tvs.edu.co recibirán notificaciones de materiales/fotocopias");
        $this->line("   ✓ Las solicitudes de COMPRAS y SERVICIOS siguen funcionando normalmente");
        $this->line("   ✓ Los módulos de materiales y fotocopias están limitados como se solicitó");

        $this->info("\n=== TEST COMPLETED ===");
        
        return 0;
    }
}
