<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $plainPassword;

    public function __construct(
        public User $user,
        string $plainPassword,
    ) {
        $this->plainPassword = $plainPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no-reply@brickspoint.com'),
                config('mail.from.name', 'Brickspoint ERP'),
            ),
            subject: 'Your Staff Account Has Been Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-created',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->plainPassword,
                'loginUrl' => route('login'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
