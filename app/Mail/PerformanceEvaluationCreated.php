<?php

namespace App\Mail;

use App\Models\PerformanceEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PerformanceEvaluationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluation;

    /**
     * Create a new message instance.
     */
    public function __construct(PerformanceEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Evaluación de Desempeño Asignada - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.performance-evaluation-created',
            with: [
                'userName' => $this->evaluation->user->name,
                'evaluationType' => $this->evaluation->evaluation_type === 'periodo_prueba' ? 'Período de Prueba' : 'Evaluación Periódica',
                'evaluationPeriodStart' => $this->evaluation->evaluation_period_start->format('d/m/Y'),
                'evaluationPeriodEnd' => $this->evaluation->evaluation_period_end->format('d/m/Y'),
                'evaluationUrl' => route('performance-evaluations.self-evaluate', $this->evaluation->id),
                'evaluationId' => $this->evaluation->id,
            ],
        );
    }
}
