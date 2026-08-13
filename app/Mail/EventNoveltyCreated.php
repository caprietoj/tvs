<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventNovelty;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventNoveltyCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public EventNovelty $novelty;

    public function __construct(Event $event, EventNovelty $novelty)
    {
        $this->event   = $event;
        $this->novelty = $novelty;
    }

    public function build(): static
    {
        return $this->markdown('emails.events.novelty_created')
                    ->subject("Nueva novedad en el evento: {$this->event->event_name} ({$this->event->consecutive})");
    }
}
