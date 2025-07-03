<?php

namespace App\Notifications;

use App\Models\SpaceReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpaceReservationStatusChanged extends Notification
{
    use Queueable;

    protected $spaceReservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(SpaceReservation $spaceReservation)
    {
        $this->spaceReservation = $spaceReservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->getStatusText();
        $space = $this->spaceReservation->space;
        $date = \Carbon\Carbon::parse($this->spaceReservation->date)->format('d/m/Y');
        $startTime = \Carbon\Carbon::parse($this->spaceReservation->start_time)->format('H:i');
        $endTime = \Carbon\Carbon::parse($this->spaceReservation->end_time)->format('H:i');

        return (new MailMessage)
            ->subject("Actualización de Reserva de Espacio: {$statusText}")
            ->greeting("Hola {$notifiable->name},")
            ->line("El estado de su reserva para el espacio '{$space->name}' ha sido actualizado a: {$statusText}.")
            ->line("Detalles de la reserva:")
            ->line("- Fecha: {$date}")
            ->line("- Horario: {$startTime} - {$endTime}")
            ->line("- Propósito: {$this->spaceReservation->purpose}")
            ->action('Ver Detalles', url(route('space-reservations.show', $this->spaceReservation)))
            ->line('Gracias por utilizar nuestro sistema de reservas.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = $this->getStatusText();
        return [
            'space_reservation_id' => $this->spaceReservation->id,
            'space_name' => $this->spaceReservation->space->name,
            'status' => $this->spaceReservation->status,
            'status_text' => $statusText,
            'date' => $this->spaceReservation->date,
            'message' => "Su reserva para {$this->spaceReservation->space->name} ha sido {$statusText}."
        ];
    }

    /**
     * Get the status text based on the current status
     */
    protected function getStatusText(): string
    {
        switch ($this->spaceReservation->status) {
            case 'approved':
                return 'APROBADA';
            case 'rejected':
                return 'RECHAZADA';
            case 'cancelled':
                return 'CANCELADA';
            default:
                return 'ACTUALIZADA';
        }
    }
}
