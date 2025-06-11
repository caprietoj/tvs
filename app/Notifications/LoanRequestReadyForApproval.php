<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\LoanRequest;

class LoanRequestReadyForApproval extends Notification
{
    use Queueable;

    protected $loanRequest;

    public function __construct(LoanRequest $loanRequest)
    {
        $this->loanRequest = $loanRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Solicitud de Préstamo Lista para Aprobación')
            ->markdown('emails.loan-requests.pending-finance', [
                'loanRequest' => $this->loanRequest
            ]);
    }
}
