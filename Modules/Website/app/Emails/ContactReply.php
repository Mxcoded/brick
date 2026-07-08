<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\ContactMessageReply;

class ContactReply extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;

    public ContactMessageReply $reply;

    public string $staffName;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactMessage $contactMessage, ContactMessageReply $reply, string $staffName)
    {
        $this->contactMessage = $contactMessage;
        $this->reply = $reply;
        $this->staffName = $staffName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->contactMessage->subject
            ? 'Re: '.$this->contactMessage->subject
            : 'Re: Your message to Brickspoint';

        return new Envelope(
            subject: $subject,
            replyTo: [config('mail.from.address')],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'website::emails.contact-reply',
        );
    }
}
