<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanRequestApproved extends Notification
{
    use Queueable;

    protected $loanRequest;
    protected $recipientType;
    protected $pdf;

    public function __construct(LoanRequest $loanRequest, $recipientType = 'applicant', $pdf = null)
    {
        $this->loanRequest = $loanRequest;
        $this->recipientType = $recipientType;
        $this->pdf = $pdf;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->recipientType === 'accounting') {
            return $this->toAccountingMail();
        }
        
        return $this->toApplicantMail();
    }

    protected function toApplicantMail()
    {
        return (new MailMessage)
            ->subject('Solicitud de Préstamo Aprobada')
            ->markdown('emails.loan-requests.approved', [
                'loanRequest' => $this->loanRequest
            ]);
    }

    protected function toAccountingMail()
    {
        // Generate PDF if not provided
        if (!$this->pdf) {
            $this->pdf = PDF::loadView('emails.loan-requests.pdf', [
                'loanRequest' => $this->loanRequest
            ]);
        }

        return (new MailMessage)
            ->subject('Préstamo Aprobado para Desembolso')
            ->markdown('emails.loan-requests.approved-accounting', [
                'loanRequest' => $this->loanRequest
            ])
            ->attachData(
                $this->pdf->output(),
                'prestamo-' . $this->loanRequest->id . '.pdf'
            );
    }
}
