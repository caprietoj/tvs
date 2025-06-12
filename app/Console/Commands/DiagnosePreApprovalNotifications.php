<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SectionClassifierService;

class DiagnosePreApprovalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:preapproval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnosticar configuración de notificaciones de pre-aprobación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DIAGNÓSTICO DE NOTIFICACIONES DE PRE-APROBACIÓN ===');
        $this->newLine();

        $classifier = new SectionClassifierService();

        // Simular secciones de ejemplo
        $testSections = [
            'PAI',
            'Primaria', 
            'Pre Escolar',
            'Bachillerato',
            'Administración',
            'Sistemas',
            'Diploma'
        ];

        foreach ($testSections as $section) {
            $this->info("Sección: $section");
            
            try {
                $directorEmail = $classifier->getDirectorEmail($section);
                $this->line("  - Director: $directorEmail");
                
                $sectionEmails = $classifier->getSectionEmails($section);
                $emailsList = empty($sectionEmails) ? 'NINGUNO' : implode(', ', $sectionEmails);
                $this->line("  - Emails específicos: $emailsList");
                
                $classification = $classifier->classifySection($section);
                $this->line("  - Clasificación: $classification");
            } catch (\Exception $e) {
                $this->error("  - ERROR: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        $this->info('=== CONFIGURACIÓN ACTUAL ===');
        $this->line('Secciones configuradas:');
        $sections = config('section_emails.sections', []);
        foreach ($sections as $key => $value) {
            $valueStr = is_array($value) ? implode(', ', $value) : $value;
            $this->line("  - $key: $valueStr");
        }

        $this->newLine();
        $this->line('Directores configurados:');
        $directors = config('section_emails.directors', []);
        foreach ($directors as $type => $email) {
            $this->line("  - $type: $email");
        }

        $this->newLine();
        $this->line('Siempre notificar:');
        $alwaysNotify = config('section_emails.always_notify', []);
        foreach ($alwaysNotify as $email) {
            $this->line("  - $email");
        }

        $this->newLine();
        $this->info('=== SIMULACIÓN DE NOTIFICACIÓN PARA PAI ===');
        $sectionName = 'PAI';
        try {
            $directorEmail = $classifier->getDirectorEmail($sectionName);
            $sectionEmails = $classifier->getSectionEmails($sectionName);

            $allEmails = [];
            if ($directorEmail) {
                $allEmails[] = $directorEmail;
            }
            if (!empty($sectionEmails)) {
                $allEmails = array_merge($allEmails, $sectionEmails);
            }
            $allEmails[] = 'compras@tvs.edu.co';
            $allEmails = array_unique($allEmails);

            $this->line("Emails que recibirían notificación para sección '$sectionName':");
            foreach ($allEmails as $email) {
                $this->line("  - $email");
            }
        } catch (\Exception $e) {
            $this->error("ERROR en simulación: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('=== FIN DEL DIAGNÓSTICO ===');
        
        return 0;
    }
}
