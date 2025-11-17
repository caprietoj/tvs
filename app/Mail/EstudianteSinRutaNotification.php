<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\IngresoEstudiante;

class EstudianteSinRutaNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ingreso;
    public $motivoSalida;

    /**
     * Create a new message instance.
     */
    public function __construct(IngresoEstudiante $ingreso, string $motivoSalida)
    {
        $this->ingreso = $ingreso;
        $this->motivoSalida = $motivoSalida;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Estudiante no tomará servicio de ruta - ' . $this->ingreso->estudiante,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.estudiante-sin-ruta',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
