<?php

namespace Modules\Website\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Website\Models\Testimonial;

class ReviewSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $testimonial;

    public function __construct(Testimonial $testimonial)
    {
        $this->testimonial = $testimonial;
    }

    public function build()
    {
        $subject = 'Thank you for your review, '.$this->testimonial->guest_name.'!';

        return $this->subject($subject)
            ->view('website::emails.review-submitted');
    }
}
