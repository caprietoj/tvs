<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EquipmentLoanRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $loan;

    public function __construct($loan)
    {
        $this->loan = $loan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [config('mail.equipment_admin')],
            subject: 'Nueva Solicitud de Préstamo de Equipo'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.equipment.loan-requested',
        );
    }
}
