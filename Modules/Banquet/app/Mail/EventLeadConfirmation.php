<?php

namespace Modules\Banquet\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Banquet\Models\EventLead;
use Modules\Banquet\Models\LeadEvent;

class EventLeadConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventLead $lead,
        public LeadEvent $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no-reply@brickspoint.com'),
                config('mail.from.name', 'Brickspoint'),
            ),
            subject: "Registration Confirmed — {$this->event->title}",
        );
    }

    public function content(): Content
    {
        $body = $this->event->confirmation_email_body
            ?: "Hi {name},\n\nThank you for registering your interest for {event}.\n\nYour registration code is: **{code}**\n\nWe will be in touch with more details soon.\n\nBest regards,\nBrickspoint Team";

        $body = str_replace(
            ['{name}', '{event}', '{code}', '{date}', '{location}'],
            [$this->lead->name, $this->event->title, $this->event->code ?? '—', $this->event->event_date?->format('F j, Y') ?? '—', $this->event->location ?? '—'],
            $body
        );

        return new Content(
            view: 'banquet::emails.event-lead-confirmation',
            with: [
                'lead' => $this->lead,
                'event' => $this->event,
                'body' => nl2br(e($body)),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
