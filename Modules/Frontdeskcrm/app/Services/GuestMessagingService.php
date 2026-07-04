<?php

namespace Modules\Frontdeskcrm\Services;

use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestMessage;
use Modules\Frontdeskcrm\Models\MessageTemplate;
use Modules\Frontdeskcrm\Models\Registration;

class GuestMessagingService
{
    public function sendFromTemplate(Registration $registration, string $event, string $channel = 'email'): ?GuestMessage
    {
        $template = MessageTemplate::active()->forEvent($event)->first();
        if (!$template) {
            return null;
        }

        $guest = $registration->guest;
        $placeholders = $this->buildPlaceholders($registration);

        $body = $this->interpolate($template->{$channel . '_body'} ?? $template->email_body, $placeholders);
        $subject = $this->interpolate($template->email_subject, $placeholders);

        $recipient = match ($channel) {
            'sms', 'whatsapp' => $guest->contact_number,
            default => $guest->email,
        };

        if (!$recipient) {
            return null;
        }

        $message = GuestMessage::create([
            'registration_id' => $registration->id,
            'guest_id' => $guest->id,
            'template_id' => $template->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
        ]);

        try {
            $this->dispatch($channel, $recipient, $subject, $body);
            $message->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $message->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }

        return $message;
    }

    public function sendRaw(Registration $registration, string $channel, string $recipient, string $subject, string $body): GuestMessage
    {
        $message = GuestMessage::create([
            'registration_id' => $registration->id,
            'guest_id' => $registration->guest_id,
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
        ]);

        try {
            $this->dispatch($channel, $recipient, $subject, $body);
            $message->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $message->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }

        return $message;
    }

    private function dispatch(string $channel, string $recipient, ?string $subject, string $body): void
    {
        switch ($channel) {
            case 'email':
                \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($recipient, $subject) {
                    $message->to($recipient)->subject($subject ?? 'Message from Brickspoint');
                });
                break;

            case 'sms':
                $sms = app(\Modules\Staff\Services\BulkSmsNigeria::class);
                $sms->send($recipient, $body);
                break;

            case 'whatsapp':
                app(\App\Services\WhatsAppService::class)->send($recipient, $body);
                break;
        }
    }

    private function buildPlaceholders(Registration $registration): array
    {
        $guest = $registration->guest;

        return [
            '{{guest_name}}' => $guest->full_name,
            '{{guest_title}}' => $guest->title ?? '',
            '{{reservation_code}}' => $registration->reservation_code,
            '{{hotel_name}}' => config('app.name'),
            '{{check_in}}' => $registration->check_in?->format('M d, Y') ?? '',
            '{{check_out}}' => $registration->check_out?->format('M d, Y') ?? '',
            '{{room_type}}' => $registration->roomType?->name ?? '',
            '{{room_number}}' => $registration->roomUnit?->room_number ?? '',
            '{{no_of_nights}}' => (string)($registration->no_of_nights ?? ''),
            '{{total_amount}}' => number_format($registration->total_amount ?? 0, 2),
            '{{pre_arrival_link}}' => $registration->pre_arrival_token
                ? route('guest.pre-arrival.token', $registration->pre_arrival_token)
                : '',
        ];
    }

    private function interpolate(?string $text, array $placeholders): string
    {
        if (!$text) {
            return '';
        }
        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }
}
