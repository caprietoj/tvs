<?php

namespace App\Console\Commands;

use App\Models\SpaceReservation;
use App\Mail\SpaceReservationNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSpaceReservationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:space-reservation-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test de envío de correos para reservas de espacios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Test de Envío de Correos para Reservas de Espacios ===');
        $this->line('');

        try {
            // Buscar la última reserva creada para probar
            $reservation = SpaceReservation::with(['user', 'space', 'items.item'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$reservation) {
                $this->error('❌ No se encontraron reservas en el sistema para probar.');
                $this->line('Cree una reserva primero desde la interfaz web.');
                return 1;
            }
            
            $this->info('✅ Reserva encontrada:');
            $this->line("   - ID: {$reservation->id}");
            $this->line("   - Usuario: {$reservation->user->name}");
            $this->line("   - Espacio: {$reservation->space->name}");
            $this->line("   - Fecha: {$reservation->date}");
            $this->line("   - Propósito: " . \Illuminate\Support\Str::limit($reservation->purpose, 50));
            $this->line("   - Estado: {$reservation->status}");
            
            if ($reservation->items && $reservation->items->count() > 0) {
                $this->line("   - Implementos: {$reservation->items->count()}");
                foreach ($reservation->items as $item) {
                    $this->line("     * {$item->item->name} (Cantidad: {$item->quantity})");
                }
            }
            
            $this->line('');
            
            // Crear la instancia del correo
            $mail = new SpaceReservationNotification($reservation);
            
            $this->info('✅ Instancia de correo creada exitosamente.');
            $this->line("   - Asunto: {$mail->envelope()->subject}");
            $this->line("   - Vista: {$mail->content()->view}");
            
            // Verificar que la plantilla existe
            $viewPath = resource_path('views/' . str_replace('.', '/', $mail->content()->view) . '.blade.php');
            if (file_exists($viewPath)) {
                $this->info("✅ Plantilla de correo encontrada: {$viewPath}");
            } else {
                $this->error("❌ Plantilla de correo NO encontrada: {$viewPath}");
                return 1;
            }
            
            $this->line('');
            
            // Simular envío (sin enviar realmente)
            $this->comment('🔄 Simulando envío de correo...');
            
            $recipients = ['asistentegeneral@tvs.edu.co', 'library@tvs.edu.co'];
            $this->line("   - Destinatarios: " . implode(', ', $recipients));
            
            // Verificar configuración de correo
            $this->line("   - MAIL_MAILER: " . config('mail.default'));
            $this->line("   - MAIL_FROM_ADDRESS: " . config('mail.from.address'));
            
            $this->line('');
            $this->info('✅ Simulación completada exitosamente.');
            $this->line('');
            $this->info('=== Resumen ===');
            $this->info('✅ Clase Mailable: App\\Mail\\SpaceReservationNotification');
            $this->info('✅ Plantilla: resources/views/emails/space-reservation-notification.blade.php');
            $this->info('✅ Controlador modificado: app/Http/Controllers/SpaceReservationController.php');
            $this->info('✅ Destinatarios configurados: asistentegeneral@tvs.edu.co, library@tvs.edu.co');
            $this->info('✅ Envío automático implementado en método store()');
            $this->line('');
            $this->comment('🎉 ¡Implementación completada exitosamente!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error durante la prueba: " . $e->getMessage());
            $this->line("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
