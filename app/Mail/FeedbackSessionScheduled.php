<?php

namespace App\Mail;

use App\Models\FeedbackSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackSessionScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $feedbackSession;
    public $recipientType; // 'employee' o 'supervisor'

    /**
     * Create a new message instance.
     */
    public function __construct(FeedbackSession $feedbackSession, string $recipientType)
    {
        $this->feedbackSession = $feedbackSession;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'employee' 
            ? 'Sesión de Retroalimentación Programada - Evaluación de Desempeño'
            : 'Confirmación: Sesión de Retroalimentación Programada';

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'intranet@tvs.edu.co')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback-session-scheduled',
            with: [
                'feedbackSession' => $this->feedbackSession,
                'recipientType' => $this->recipientType,
                'evaluation' => $this->feedbackSession->performanceEvaluation,
                'employee' => $this->feedbackSession->employee,
                'supervisor' => $this->feedbackSession->supervisor,
                'scheduledDate' => $this->feedbackSession->scheduled_datetime->format('d/m/Y'),
                'scheduledTime' => $this->feedbackSession->scheduled_datetime->format('H:i'),
                'evaluationPeriod' => $this->feedbackSession->evaluation_period
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
