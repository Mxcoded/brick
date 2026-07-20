<?php

namespace Modules\Website\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Finance\Services\PostingService;
use Modules\Website\Contracts\PaymentGatewayInterface;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Models\Booking;
use Modules\Website\Models\PaymentGateway;
use Modules\Website\Models\Refund;

class PaystackGateway implements PaymentGatewayInterface
{
    public function __construct(protected PaymentGateway $record) {}

    public function code(): string
    {
        return 'paystack';
    }

    public function webhookSignatureHeader(): string
    {
        return 'x-paystack-signature';
    }

    protected function secret(): ?string
    {
        return $this->record->credential('secret');
    }

    public function initialize(string $email, float $amount, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $secret = $this->secret();
        if (! $secret) {
            return ['status' => false, 'message' => 'Payment gateway not configured.'];
        }

        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'Bearer '.$secret,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => (int) round($amount * 100), // kobo
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ]);

        return $response->json();
    }

    public function verify(string $reference): array
    {
        $secret = $this->secret();

        $response = Http::withOptions(['verify' => false])
            ->withHeaders(['Authorization' => 'Bearer '.$secret])
            ->get('https://api.paystack.co/transaction/verify/'.$reference);

        return $response->json();
    }

    public function refund(string $transactionReference, float $amount, string $reason = '', ?array $metadata = null): array
    {
        $secret = $this->secret();
        if (! $secret) {
            return ['status' => false, 'message' => 'Payment gateway not configured.'];
        }

        $payload = array_filter([
            'transaction' => $transactionReference,
            'amount' => (int) round($amount * 100), // kobo
            'reason' => $reason,
            'metadata' => $metadata,
        ]);

        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'Bearer '.$secret,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.paystack.co/refund', $payload);

        return $response->json();
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->secret();
        if (! $secret || ! $signature) {
            return false;
        }

        return hash_hmac('sha512', $payload, $secret) === $signature;
    }

    public function handleWebhook(array $event): void
    {
        $eventType = $event['event'] ?? null;
        $data = $event['data'] ?? null;

        if (! $data) {
            return;
        }

        if ($eventType === 'charge.success') {
            $this->handleChargeSuccess($data);
        } elseif (in_array($eventType, ['refund.pending', 'refund.processed', 'refund.failed', 'refund.declined'])) {
            $this->handleRefundWebhook($eventType, $data);
        } elseif ($eventType === 'charge.failed') {
            $this->handleChargeFailed($data);
        }
    }

    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? null;
        $channel = $data['channel'] ?? 'unknown';

        if (! $reference || $status !== 'success') {
            return;
        }

        $isGroupPayment = str_starts_with($reference, 'GRP');

        if ($isGroupPayment) {
            $bookings = Booking::where('booking_group_id', $reference)
                ->where('payment_status', '!=', 'paid')
                ->get();

            foreach ($bookings as $booking) {
                $this->markBookingPaid($booking, $channel);
            }
        } else {
            $booking = Booking::where('booking_reference', $reference)
                ->where('payment_status', '!=', 'paid')
                ->first();

            if ($booking) {
                $this->markBookingPaid($booking, $channel);
            }
        }
    }

    private function markBookingPaid(Booking $booking, string $channel): void
    {
        $gateway = $this->code();

        $booking->update([
            'payment_status' => 'paid',
            'amount_paid' => $booking->total_amount,
            'payment_method' => $gateway,
            'status' => 'confirmed',
        ]);

        try {
            app(PostingService::class)
                ->recordSale('website', (float) $booking->total_amount, $gateway, 'booking', $booking->id);
        } catch (\Throwable $e) {
            report($e);
        }

        $this->sendConfirmationEmail($booking);
    }

    private function handleRefundWebhook(string $eventType, array $data): void
    {
        $refundReference = $data['reference'] ?? null;
        $transaction = $data['transaction'] ?? null;
        $originalReference = is_array($transaction) ? ($transaction['reference'] ?? null) : $transaction;
        $amount = ($data['amount'] ?? 0) / 100;

        $statusMap = [
            'refund.pending' => 'pending',
            'refund.processed' => 'processed',
            'refund.failed' => 'failed',
            'refund.declined' => 'declined',
        ];
        $newStatus = $statusMap[$eventType] ?? 'pending';

        $refund = null;
        if ($refundReference) {
            $refund = Refund::where('gateway_reference', $refundReference)->first();
        }
        if (! $refund && $originalReference) {
            $refund = Refund::where('transaction_reference', $originalReference)
                ->where('status', '!=', 'processed')
                ->first();
        }

        if ($refund) {
            $refund->update([
                'status' => $newStatus,
                'amount' => $refund->amount ?: $amount,
                'processed_at' => $newStatus === 'processed' ? now() : null,
                'metadata' => array_merge((array) $refund->metadata, ['webhook' => $data]),
            ]);
        }

        if ($originalReference) {
            $bookings = str_starts_with($originalReference, 'GRP')
                ? Booking::where('booking_group_id', $originalReference)->get()
                : Booking::where('booking_reference', $originalReference)->get();

            foreach ($bookings as $booking) {
                if ($newStatus === 'processed') {
                    $booking->update(['payment_status' => 'refunded', 'amount_paid' => 0]);
                } elseif (in_array($newStatus, ['failed', 'declined'])) {
                    $booking->update(['payment_status' => 'paid']);
                }
            }
        }

        Log::info('Paystack webhook: refund event', [
            'event' => $eventType,
            'refund_reference' => $refundReference,
            'original_reference' => $originalReference,
            'status' => $newStatus,
        ]);
    }

    private function handleChargeFailed(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (! $reference) {
            return;
        }

        $booking = Booking::where('booking_reference', $reference)->first();
        if ($booking && $booking->payment_status === 'pending') {
            $booking->update(['payment_status' => 'failed']);

            Log::warning('Paystack webhook: Payment failed', [
                'reference' => $reference,
                'reason' => $data['gateway_response'] ?? 'Unknown',
            ]);
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
