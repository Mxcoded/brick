<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $apiToken;

    protected ?string $phoneNumberId;

    public function __construct()
    {
        $this->apiToken = config('services.whatsapp.api_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    public function send(string $recipient, string $message): array
    {
        if (! $this->isConfigured()) {
            Log::info('WhatsApp message queued (not configured)', [
                'to' => $recipient,
                'body' => $message,
            ]);

            return ['success' => true, 'message' => 'Logged (not configured)'];
        }

        // TODO: Replace with actual WhatsApp Cloud API call when credentials are configured.
        // $response = Http::withToken($this->apiToken)
        //     ->post("https://graph.facebook.com/v18.0/{$this->phoneNumberId}/messages", [
        //         'messaging_product' => 'whatsapp',
        //         'to' => $recipient,
        //         'type' => 'text',
        //         'text' => ['body' => $message],
        //     ]);

        Log::info('WhatsApp message sent via API stub', [
            'to' => $recipient,
            'phone_number_id' => $this->phoneNumberId,
        ]);

        return ['success' => true, 'message' => 'Sent via stub'];
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiToken) && ! empty($this->phoneNumberId);
    }
}
