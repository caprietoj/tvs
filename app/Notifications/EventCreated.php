<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        try {
            \Log::debug('Building email notification', [
                'event' => $this->event->title,
                'recipient' => $notifiable,
                'mail_config' => [
                    'from' => config('mail.from.address'),
                    'host' => config('mail.mailers.smtp.host'),
                ]
            ]);

            return (new MailMessage)
                ->subject('Nuevo Evento TVS: ' . $this->event->title)
                ->greeting('¡Hola!')
                ->line('Se ha creado un nuevo evento que requiere su atención.')
                ->line('Detalles del evento:')
                ->line("📅 Fecha: " . $this->event->service_date->format('d/m/Y H:i'))
                ->line("📍 Lugar: " . $this->event->location)
                ->line("📝 Descripción: " . $this->event->description)
                ->action('Ver Detalles del Evento', route('events.show', $this->event))
                ->line('Por favor revise los detalles y confirme su asistencia.')
                ->salutation('Saludos cordiales');
        } catch (\Exception $e) {
            \Log::error('Error creating mail notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
