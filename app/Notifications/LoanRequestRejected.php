<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanRequestRejected extends Notification
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
            ->subject('Solicitud de Préstamo No Aprobada')
            ->markdown('emails.loan-requests.rejected', [
                'loanRequest' => $this->loanRequest,
                'user' => $notifiable
            ]);
    }
}
