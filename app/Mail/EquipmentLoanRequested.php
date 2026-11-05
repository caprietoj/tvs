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
        $recipients = [config('mail.equipment_admin')];

        if ($this->isLibraryLoan()) {
            $recipients[] = config('mail.library_admin');
        }

        return new Envelope(
            to: array_values(array_unique(array_filter($recipients))),
            subject: 'Nueva Solicitud de Préstamo de Equipo'
        );
    }

    /**
     * Determina si el préstamo corresponde a un espacio de biblioteca.
     */
    protected function isLibraryLoan(): bool
    {
        if (($this->loan->section ?? null) === 'biblioteca_sala_computadores') {
            return true;
        }

        $spaceName = optional(optional($this->loan->equipment)->space)->name
            ?? $this->loan->grade
            ?? '';

        return stripos($spaceName, 'biblioteca') !== false;
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.equipment.loan-requested',
        );
    }
}
