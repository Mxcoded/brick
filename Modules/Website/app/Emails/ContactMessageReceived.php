<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Inquiry from '.$this->data['name'])
            ->replyTo($this->data['email']) // Allow admin to reply directly to guest
            ->view('website::emails.contact-message');
    }
}
