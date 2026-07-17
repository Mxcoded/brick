<?php

namespace Modules\Frontdeskcrm\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Frontdeskcrm\Models\Registration;

class RegistrationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;

    public $headline;

    public $messageBody;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;

        // Dynamic Content based on Status
        switch ($registration->stay_status) {
            case 'checked_in':
                $this->headline = 'Welcome to Brickspoint!';
                $this->messageBody = 'You have successfully checked in. We wish you a pleasant stay.';
                break;
            case 'reserved':
                $this->headline = 'Reservation Confirmed';
                $this->messageBody = 'Your reservation has been secured. We look forward to your arrival.';
                break;
            default: // draft_by_guest
                $this->headline = 'Pre-Checkin Received';
                $this->messageBody = 'We have received your details. Please visit the front desk to collect your key.';
                break;
        }
    }

    public function build()
    {
        return $this->subject('Update: '.$this->headline)
            ->view('frontdeskcrm::emails.registration_status');
    }
}
