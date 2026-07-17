<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\Booking;

class PostStayFollowUp extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('We hope you enjoyed your stay - '.$this->booking->booking_reference)
            ->view('website::emails.post-stay-followup');
    }
}
