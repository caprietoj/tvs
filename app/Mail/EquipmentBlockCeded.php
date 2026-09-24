<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EquipmentBlockCeded extends Mailable
{
    use Queueable, SerializesModels;

    public array $details;

    /**
     * @param array $details {
     *   space_name: string,
     *   date: string,
     *   start_time: string,
     *   end_time: string,
     *   original_teacher: ?string,
     *   new_teacher: string,
     *   ceded_by: string,
     * }
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sala de informática cedida - ' . ($this->details['space_name'] ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.equipment.block-ceded',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
