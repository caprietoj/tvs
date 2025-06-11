<?php

namespace App\Mail;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanRequestFinalized extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The loan request instance.
     */
    public $loanRequest;
    
    /**
     * Whether the loan was approved.
     */
    public $isApproved;
    
    /**
     * The recipient type.
     */
    public $recipientType;

    /**
     * Create a new message instance.
     */
    public function __construct(LoanRequest $loanRequest, bool $isApproved, string $recipientType = 'hr')
    {
        $this->loanRequest = $loanRequest;
        $this->isApproved = $isApproved;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->isApproved ? 'Aprobada' : 'Rechazada';
        return new Envelope(
            subject: "Solicitud de Préstamo {$status} - {$this->loanRequest->full_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-requests.finalized',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
