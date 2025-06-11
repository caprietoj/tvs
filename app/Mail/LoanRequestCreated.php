<?php

namespace App\Mail;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The loan request instance.
     */
    public $loanRequest;

    /**
     * The email template.
     */
    protected $template;

    /**
     * Create a new message instance.
     */
    public function __construct(LoanRequest $loanRequest, string $template)
    {
        $this->loanRequest = $loanRequest;
        $this->template = $template;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view("emails.loan-requests.{$this->template}")
                   ->subject('Nueva Solicitud de Préstamo');
    }
}
