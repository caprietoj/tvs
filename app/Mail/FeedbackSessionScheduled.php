<?php

namespace App\Mail;

use App\Models\FeedbackSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class FeedbackSessionScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $feedbackSession;
    public $recipientType; // 'employee' o 'jefe_inmediato'

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
            : 'Confirmación: Sesión de Retroalimentación Programada con Colaborador';

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'intranet@tvs.edu.co'),
            using: [
                function ($message) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('Content-Class', 'urn:content-classes:calendarmessage');
                    $headers->addTextHeader('X-MS-OLK-FORCEINSPECTOROPEN', 'TRUE');
                    $headers->addTextHeader('X-Microsoft-CDO-Busystatus', 'BUSY');
                    $headers->addTextHeader('X-Microsoft-CDO-Importance', '1');
                }
            ]
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
                'evaluationPeriod' => $this->feedbackSession->evaluation_period,
                'icsContent' => $this->generateIcsContent()
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $icsContent = $this->generateIcsContent();
        
        // Crear archivo temporal
        $fileName = 'sesion_retroalimentacion_' . $this->feedbackSession->id . '.ics';
        $tempPath = storage_path('app/temp/' . $fileName);
        
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        file_put_contents($tempPath, $icsContent);
        
        return [
            Attachment::fromPath($tempPath)
                ->as('invite.ics')
                ->withMime('text/calendar')
        ];
    }

    /**
     * Generar contenido ICS para el evento de calendario
     */
    private function generateIcsContent(): string
    {
        $feedbackSession = $this->feedbackSession;
        $startDateTime = $feedbackSession->scheduled_datetime;
        $endDateTime = $startDateTime->copy()->addMinutes(60);
        
        // Formatear fechas en UTC
        $dtStart = $startDateTime->utc()->format('Ymd\THis\Z');
        $dtEnd = $endDateTime->utc()->format('Ymd\THis\Z');
        $dtStamp = now()->utc()->format('Ymd\THis\Z');
        
        // UID simple y único
        $uid = uniqid('feedback-' . $feedbackSession->id . '-') . '@tvs.edu.co';
        
        $employeeName = $feedbackSession->employee->name;
        $supervisorName = $feedbackSession->supervisor->name;
        $location = $feedbackSession->location ?: 'Por definir';
        
        // ICS ultra-simplificado
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//The Victoria School//EN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTART:{$dtStart}\r\n";
        $ics .= "DTEND:{$dtEnd}\r\n";
        $ics .= "DTSTAMP:{$dtStamp}\r\n";
        $ics .= "SUMMARY:Sesión de Retroalimentación - {$employeeName}\r\n";
        $ics .= "DESCRIPTION:Sesión de retroalimentación programada\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= "ORGANIZER:MAILTO:{$feedbackSession->supervisor->email}\r\n";
        $ics .= "ATTENDEE;RSVP=TRUE:MAILTO:{$feedbackSession->employee->email}\r\n";
        $ics .= "ATTENDEE:MAILTO:{$feedbackSession->supervisor->email}\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }

    /**
     * Generar descripción detallada del evento
     */
    private function generateEventDescription(): string
    {
        $evaluation = $this->feedbackSession->performanceEvaluation;
        
        $description = "SESIÓN DE RETROALIMENTACIÓN - EVALUACIÓN DE DESEMPEÑO\\n\\n";
        $description .= "Colaborador: " . $this->feedbackSession->employee->name . "\\n";
        $description .= "Jefe Inmediato: " . $this->feedbackSession->supervisor->name . "\\n";
        $description .= "Período de Evaluación: " . $this->feedbackSession->evaluation_period . "\\n\\n";
        
        if ($this->feedbackSession->description) {
            $description .= "Agenda/Descripción:\\n" . $this->feedbackSession->description . "\\n\\n";
        }
        
        $description .= "OBJETIVOS DE LA SESIÓN:\\n";
        $description .= "• Revisar resultados de la evaluación de desempeño\\n";
        $description .= "• Discutir fortalezas y áreas de mejora identificadas\\n";
        $description .= "• Establecer metas y planes de desarrollo\\n";
        $description .= "• Brindar orientación para el crecimiento profesional\\n\\n";
        
        $description .= "Esta es una reunión importante para el desarrollo profesional. ";
        $description .= "Se recomienda revisar la evaluación completa antes de la sesión.\\n\\n";
        
        $description .= "Sistema de Evaluación de Desempeño - The Victoria School";
        
        return $description;
    }

    /**
     * Escapar texto para formato ICS
     */
    private function escapeIcsText($text): string
    {
        if (empty($text)) {
            return '';
        }
        
        // Escapar caracteres especiales según RFC 5545
        $text = str_replace(['\\', ',', ';', "\n", "\r"], ['\\\\', '\\,', '\\;', '\\n', ''], $text);
        
        // Limitar longitud de líneas a 75 caracteres según RFC
        $lines = [];
        $currentLine = '';
        $words = explode(' ', $text);
        
        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) > 70) {
                if (!empty($currentLine)) {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $lines[] = $word;
                }
            } else {
                $currentLine .= empty($currentLine) ? $word : ' ' . $word;
            }
        }
        
        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }
        
        return implode('\\n ', $lines);
    }
}
