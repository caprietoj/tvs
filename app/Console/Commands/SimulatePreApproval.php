<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\RequestHistory;
use App\Services\SectionClassifierService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class SimulatePreApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:preapproval {--request-id=5} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simular el proceso completo de pre-aprobación con notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requestId = $this->option('request-id');
        $dryRun = $this->option('dry-run');
        
        $this->info('=== SIMULACIÓN DE PRE-APROBACIÓN ===');
        $this->newLine();

        // Obtener la solicitud y la cotización
        $purchaseRequest = PurchaseRequest::find($requestId);
        if (!$purchaseRequest) {
            $this->error("No se encontró la solicitud con ID: $requestId");
            return 1;
        }

        $quotation = $purchaseRequest->quotations()->first();
        if (!$quotation) {
            $this->error("No se encontraron cotizaciones para la solicitud #{$requestId}");
            return 1;
        }

        $this->line("Solicitud: #{$purchaseRequest->id} - {$purchaseRequest->request_number}");
        $this->line("Sección: {$purchaseRequest->section_area}");
        $this->line("Estado actual: {$purchaseRequest->status}");
        $this->line("Cotización: {$quotation->provider_name} - $" . number_format($quotation->total_amount, 0, ',', '.'));
        $this->newLine();

        if ($dryRun) {
            $this->warn('MODO DRY-RUN: No se realizarán cambios en la base de datos ni se enviarán emails reales');
            $this->newLine();
        }

        // Simular el proceso de pre-aprobación
        $sectionClassifier = new SectionClassifierService();
        $directorEmail = $sectionClassifier->getDirectorEmail($purchaseRequest->section_area);
        $sectionEmails = $sectionClassifier->getSectionEmails($purchaseRequest->section_area);
        
        // Crear lista de todos los emails que deben ser notificados
        $allEmails = [];
        
        // Agregar director
        if ($directorEmail) {
            $allEmails[] = $directorEmail;
        }
        
        // Agregar emails específicos de la sección
        if (!empty($sectionEmails)) {
            $allEmails = array_merge($allEmails, $sectionEmails);
        }
        
        // Agregar compras@tvs.edu.co siempre
        $allEmails[] = 'compras@tvs.edu.co';
        
        // Eliminar duplicados
        $allEmails = array_unique($allEmails);

        $this->info('Emails que recibirán notificación:');
        foreach ($allEmails as $email) {
            $this->line("  - $email");
        }
        $this->newLine();

        if (!$dryRun) {
            // Realizar los cambios reales
            $this->info('Ejecutando pre-aprobación...');
            
            // Desmarcar cualquier otra cotización que pudiera estar seleccionada
            Quotation::where('purchase_request_id', $purchaseRequest->id)
                ->update(['is_selected' => false]);
                
            // Actualizar el estado de la cotización a pre-aprobada y marcarla como seleccionada
            $quotation->update([
                'status' => 'pre-approved',
                'is_selected' => true,
                'pre_approval_date' => now(),
                'pre_approval_comments' => 'Pre-aprobación de prueba',
                'pre_approved_by' => 1,
            ]);

            // Actualizar el estado de la solicitud a pre-aprobada
            $purchaseRequest->update([
                'status' => 'Pre-aprobada',
                'pre_approved_quotation_id' => $quotation->id,
                'pre_approved_by' => 1,
                'pre_approved_at' => now(),
                'budget' => 'Presupuesto de prueba'
            ]);

            // Registrar en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => 1,
                'action' => 'Pre-aprobación de cotización (SIMULACIÓN)',
                'notes' => 'Cotización de ' . $quotation->provider_name . ' pre-aprobada. Comentarios: Pre-aprobación de prueba',
            ]);

            $this->info('✓ Base de datos actualizada');

            // Cargar la solicitud con las relaciones necesarias para la notificación
            $purchaseRequest = $purchaseRequest->fresh(['user']);

            try {
                $this->info('Enviando notificaciones...');
                
                // Enviar notificaciones a todos los emails relevantes
                foreach ($allEmails as $email) {
                    $this->line("  Enviando a: $email");
                    Notification::route('mail', $email)
                        ->notify(new \App\Notifications\QuotationPreApproved($purchaseRequest, $email, $quotation));
                }
                
                // Notificar al solicitante por separado
                if ($purchaseRequest->user) {
                    $this->line("  Enviando al solicitante: {$purchaseRequest->user->email}");
                    $purchaseRequest->user->notify(new \App\Notifications\QuotationPreApproved($purchaseRequest, $purchaseRequest->user->email, $quotation));
                }
                
                $this->info('✓ Notificaciones enviadas exitosamente');
                
            } catch (\Exception $e) {
                $this->error('✗ Error al enviar notificaciones: ' . $e->getMessage());
                $this->line('Trace: ' . $e->getTraceAsString());
            }
        }

        $this->newLine();
        $this->info('=== VERIFICAR RESULTADOS ===');
        $this->line('1. Revisar logs en storage/logs/laravel.log');
        $this->line('2. Confirmar que los destinatarios recibieron los emails');
        $this->line('3. Verificar estado de la solicitud en la interfaz web');
        
        if (!$dryRun) {
            $this->newLine();
            $this->line("Estado final de la solicitud: {$purchaseRequest->fresh()->status}");
            $this->line("Cotización pre-aprobada: {$quotation->fresh()->provider_name}");
        }
        
        return 0;
    }
}
