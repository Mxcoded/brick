<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\Booking;

class BookingCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public $isStaffCopy;

    public function __construct(Booking $booking, bool $isStaffCopy = false)
    {
        $this->booking = $booking;
        $this->isStaffCopy = $isStaffCopy;
    }

    public function build()
    {
        $subject = $this->isStaffCopy
            ? '[CANCELLED] '.$this->booking->booking_reference.' - '.$this->booking->guest_name
            : 'Booking Cancelled - '.$this->booking->booking_reference;

        $view = $this->isStaffCopy
            ? 'website::emails.booking-cancellation-staff'
            : 'website::emails.booking-cancellation';

        return $this->subject($subject)
            ->view($view);
    }
}
