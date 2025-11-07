<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class EnfermeriaReporteSent extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $reportType;
    public $dateRange;
    public $totalRecords;
    public $excelPath;

    /**
     * Create a new message instance.
     */
    public function __construct($recipientName, $reportType, $dateRange, $totalRecords, $excelPath)
    {
        $this->recipientName = $recipientName;
        $this->reportType = $reportType;
        $this->dateRange = $dateRange;
        $this->totalRecords = $totalRecords;
        $this->excelPath = $excelPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte de Enfermería - ' . $this->reportType,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.enfermeria-reporte',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->excelPath)
                ->as('Reporte_Enfermeria_' . $this->reportType . '_' . date('Y-m-d') . '.xlsx')
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
