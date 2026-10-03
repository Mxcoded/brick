<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalLine;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\Invoice;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Tests\TestCase;

/**
 * The city ledger owes the hotel money, so the account balance and the general
 * ledger are two views of the same fact and must never disagree.
 *
 * The existing CityLedgerTest only asserted the account balance, which meant the
 * journal side of every method here went completely unverified.
 */
class CityLedgerPostingTest extends TestCase
{
    use DatabaseTransactions;

    private CityLedgerService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['1200', 'Accounts Receivable', 'asset', 'debit'],
            ['4000', 'Room Revenue', 'income', 'credit'],
            ['1000', 'Cash', 'asset', 'debit'],
            ['1100', 'Bank', 'asset', 'debit'],
        ] as [$code, $name, $type, $normal]) {
            ChartOfAccount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'active' => true]
            );
        }

        $this->service = app(CityLedgerService::class);
        $this->user = User::factory()->create();
    }

    private function makeAccount(float $creditLimit = 5_000_000): CityLedgerAccount
    {
        return $this->service->createAccount([
            'name' => 'Acme Corp',
            'contact_person' => 'John Doe',
            'credit_limit' => $creditLimit,
            'payment_terms' => 'net30',
        ], $this->user->id);
    }

    private function makeRegistration(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'title' => 'Mrs',
            'full_name' => 'Ngozi Okafor',
            'contact_number' => '08030000001',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
        ], $attributes));
    }

    private function makeInvoice(array $attributes = []): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'issue_date' => now()->toDateString(),
            'total' => 300_000,
            'paid_amount' => 0,
            'status' => 'issued',
        ], $attributes));
    }

    /**
     * Debit and credit totals of the entry carrying a given entry_number.
     *
     * @return array{debit: float, credit: float}
     */
    private function entryTotals(string $entryNumber): array
    {
        $lines = $this->linesFor($entryNumber);

        return [
            'debit' => (float) $lines->sum('debit'),
            'credit' => (float) $lines->sum('credit'),
        ];
    }

    /**
     * The lines of a given entry number.
     */
    private function linesFor(string $entryNumber)
    {
        return JournalLine::with('account')
            ->whereHas('journalEntry', fn ($e) => $e->where('entry_number', $entryNumber))
            ->get();
    }

    /**
     * The single line on a given side of an entry.
     *
     * A line carries an amount in either the debit or the credit column, so the
     * side is whichever is non-zero.
     */
    private function lineOnSide(string $entryNumber, string $side): JournalLine
    {
        $line = $this->linesFor($entryNumber)
            ->first(fn (JournalLine $l) => (float) $l->{$side} > 0);

        $this->assertNotNull($line, "No {$side} line on entry {$entryNumber}.");

        return $line;
    }

    public function test_guest_folio_charge_debits_receivable_and_credits_revenue(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeRegistration();

        $transaction = $this->service->chargeRegistration($account, $registration, 125_000, null, $this->user->id);

        $totals = $this->entryTotals('CLCHARGE-'.$transaction->id);

        $this->assertEqualsWithDelta(125_000, $totals['debit'], 0.01);
        $this->assertEqualsWithDelta(125_000, $totals['credit'], 0.01);

        // AR is debited, revenue is credited: the money is owed, not received.
        $this->assertSame('1200', $this->lineOnSide('CLCHARGE-'.$transaction->id, 'debit')->account->code);
        $this->assertSame('4000', $this->lineOnSide('CLCHARGE-'.$transaction->id, 'credit')->account->code);
    }

    public function test_guest_folio_charge_names_the_guest_and_links_the_registration(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeRegistration(['booking_group_id' => 'GRP-1001']);

        $transaction = $this->service->chargeRegistration($account, $registration, 50_000, null, $this->user->id);

        $this->assertSame($registration->id, $transaction->registration_id);
        $this->assertStringContainsString('Ngozi Okafor', $transaction->description);
        $this->assertStringContainsString('GRP-1001', $transaction->description);
    }

    public function test_group_booking_charge_is_posted_and_linked_to_the_invoice(): void
    {
        $account = $this->makeAccount();
        $invoice = $this->makeInvoice();

        $transaction = $this->service->chargeGroupBooking($account, $invoice, 300_000, null, $this->user->id);

        $this->assertSame($invoice->id, $transaction->invoice_id);
        $this->assertSame('INV-'.$invoice->id, $transaction->reference);

        $totals = $this->entryTotals('CLCHARGE-'.$transaction->id);
        $this->assertEqualsWithDelta(300_000, $totals['debit'], 0.01);
        $this->assertEqualsWithDelta(300_000, $totals['credit'], 0.01);
    }

    public function test_booking_group_charge_is_posted_under_the_group_reference(): void
    {
        $account = $this->makeAccount();

        $transaction = $this->service->chargeBookingGroup($account, 'GRP-2002', 1_250_000, null, $this->user->id);

        $this->assertSame('GRP-GRP-2002', $transaction->reference);
        $this->assertEqualsWithDelta(1_250_000, $account->fresh()->balance, 0.01);

        $totals = $this->entryTotals('CLCHARGE-'.$transaction->id);
        $this->assertEqualsWithDelta(1_250_000, $totals['debit'], 0.01);
    }

    public function test_guest_and_group_charges_on_one_account_roll_up_to_a_single_balance(): void
    {
        $account = $this->makeAccount();

        $this->service->chargeRegistration($account, $this->makeRegistration(['full_name' => 'A Chukwu']), 40_000, null, $this->user->id);
        $this->service->chargeGroupBooking($account, $this->makeInvoice(), 300_000, null, $this->user->id);
        $this->service->chargeBookingGroup($account, 'GRP-2002', 60_000, null, $this->user->id);

        $this->assertEqualsWithDelta(400_000, $account->fresh()->balance, 0.01);
        $this->assertEquals(3, $account->transactions()->count());

        $this->service->recordPayment($account, 150_000, 'bank_transfer', 'Part payment', null, $this->user->id);

        $this->assertEqualsWithDelta(250_000, $account->fresh()->balance, 0.01);
    }

    public function test_payment_debits_the_bank_named_by_the_method_and_credits_receivable(): void
    {
        $account = $this->makeAccount();
        $this->service->postCharge($account, 200_000, 'Charge', null, null, null, $this->user->id);

        $transaction = $this->service->recordPayment(
            $account, 80_000, 'bank_transfer', 'Payment received', 'PAY-001', $this->user->id
        );

        $debit = $this->lineOnSide('CLPAY-'.$transaction->id, 'debit');
        $credit = $this->lineOnSide('CLPAY-'.$transaction->id, 'credit');

        $this->assertSame('1100', $debit->account->code, 'bank_transfer settles to Bank');
        $this->assertEqualsWithDelta(80_000, (float) $debit->debit, 0.01);
        $this->assertSame('1200', $credit->account->code);
        $this->assertEqualsWithDelta(80_000, (float) $credit->credit, 0.01);
    }

    public function test_credit_note_reverses_the_receivable(): void
    {
        $account = $this->makeAccount();
        $this->service->postCharge($account, 50_000, 'Charge', null, null, null, $this->user->id);

        $transaction = $this->service->createCreditNote($account, 10_000, 'Adjustment', 'CN-001', $this->user->id);

        $this->assertSame(
            '4000',
            $this->lineOnSide('CLCN-'.$transaction->id, 'debit')->account->code
        );
        $this->assertSame(
            '1200',
            $this->lineOnSide('CLCN-'.$transaction->id, 'credit')->account->code
        );
        $this->assertEqualsWithDelta(40_000, $account->fresh()->balance, 0.01);
    }

    public function test_a_guest_folio_receives_a_4000_credit_and_the_account_stays_in_step_with_the_ledger(): void
    {
        $account = $this->makeAccount();

        $charge = $this->service->chargeRegistration($account, $this->makeRegistration(), 90_000, null, $this->user->id);
        $payment = $this->service->recordPayment($account, 30_000, 'cash', 'Part payment', null, $this->user->id);

        $entries = JournalEntry::whereIn('entry_number', [
            'CLCHARGE-'.$charge->id,
            'CLPAY-'.$payment->id,
        ])->get();

        $ar = JournalLine::whereIn('journal_entry_id', $entries->pluck('id'))
            ->whereHas('account', fn ($a) => $a->where('code', '1200'))
            ->get();

        $ledgerAr = (float) $ar->sum('debit') - (float) $ar->sum('credit');

        // The whole point: what the account says is owed is what AR says is owed.
        $this->assertEqualsWithDelta(60_000, $account->fresh()->balance, 0.01);
        $this->assertEqualsWithDelta((float) $account->fresh()->balance, $ledgerAr, 0.01);
    }

    public function test_charges_are_not_double_posted_on_retry(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeRegistration();

        $first = $this->service->chargeRegistration($account, $registration, 70_000, null, $this->user->id);

        // Replaying the same posting key must not create a second entry.
        app(PostingService::class)->recordReceivableCharge(
            amount: 70_000,
            revenueCode: '4000',
            referenceType: 'city_ledger_transaction',
            referenceId: $first->id,
            idempotencyKey: 'CLCHARGE-'.$first->id,
        );

        $this->assertSame(
            1,
            JournalEntry::where('entry_number', 'CLCHARGE-'.$first->id)->count()
        );
    }

    public function test_a_non_positive_amount_is_rejected_instead_of_silently_skipped(): void
    {
        $account = $this->makeAccount();

        // The posting service ignores non-positive amounts, so the city ledger
        // must refuse them outright rather than move its own balance only.
        $this->expectException(InvalidArgumentException::class);

        $this->service->postCharge($account, -5_000, 'Negative charge', null, null, null, $this->user->id);
    }

    public function test_a_failed_posting_leaves_neither_transaction_nor_balance_moved(): void
    {
        $account = $this->makeAccount();

        $before = $account->fresh()->balance;

        // A revenue account that does not exist makes the ledger post fail.
        config(['finance.accounts.revenue.frontdesk' => '9999']);

        try {
            $this->service->chargeRegistration($account, $this->makeRegistration(), 25_000, null, $this->user->id);
            $this->fail('Expected the posting to fail for an unknown revenue account.');
        } catch (\Throwable) {
            // expected
        } finally {
            config(['finance.accounts.revenue.frontdesk' => '4000']);
        }

        $this->assertEquals(0, DB::table('city_ledger_transactions')->count());
        $this->assertEqualsWithDelta($before, $account->fresh()->balance, 0.01);
    }
}
