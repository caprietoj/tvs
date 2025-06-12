<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Services\SectionClassifierService;
use Illuminate\Support\Facades\Notification;

class TestPreApprovalFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:preapproval {--request-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el flujo de notificaciones de pre-aprobación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DE FLUJO DE PRE-APROBACIÓN ===');
        $this->newLine();

        $requestId = $this->option('request-id');
        
        if (!$requestId) {
            // Buscar una solicitud de prueba
            $purchaseRequest = PurchaseRequest::where('section_area', 'PAI')
                ->where('status', 'En Cotización')
                ->first();
                
            if (!$purchaseRequest) {
                $this->error('No se encontró ninguna solicitud de PAI en estado "En Cotización"');
                $this->line('Creando una solicitud de prueba...');
                
                // Crear una solicitud de prueba
                $purchaseRequest = PurchaseRequest::create([
                    'request_number' => 'TEST-' . time(),
                    'requester' => 'Usuario de Prueba',
                    'section_area' => 'PAI',
                    'status' => 'En Cotización',
                    'request_date' => now(),
                    'type' => 'purchase',
                    'user_id' => 1, // Asumiendo que existe el usuario con ID 1
                ]);
                
                $this->info("Solicitud de prueba creada con ID: {$purchaseRequest->id}");
            }
        } else {
            $purchaseRequest = PurchaseRequest::find($requestId);
            if (!$purchaseRequest) {
                $this->error("No se encontró la solicitud con ID: $requestId");
                return 1;
            }
        }

        $this->line("Usando solicitud: #{$purchaseRequest->id} - {$purchaseRequest->request_number}");
        $this->line("Sección: {$purchaseRequest->section_area}");
        $this->line("Estado: {$purchaseRequest->status}");
        $this->newLine();

        // Simular el flujo de pre-aprobación
        $sectionClassifier = new SectionClassifierService();
        $directorEmail = $sectionClassifier->getDirectorEmail($purchaseRequest->section_area);
        $sectionEmails = $sectionClassifier->getSectionEmails($purchaseRequest->section_area);
        
        // Crear lista de todos los emails que deben ser notificados
        $allEmails = [];
        
        // Agregar director
        if ($directorEmail) {
            $allEmails[] = $directorEmail;
            $this->line("✓ Director añadido: $directorEmail");
        }
        
        // Agregar emails específicos de la sección
        if (!empty($sectionEmails)) {
            $allEmails = array_merge($allEmails, $sectionEmails);
            $this->line("✓ Emails de sección añadidos: " . implode(', ', $sectionEmails));
        }
        
        // Agregar compras@tvs.edu.co siempre
        $allEmails[] = 'compras@tvs.edu.co';
        $this->line("✓ Compras añadido: compras@tvs.edu.co");
        
        // Eliminar duplicados
        $allEmails = array_unique($allEmails);
        
        $this->newLine();
        $this->info('Lista final de emails que recibirán notificación:');
        foreach ($allEmails as $email) {
            $this->line("  - $email");
        }
        
        $this->newLine();
        $this->line('Clasificación de sección: ' . $sectionClassifier->classifySection($purchaseRequest->section_area));
        
        // Verificar si el usuario existe
        if ($purchaseRequest->user) {
            $this->line("✓ Solicitante: {$purchaseRequest->user->name} ({$purchaseRequest->user->email})");
        } else {
            $this->warn("⚠ No se encontró usuario asociado a la solicitud");
        }
        
        $this->newLine();
        $this->info('=== VERIFICAR LOGS ===');
        $this->line('Para verificar si las notificaciones se están enviando, revisar:');
        $this->line('- storage/logs/laravel.log');
        $this->line('- Buscar por: "Enviando notificación de pre-aprobación"');
        
        return 0;
    }
}
