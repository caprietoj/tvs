<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentRequest;

class DocumentRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    public function build()
    {
        return $this->subject('Solicitud de Documento Recibida')
                    ->view('emails.documentRequestCreated');
    }
}
