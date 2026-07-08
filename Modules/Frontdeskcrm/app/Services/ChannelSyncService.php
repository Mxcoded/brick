<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Frontdeskcrm\Models\Channel;

class ChannelSyncService
{
    protected function normalizeProvider(string $provider): string
    {
        return Str::studly(str_replace(['.', '-', '_', ' '], '_', $provider));
    }

    public function sync(Channel $channel): array
    {
        $results = [
            'availability_pushed' => false,
            'bookings_pulled' => 0,
            'errors' => [],
        ];

        try {
            if ($channel->api_endpoint && $channel->api_key) {
                $this->pushAvailability($channel);
                $results['availability_pushed'] = true;

                $bookings = $this->pullBookings($channel);
                $results['bookings_pulled'] = count($bookings);
            }
        } catch (\Exception $e) {
            Log::error("Channel sync failed for {$channel->name}: ".$e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        $channel->update([
            'last_sync_at' => now(),
            'last_sync_status' => empty($results['errors']) ? 'success' : 'failed',
        ]);

        return $results;
    }

    public function pushAvailability(Channel $channel): void
    {
        $mappings = $channel->roomMappings()->with('roomUnit.roomType')->get();

        foreach ($mappings as $mapping) {
            $unit = $mapping->roomUnit;
            if (! $unit) {
                continue;
            }

            $payload = [
                'external_room_id' => $mapping->external_room_id,
                'status' => $unit->status === 'available' ? 'open' : 'closed',
                'price' => $unit->roomType?->price ?? 0,
                'last_updated' => now()->toIso8601String(),
            ];

            $method = 'pushAvailability'.$this->normalizeProvider($channel->provider);
            if (method_exists($this, $method)) {
                $this->$method($channel, $payload);
            } else {
                $this->pushViaApi($channel, 'inventory', $payload);
            }
        }
    }

    public function pullBookings(Channel $channel): array
    {
        $method = 'pullBookings'.$this->normalizeProvider($channel->provider);
        if (method_exists($this, $method)) {
            return $this->$method($channel);
        }

        return $this->pullViaApi($channel);
    }

    public function handleWebhook(Channel $channel, array $payload): ?array
    {
        $method = 'handleWebhook'.$this->normalizeProvider($channel->provider);
        if (method_exists($this, $method)) {
            return $this->$method($channel, $payload);
        }

        Log::warning("No webhook handler for provider: {$channel->provider}");

        return null;
    }

    // --- Generic HTTP methods ---

    protected function pushViaApi(Channel $channel, string $endpoint, array $data): array
    {
        $url = rtrim($channel->api_endpoint, '/').'/'.$endpoint;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$channel->api_key,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($url, $data);

        if ($response->failed()) {
            throw new \RuntimeException("API push failed: {$response->status()} - {$response->body()}");
        }

        return $response->json() ?? [];
    }

    protected function pullViaApi(Channel $channel): array
    {
        $url = rtrim($channel->api_endpoint, '/').'/bookings';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$channel->api_key,
            'Content-Type' => 'application/json',
        ])->timeout(30)->get($url, [
            'since' => $channel->last_sync_at?->toIso8601String() ?? now()->subDay()->toIso8601String(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("API pull failed: {$response->status()} - {$response->body()}");
        }

        return $response->json()['bookings'] ?? [];
    }

    // --- Provider-specific stubs (extend per OTA) ---

    protected function pushAvailabilityBookingCom(Channel $channel, array $payload): array
    {
        $url = rtrim($channel->api_endpoint, '/').'/availability';
        $response = Http::withHeaders([
            'X-Booking-Com-Api-Key' => $channel->api_key,
        ])->timeout(30)->post($url, ['rooms' => [$payload]]);

        if ($response->failed()) {
            throw new \RuntimeException("Booking.com push failed: {$response->body()}");
        }

        return $response->json() ?? [];
    }

    protected function pullBookingsBookingCom(Channel $channel): array
    {
        $url = rtrim($channel->api_endpoint, '/').'/reservations';
        $response = Http::withHeaders([
            'X-Booking-Com-Api-Key' => $channel->api_key,
        ])->timeout(30)->get($url, [
            'modified_since' => $channel->last_sync_at?->toRfc3339String() ?? now()->subDay()->toRfc3339String(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Booking.com pull failed: {$response->body()}");
        }

        return $response->json()['reservations'] ?? [];
    }

    protected function handleWebhookBookingCom(Channel $channel, array $payload): ?array
    {
        $event = $payload['event'] ?? null;
        Log::info("Booking.com webhook received: {$event}", $payload);

        return [
            'event' => $event,
            'processed' => true,
        ];
    }
}
