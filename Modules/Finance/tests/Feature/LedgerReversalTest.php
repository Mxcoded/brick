<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Refund;
use Modules\Website\Services\BookingLedgerService;
use Tests\TestCase;

/**
 * Covers the two revenue-integrity fixes:
 *
 *  1. A settled refund must reverse the revenue the settlement recognised.
 *  2. A purchase order received in several deliveries must raise a payable
 *     for every delivery, not just the first.
 */
class LedgerReversalTest extends TestCase
{
    use DatabaseTransactions;

    protected PostingService $posting;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();

        JournalEntry::query()->delete();

        $this->posting = app(PostingService::class);
    }

    private function booking(float $total = 100000): Booking
    {
        return Booking::create([
            'booking_reference' => 'BK-TEST-'.uniqid(),
            'guest_name' => 'Ledger Test',
            'guest_email' => 'ledger@test.com',
            'check_in_date' => now()->addDay(),
            'check_out_date' => now()->addDays(3),
            'total_amount' => $total,
            'amount_paid' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    public function test_settlement_recognises_revenue_once(): void
    {
        $booking = $this->booking();
        $ledger = app(BookingLedgerService::class);

        $first = $ledger->recordSettlement($booking, 'paystack');
        $second = $ledger->recordSettlement($booking, 'paystack');

        $this->assertNotNull($first);
        $this->assertNull($second, 'A second confirmation double-posted the sale.');
        $this->assertSame(1, JournalEntry::where('entry_number', "SALE-booking-{$booking->id}")->count());
        $this->assertSame(100000.0, (float) $first->lines->sum('credit'));
    }

    public function test_settlement_from_a_different_path_does_not_double_post(): void
    {
        $booking = $this->booking();
        $ledger = app(BookingLedgerService::class);

        // e.g. the synchronous verify callback wins the race against the webhook.
        $ledger->recordSettlement($booking, 'stripe');
        $webhook = $ledger->recordSettlement($booking, 'paystack');

        $this->assertNull($webhook);
        $this->assertSame(
            1,
            JournalEntry::where('reference_type', 'booking')->where('reference_id', $booking->id)->count(),
        );
    }

    public function test_refund_reverses_the_recognised_revenue(): void
    {
        $booking = $this->booking();
        $ledger = app(BookingLedgerService::class);

        $sale = $ledger->recordSettlement($booking, 'paystack');
        $refund = $ledger->recordRefund($booking, null, null, 'paystack', 'guest cancelled');

        $this->assertNotNull($refund);
        $this->assertSame('4000', $refund->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('1110', $refund->lines->firstWhere('credit', '>', 0)->account->code);
        $this->assertSame(100000.0, (float) $refund->lines->sum('debit'));

        // Net revenue for the booking must be zero after a full refund.
        $netRevenue = JournalEntry::whereIn('entry_number', [
            "SALE-booking-{$booking->id}",
            "REF-booking-{$booking->id}",
        ])->get()->sum(fn ($e) => (float) $e->lines->sum('credit') - (float) $e->lines->sum('debit'));

        $this->assertSame(0.0, (float) $netRevenue);

        $this->assertNotNull($sale);
    }

    public function test_repeated_refund_webhook_delivery_posts_one_reversal(): void
    {
        $booking = $this->booking();
        $ledger = app(BookingLedgerService::class);

        $ledger->recordSettlement($booking, 'paystack');

        $first = $ledger->recordRefund($booking, null, null, 'paystack');
        $replay = $ledger->recordRefund($booking, null, null, 'paystack');

        $this->assertNotNull($first);
        $this->assertNull($replay, 'A replayed refund webhook double-reversed revenue.');
    }

    public function test_successive_partial_refunds_each_post_their_own_reversal(): void
    {
        $booking = $this->booking();
        $ledger = app(BookingLedgerService::class);

        $ledger->recordSettlement($booking, 'paystack');

        $refunds = Refund::create([
            'gateway' => 'paystack',
            'gateway_reference' => 'RFR_'.uniqid(),
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => 25000,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        $second = Refund::create([
            'gateway' => 'paystack',
            'gateway_reference' => 'RFR_'.uniqid(),
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => 40000,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        $one = $ledger->recordRefund($booking, $refunds, null, 'paystack');
        $two = $ledger->recordRefund($booking, $second, null, 'paystack');

        $this->assertNotNull($one);
        $this->assertNotNull($two, 'The second partial refund was swallowed by the first.');
        $this->assertSame(25000.0, (float) $one->lines->sum('debit'));
        $this->assertSame(40000.0, (float) $two->lines->sum('debit'));
    }

    public function test_each_goods_delivery_raises_its_own_payable(): void
    {
        $firstKey = 'AP-purchase_order-7-rcpt-101';
        $secondKey = 'AP-purchase_order-7-rcpt-102';

        $first = $this->posting->recordApLiability(30000, '5000', 'purchase_order', 7, null, 'Delivery 1', $firstKey);
        $replay = $this->posting->recordApLiability(30000, '5000', 'purchase_order', 7, null, 'Delivery 1', $firstKey);
        $second = $this->posting->recordApLiability(45000, '5000', 'purchase_order', 7, null, 'Delivery 2', $secondKey);

        $this->assertNotNull($first);
        $this->assertNull($replay, 'A replayed delivery double-posted the payable.');
        $this->assertNotNull($second, 'The second delivery was silently dropped.');
        $this->assertSame(75000.0, (float) JournalEntry::where('reference_type', 'purchase_order')
            ->where('reference_id', 7)
            ->get()
            ->sum(fn ($e) => (float) $e->lines->sum('credit')));
    }

    public function test_payable_reference_stays_traceable_to_the_purchase_order(): void
    {
        $entry = $this->posting->recordApLiability(
            20000,
            '5000',
            'purchase_order',
            42,
            null,
            'Goods received against PO #PO-42',
            'AP-purchase_order-42-rcpt-999',
        );

        $this->assertNotNull($entry);
        $this->assertSame('purchase_order', $entry->reference_type);
        $this->assertSame(42, (int) $entry->reference_id);
        $this->assertSame('2000', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }
}
