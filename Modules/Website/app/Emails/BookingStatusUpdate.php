<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\Booking;

class BookingStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public $statusLabel;

    public $isStaffCopy;

    /**
     * @param  string  $statusLabel  Human-readable status label (e.g. "Checked In", "Checkout Complete")
     */
    public function __construct(Booking $booking, string $statusLabel = '', bool $isStaffCopy = false)
    {
        $this->booking = $booking;
        $this->statusLabel = $statusLabel ?: ucfirst(str_replace('_', ' ', $booking->status));
        $this->isStaffCopy = $isStaffCopy;
    }

    public function build(): static
    {
        $subject = $this->isStaffCopy
            ? '[STATUS UPDATE] '.$this->booking->booking_reference.' — '.$this->statusLabel
            : $this->statusLabel.' — '.$this->booking->booking_reference;

        $view = $this->isStaffCopy
            ? 'website::emails.booking-status-update-staff'
            : 'website::emails.booking-status-update';

        return $this->subject($subject)
            ->view($view);
    }
}
