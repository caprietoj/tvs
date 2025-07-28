<?php

namespace App\Console\Commands;

use App\Models\FeedbackSession;
use App\Mail\FeedbackSessionScheduled;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestFeedbackSessionNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:test-notifications {session_id? : ID de la sesión de retroalimentación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el envío de notificaciones con Gmail Events para sesiones de retroalimentación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionId = $this->argument('session_id');
        
        if ($sessionId) {
            $feedbackSession = FeedbackSession::find($sessionId);
            
            if (!$feedbackSession) {
                $this->error("No se encontró la sesión de retroalimentación con ID: {$sessionId}");
                return 1;
            }
        } else {
            // Buscar la sesión más reciente
            $feedbackSession = FeedbackSession::with(['employee', 'supervisor', 'performanceEvaluation'])
                ->where('status', 'programada')
                ->latest()
                ->first();
                
            if (!$feedbackSession) {
                $this->error("No se encontraron sesiones de retroalimentación programadas para probar.");
                return 1;
            }
        }

        $this->info("Probando notificaciones para la sesión ID: {$feedbackSession->id}");
        $this->line("Colaborador: {$feedbackSession->employee->name}");
        $this->line("Supervisor: {$feedbackSession->supervisor->name}");
        $this->line("Fecha: {$feedbackSession->formatted_datetime}");
        $this->newLine();

        // Preguntar si quiere continuar
        if (!$this->confirm('¿Enviar notificaciones de prueba?')) {
            $this->info('Prueba cancelada.');
            return 0;
        }

        try {
            // Crear instancia del mailable para verificar la generación del ICS
            $this->info('Generando archivo ICS de prueba...');
            
            $mailable = new FeedbackSessionScheduled($feedbackSession, 'employee');
            $attachments = $mailable->attachments();
            
            if (!empty($attachments)) {
                $this->info('✓ Archivo ICS generado correctamente');
                $this->line("Archivos adjuntos: " . count($attachments));
                
                foreach ($attachments as $attachment) {
                    $this->line("- " . $attachment->as);
                }
            } else {
                $this->warn('⚠ No se generaron archivos adjuntos');
            }

            // Opción para enviar email de prueba
            if ($this->confirm('¿Enviar email de prueba a su correo?')) {
                $testEmail = $this->ask('Ingrese el email de prueba', 'jefesistemas@tvs.edu.co');
                
                $this->info("Enviando email de prueba a: {$testEmail}");
                
                Mail::to($testEmail)->send($mailable);
                
                $this->info('✓ Email de prueba enviado');
                $this->line('Verifique que el email contenga el archivo .ics adjunto');
                $this->line('Al hacer clic en el archivo debe abrirse su aplicación de calendario');
            }

            $this->newLine();
            $this->info('=== INSTRUCCIONES PARA VERIFICAR ===');
            $this->line('1. Abra el email recibido');
            $this->line('2. Verifique que hay un archivo adjunto .ics');
            $this->line('3. Haga clic en el archivo para agregarlo al calendario');
            $this->line('4. Confirme que el evento se creó con todos los detalles');
            $this->line('5. Verifique que los recordatorios estén configurados');

        } catch (\Exception $e) {
            $this->error('Error durante la prueba: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
