<?php

namespace Modules\Staff\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Staff\Models\Employee;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no-reply@brickspoint.com'),
                config('mail.from.name', 'Brickspoint ERP'),
            ),
            subject: 'Welcome to Brickspoint — Staff Registration Successful',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'staff::emails.welcome',
            with: [
                'name' => $this->employee->name,
                'email' => $this->employee->email,
                'phone' => $this->employee->phone_number,
                'position' => $this->employee->position,
                'department' => $this->employee->department,
                'staffCode' => $this->employee->staff_code,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
