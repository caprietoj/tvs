<?php

namespace App\Mail;

use App\Models\SpaceReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpaceReservationStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(SpaceReservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reserva de Espacio ' . $this->statusText() . ' - ' . $this->reservation->space->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.space-reservation-status',
            with: ['statusText' => $this->statusText()],
        );
    }

    protected function statusText(): string
    {
        return match ($this->reservation->status) {
            'approved' => 'APROBADA',
            'rejected' => 'RECHAZADA',
            'cancelled' => 'CANCELADA',
            default => 'ACTUALIZADA',
        };
    }
}
