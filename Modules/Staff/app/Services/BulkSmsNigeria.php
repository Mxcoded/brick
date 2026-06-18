<?php

namespace Modules\Staff\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSmsNigeria
{
    protected string $apiToken;

    protected string $sender;

    protected string $baseUrl;

    protected bool $verifySsl;

    public function __construct()
    {
        $this->apiToken = config('services.bulksmsnigeria.api_token');
        $this->sender = config('services.bulksmsnigeria.sender', 'Brickspoint');
        $this->baseUrl = config('services.bulksmsnigeria.base_url', 'https://www.bulksmsnigeria.com/api/sandbox/v2');
        $this->verifySsl = config('services.bulksmsnigeria.verify_ssl', false);
    }

    public function send(string $message, string|array $recipients): array
    {
        if (empty($this->apiToken)) {
            Log::warning('BulkSMSNigeria API token not configured.');

            return $this->result(false, 'API token not configured.');
        }

        if (is_array($recipients)) {
            $recipients = implode(',', $recipients);
        }

        $endpoint = $this->baseUrl.'/sms/create';

        $payload = [
            'from' => $this->sender,
            'body' => $message,
            'to' => $recipients,
        ];

        $response = $this->http()->post($endpoint, $payload);

        if ($response->status() === 400 || $response->status() === 401) {
            $altPayload = array_merge($payload, [
                'sender' => $this->sender,
                'message' => $message,
                'recipients' => $recipients,
            ]);
            $response = $this->http()->post($endpoint, $altPayload);
        }

        if ($response->status() === 401) {
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->asForm()
                ->post($endpoint, array_merge(
                    ['api_token' => $this->apiToken],
                    $payload
                ));
        }

        $body = $response->json() ?? [];
        $httpStatus = $response->status();

        Log::debug('BulkSMSNigeria send response', [
            'http_status' => $httpStatus,
            'body' => $body,
            'recipients' => $recipients,
        ]);

        if ($response->failed()) {
            $errorMsg = $body['message'] ?? ($body['error'] ?? "HTTP {$httpStatus}");
            Log::error('BulkSMSNigeria request failed', [
                'http_status' => $httpStatus,
                'body' => $body,
            ]);

            return $this->result(false, $errorMsg, $body);
        }

        if ($this->isSuccess($body)) {
            return $this->result(true, $body['message'] ?? 'SMS sent successfully', $body);
        }

        Log::warning('BulkSMSNigeria returned unexpected response', ['body' => $body]);

        return $this->result(false, $body['message'] ?? 'Unexpected API response', $body);
    }

    public function getBalance(): array
    {
        if (empty($this->apiToken)) {
            return $this->result(false, 'API token not configured.');
        }

        $response = $this->http()->get($this->baseUrl.'/balance');

        $body = $response->json() ?? [];

        if ($response->failed()) {
            return $this->result(false, 'Failed to fetch balance.', $body);
        }

        $balance = $body['data']['balance'] ?? null;

        return $this->result(true, 'Balance retrieved', $body, ['balance' => $balance]);
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);

        if (preg_match('/^\+?234/', $phone)) {
            return ltrim($phone, '+');
        }

        if (preg_match('/^0([7-9][0-1][0-9]{8})$/', $phone, $m)) {
            return '234'.$m[1];
        }

        return $phone;
    }

    protected function http(): PendingRequest
    {
        return Http::withOptions(['verify' => $this->verifySsl])
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
    }

    protected function isSuccess(array $body): bool
    {
        return ($body['status'] ?? '') === 'success'
            || ($body['success'] ?? false) === true;
    }

    protected function result(bool $ok, string $message, array $data = [], array $extra = []): array
    {
        return array_merge([
            'success' => $ok,
            'message' => $message,
            'data' => $data,
        ], $extra);
    }
}
