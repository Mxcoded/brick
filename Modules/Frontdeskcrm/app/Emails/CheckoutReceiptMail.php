<?php

namespace Modules\Frontdeskcrm\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Frontdeskcrm\Models\Registration;

class CheckoutReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;

    public $pdfOutput;

    public function __construct(Registration $registration, string $pdfOutput)
    {
        $this->registration = $registration;
        $this->pdfOutput = $pdfOutput;
    }

    public function build()
    {
        $mail = $this->subject('Your Receipt from Brickspoint Boutique Aparthotel')
            ->view('frontdeskcrm::emails.checkout_receipt');

        if ($this->pdfOutput) {
            $mail->attachData($this->pdfOutput, 'invoice-'.$this->registration->reservation_code.'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
