<?php

namespace Modules\Website\Services;

use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Refund;

/**
 * Single entry point for website booking revenue in the general ledger.
 *
 * Every path that can settle a booking (Paystack webhook, Stripe webhook, the
 * synchronous verify callback, and the admin "mark paid" action) funnels through
 * here, so a booking is recognised the same amount regardless of which path
 * confirmed it, and no path can settle a booking without touching the ledger.
 */
class BookingLedgerService
{
    public function __construct(private PostingService $postings) {}

    /**
     * Recognise revenue for a settled booking.
     *
     * Idempotent per booking (SALE-booking-{id}), so whichever path confirms
     * first wins and later confirmations are no-ops rather than duplicates.
     */
    public function recordSettlement(Booking $booking, ?string $gateway = null): ?JournalEntry
    {
        $amount = (float) $booking->total_amount;

        if ($amount <= 0) {
            return null;
        }

        return $this->guard(fn () => $this->postings->recordSale(
            'website',
            $amount,
            $gateway ?: ($booking->payment_method ?: 'bank'),
            'booking',
            $booking->id,
            null,
            "Website booking {$booking->booking_reference}",
        ));
    }

    /**
     * Reverse revenue for a settled refund.
     *
     * Keyed on the refund's own id when a refunds row exists, so successive
     * partial refunds each post their own reversal rather than the first one
     * masking the others. Falls back to one reversal per booking.
     */
    public function recordRefund(
        Booking $booking,
        ?Refund $refund = null,
        ?float $amount = null,
        ?string $gateway = null,
        ?string $reason = null
    ): ?JournalEntry {
        $amount ??= $refund?->amount !== null
            ? (float) $refund->amount
            : (float) $booking->total_amount;

        if ($amount <= 0) {
            return null;
        }

        $refundId = $refund?->getKey();
        $key = $refundId
            ? "REF-booking-{$booking->id}-refund-{$refundId}"
            : "REF-booking-{$booking->id}";

        return $this->guard(fn () => $this->postings->recordRefund(
            'website',
            $amount,
            $gateway ?: ($booking->payment_method ?: 'bank'),
            'booking',
            $booking->id,
            null,
            trim(sprintf(
                'Refund for website booking %s%s',
                $booking->booking_reference,
                $reason ? ": {$reason}" : '',
            )),
            $key,
        ));
    }

    /**
     * A ledger failure must never roll back a payment the guest already made.
     * Report it loudly so `finance:reconcile` can pick it up.
     */
    private function guard(callable $post): ?JournalEntry
    {
        try {
            return $post();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
