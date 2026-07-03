<?php

namespace Modules\Maintenance\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Maintenance\Models\MaintenanceLog;

class MaintenanceNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The notification type: 'new' or 'status_update'
     */
    public string $notificationType;

    /**
     * The previous status (for status updates)
     */
    public ?string $previousStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public MaintenanceLog $log,
        string $notificationType = 'new',
        ?string $previousStatus = null
    ) {
        $this->notificationType = $notificationType;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->notificationType === 'new'
            ? 'New Maintenance Request: ' . $this->log->location
            : 'Maintenance Status Update: ' . $this->log->location;

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no-reply@brickspoint.com'),
                config('mail.from.name', 'Brickspoint Maintenance')
            ),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'maintenance::emails.notification',
            with: [
                'log' => $this->log,
                'notificationType' => $this->notificationType,
                'previousStatus' => $this->previousStatus,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
