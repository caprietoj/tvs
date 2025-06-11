<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentRequest;

class DocumentResolved extends Mailable
{
    use Queueable, SerializesModels;

    public $documentRequest;
    public $certificate; // certificate file path

    public function __construct(DocumentRequest $documentRequest, $certificate)
    {
        $this->documentRequest = $documentRequest;
        $this->certificate = $certificate;
    }

    public function build()
    {
        $email = $this->subject('Solicitud Resuelta: Certificado Adjuntado')
                      ->view('emails.document_resolved')
                      ->with(['documentRequest' => $this->documentRequest]);
        
        if ($this->certificate) {
            $email->attach(storage_path('app/public/' . $this->certificate));
        }

        return $email;
    }
}
