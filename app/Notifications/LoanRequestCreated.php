<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\LoanRequest;

class LoanRequestCreated extends Notification
{
    use Queueable;

    protected $loanRequest;
    protected $recipientType;

    public function __construct(LoanRequest $loanRequest, $recipientType)
    {
        $this->loanRequest = $loanRequest;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return $this->recipientType === 'applicant' 
            ? $this->toApplicantMail()
            : $this->toHrMail();
    }

    protected function toApplicantMail()
    {
        return (new MailMessage)
            ->subject('Solicitud de Préstamo Recibida')
            ->markdown('emails.loan-requests.created-applicant', [
                'loanRequest' => $this->loanRequest
            ]);
    }

    protected function toHrMail()
    {
        return (new MailMessage)
            ->subject('Nueva Solicitud de Préstamo para Revisión')
            ->markdown('emails.loan-requests.created-hr', [
                'loanRequest' => $this->loanRequest
            ]);
    }
}
