<?php

namespace Modules\Banquet\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Banquet\Models\BanquetEnquiry;

class NewEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BanquetEnquiry $enquiry) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Banquet Enquiry: '.$this->enquiry->name.' - '.$this->enquiry->event_type)
            ->greeting('New Meeting Enquiry Received')
            ->line('A new banquet enquiry has been submitted through the website.')
            ->line('**Contact:** '.$this->enquiry->name)
            ->line('**Email:** '.$this->enquiry->email)
            ->line('**Phone:** '.$this->enquiry->phone)
            ->line('**Company:** '.($this->enquiry->company ?? 'N/A'))
            ->line('**Event Type:** '.$this->enquiry->event_type)
            ->line('**Event Date:** '.$this->enquiry->event_date->format('l, F d, Y'))
            ->line('**Guests:** '.$this->enquiry->guest_count)
            ->action('View Enquiry', route('banquet.enquiries.show', $this->enquiry->id));
    }
}
