<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\Booking;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public $isStaffCopy;

    /**
     * Create a new message instance.
     *
     * @param  bool  $isStaffCopy  Whether this is a copy for staff/reservations team
     */
    public function __construct(Booking $booking, bool $isStaffCopy = false)
    {
        $this->booking = $booking;
        $this->isStaffCopy = $isStaffCopy;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isStaffCopy
            ? '[NEW BOOKING] '.$this->booking->booking_reference.' - '.$this->booking->guest_name
            : 'Booking Confirmation - '.$this->booking->booking_reference;

        return $this->subject($subject)
            ->view('website::emails.booking-confirmation');
    }
}
