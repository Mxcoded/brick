<?php

namespace Modules\Website\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Contracts\PaymentGatewayInterface;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Models\Booking;
use Modules\Website\Models\PaymentGateway;
use Modules\Website\Models\Refund;
use Modules\Website\Services\BookingLedgerService;

/**
 * Stripe payment gateway driver (template).
 *
 * Implements the PaymentGatewayInterface so it can be selected at runtime from
 * the `payment_gateways` table (or the env fallback in services.stripe). Wire
 * the Stripe webhook endpoint to /webhooks/payment/stripe in the Stripe dashboard.
 */
class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(protected PaymentGateway $record) {}

    public function code(): string
    {
        return 'stripe';
    }

    public function webhookSignatureHeader(): string
    {
        return 'stripe-signature';
    }

    protected function secret(): ?string
    {
        return $this->record->credential('secret');
    }

    protected function webhookSecret(): ?string
    {
        return $this->record->credential('webhook_secret');
    }

    public function initialize(string $email, float $amount, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $secret = $this->secret();
        if (! $secret) {
            return ['status' => false, 'message' => 'Payment gateway not configured.'];
        }

        // Stripe expects the smallest currency unit (kobo for NGN).
        $amountMinor = (int) round($amount * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$secret,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
            'amount' => $amountMinor,
            'currency' => 'ngn',
            'receipt_email' => $email,
            'metadata[reference]' => $reference,
            'metadata' => $metadata,
        ]);

        $data = $response->json();

        if (! $response->successful()) {
            return ['status' => false, 'message' => $data['error']['message'] ?? 'Stripe error'];
        }

        // Return a Paystack-compatible shape so the controller can redirect/inspect uniformly.
        return [
            'status' => true,
            'data' => [
                'authorization_url' => $callbackUrl.'?payment_intent='.$data['id'],
                'reference' => $reference,
                'client_secret' => $data['client_secret'] ?? null,
            ],
        ];
    }

    public function verify(string $reference): array
    {
        $secret = $this->secret();

        $response = Http::withHeaders(['Authorization' => 'Bearer '.$secret])
            ->get('https://api.stripe.com/v1/payment_intents', [
                'metadata[reference]' => $reference,
            ]);

        $data = $response->json();

        if (! $response->successful()) {
            return ['status' => false, 'message' => $data['error']['message'] ?? 'Stripe error'];
        }

        $intent = $data['data'][0] ?? null;

        return [
            'status' => true,
            'data' => [
                'status' => $intent['status'] === 'succeeded' ? 'success' : $intent['status'] ?? 'pending',
                'reference' => $reference,
            ],
        ];
    }

    public function refund(string $transactionReference, float $amount, string $reason = '', ?array $metadata = null): array
    {
        $secret = $this->secret();
        if (! $secret) {
            return ['status' => false, 'message' => 'Payment gateway not configured.'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$secret,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://api.stripe.com/v1/refunds', [
            'payment_intent' => $transactionReference,
            'amount' => (int) round($amount * 100),
            'reason' => 'requested_by_customer',
            'metadata[reason]' => $reason,
        ]);

        $data = $response->json();

        if (! $response->successful()) {
            return ['status' => false, 'message' => $data['error']['message'] ?? 'Stripe refund error'];
        }

        return [
            'status' => true,
            'data' => [
                'reference' => $data['id'],
                'status' => $data['status'] === 'succeeded' ? 'processed' : 'pending',
                'amount' => $data['amount'] ?? null,
            ],
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->webhookSecret();
        if (! $secret || ! $signature) {
            return false;
        }

        // Stripe sends `t=<timestamp>,v1=<hmac>[,v1=<hmac>]` and signs
        // "{timestamp}.{raw_body}". Signing the bare body made every genuine
        // Stripe webhook fail verification. Two v1 values are sent while a
        // webhook secret is being rotated, so accept any that matches.
        $timestamp = null;
        $candidates = [];

        foreach (explode(',', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');

            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $candidates[] = $value;
            }
        }

        if ($timestamp === null || $candidates === []) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($candidates as $candidate) {
            if (hash_equals($expected, (string) $candidate)) {
                return true;
            }
        }

        return false;
    }

    public function handleWebhook(array $event): void
    {
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? null;

        if (! $object) {
            return;
        }

        if ($type === 'payment_intent.succeeded') {
            $this->markBookingPaid($object['metadata']['reference'] ?? null, 'stripe');
        } elseif ($type === 'charge.refunded' || $type === 'refund.updated') {
            $this->handleRefundWebhook($event);
        }

        Log::info('Stripe webhook', ['type' => $type]);
    }

    private function markBookingPaid(?string $reference, string $channel): void
    {
        if (! $reference) {
            return;
        }

        $gateway = $this->code();

        $bookings = str_starts_with($reference, 'GRP')
            ? Booking::where('booking_group_id', $reference)
                ->where('payment_status', '!=', 'paid')->get()
            : Booking::where('booking_reference', $reference)
                ->where('payment_status', '!=', 'paid')->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'payment_status' => 'paid',
                'amount_paid' => $booking->total_amount,
                'payment_method' => $gateway,
                'status' => 'confirmed',
            ]);

            app(BookingLedgerService::class)->recordSettlement($booking, $gateway);

            $this->sendConfirmationEmail($booking);
        }
    }

    /**
     * Stripe sends `charge.refunded` (charge object, refunds[]) and
     * `refund.updated` (refund object with a charge id). Both are handled so a
     * refund is not silently dropped: the sale must be reversed in the ledger
     * or the guest is refunded while revenue stays recognised.
     */
    private function handleRefundWebhook(array $event): void
    {
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];
        $status = $type === 'refund.updated'
            ? strtolower((string) ($object['status'] ?? ''))
            : 'succeeded';

        $succeeded = in_array($status, ['succeeded', 'success', 'processed'], true);

        // charge.refunded carries the full refunds[] list; refund.updated carries
        // a single refund and points back at the charge.
        $refunds = $object['refunds']['data'] ?? [[
            'id' => $object['id'] ?? null,
            'amount' => $object['amount'] ?? 0,
            'status' => $object['status'] ?? $status,
            'reason' => $object['reason'] ?? null,
        ]];

        foreach ((array) $refunds as $refund) {
            $refundAmount = (float) (($refund['amount'] ?? 0) / 100);
            $this->reverseRefundForCharge($object, (int) $refundAmount, $refund['reason'] ?? null, $succeeded);
        }
    }

    /**
     * @param  array<string, mixed>  $object  the charge (or payment intent) object
     */
    private function reverseRefundForCharge(array $object, int $refundAmount, ?string $reason, bool $succeeded): void
    {
        $reference = $object['metadata']['reference']
            ?? $object['payment_intent']
            ?? null;

        if (! $reference) {
            Log::warning('Stripe refund received without a booking reference', [
                'charge' => $object['id'] ?? null,
            ]);

            return;
        }

        $bookings = str_starts_with((string) $reference, 'GRP')
            ? Booking::where('booking_group_id', $reference)->get()
            : Booking::where('booking_reference', $reference)->get();

        if ($bookings->isEmpty()) {
            Log::warning('Stripe refund matched no booking', ['reference' => $reference]);

            return;
        }

        foreach ($bookings as $booking) {
            if (! $succeeded) {
                // Only a settled refund reverses revenue.
                continue;
            }

            $refund = Refund::where('gateway', 'stripe')
                ->where('refundable_id', $booking->id)
                ->where('status', '!=', 'failed')
                ->latest('id')
                ->first();

            app(BookingLedgerService::class)->recordRefund(
                $booking,
                $refund,
                $refundAmount > 0 ? (float) $refundAmount : null,
                $this->code(),
                $reason ? "Stripe refund: {$reason}" : 'Stripe refund processed',
            );

            $booking->update(['payment_status' => 'refunded', 'amount_paid' => 0]);
        }
    }

    private function sendConfirmationEmail(Booking $booking): void
    {
        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));

            $reservationsEmail = config('mail.reservations_email');
            if ($reservationsEmail) {
                Mail::to($reservationsEmail)->send(new BookingConfirmation($booking, true));
            }
        } catch (\Exception $e) {
            Log::error('Email Failed: '.$e->getMessage());
        }
    }
}
