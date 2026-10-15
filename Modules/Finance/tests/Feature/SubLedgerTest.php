<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Services\Contracts\LedgerServiceInterface;
use Modules\Finance\Services\JournalEntryService;
use Modules\Finance\Services\PostingService;
use Modules\Finance\Support\SubLedger;
use Modules\Frontdeskcrm\Models\Guest;
use Tests\TestCase;

/**
 * A sub-ledger is what lets the AR control account be read back per customer.
 * The line-level plumbing is what matters: the party is stored on exactly the
 * line that touches a control account, survives a reversal, and can be rebuilt
 * from the single value the journal form submits.
 */
class SubLedgerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
    }

    public function test_the_ledger_stores_the_party_on_the_line_that_carries_one(): void
    {
        $ar = ChartOfAccount::where('code', '1200')->firstOrFail();
        $revenue = ChartOfAccount::where('code', '4000')->firstOrFail();

        $entry = app(LedgerServiceInterface::class)->post([
            ['account_id' => $ar->id, 'debit' => 100, 'party_type' => User::class, 'party_id' => 7],
            ['account_id' => $revenue->id, 'credit' => 100],
        ]);

        $arLine = $entry->lines->firstWhere('debit', '>', 0);
        $this->assertSame(User::class, $arLine->party_type);
        $this->assertSame(7, (int) $arLine->party_id);

        // Every other line stays free of a sub-ledger.
        $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
        $this->assertNull($revenueLine->party_type);
        $this->assertNull($revenueLine->party_id);
        $this->assertFalse($revenueLine->hasParty());
    }

    public function test_a_receivable_charge_tags_only_the_ar_line(): void
    {
        $user = User::factory()->create();

        $entry = app(PostingService::class)->recordReceivableCharge(
            amount: 500,
            revenueCode: '4000',
            referenceType: 'sub_ledger_charge',
            referenceId: 1,
            party: $user,
        );

        $arLine = $entry->lines->firstWhere('debit', '>', 0);
        $this->assertSame(User::class, $arLine->party_type);
        $this->assertSame($user->id, (int) $arLine->party_id);

        $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
        $this->assertNull($revenueLine->party_type);
    }

    public function test_a_receivable_payment_tags_only_the_ar_line(): void
    {
        $user = User::factory()->create();

        $entry = app(PostingService::class)->recordReceivablePayment(
            amount: 500,
            paymentMethod: 'bank_transfer',
            referenceType: 'sub_ledger_payment',
            referenceId: 1,
            party: $user,
        );

        $arLine = $entry->lines->firstWhere('credit', '>', 0);
        $this->assertSame(User::class, $arLine->party_type);
        $this->assertSame($user->id, (int) $arLine->party_id);
    }

    public function test_reversing_an_entry_carries_the_party_across(): void
    {
        $user = User::factory()->create();

        $entry = app(PostingService::class)->recordReceivableCharge(
            amount: 800,
            revenueCode: '4000',
            referenceType: 'sub_ledger_reversal',
            referenceId: 3,
            party: $user,
        );

        $reversal = app(JournalEntryService::class)->reverse($entry);

        $reversedAr = $reversal->lines->firstWhere('credit', '>', 0);
        $this->assertSame(User::class, $reversedAr->party_type);
        $this->assertSame($user->id, (int) $reversedAr->party_id);
    }

    public function test_the_party_label_resolves_the_guest_name(): void
    {
        if (! class_exists(Guest::class)) {
            $this->markTestSkipped('The front desk module is not enabled.');
        }

        $guest = Guest::create([
            'full_name' => 'Ada Lovelace',
            'contact_number' => '08030000009',
        ]);

        $this->assertSame('Ada Lovelace', SubLedger::label(Guest::class, $guest->id));
        $this->assertSame('Ada Lovelace', SubLedger::labelFor(Guest::class.':'.$guest->id));
        $this->assertNull(SubLedger::label(Guest::class, 999999));
    }

    public function test_parsing_rejects_unknown_or_empty_party_values(): void
    {
        $guest = Guest::class;

        $this->assertSame(['type' => $guest, 'id' => 5], SubLedger::parse($guest.':5'));
        $this->assertNull(SubLedger::parse('app\\models\\user:5'));
        $this->assertNull(SubLedger::parse('nonsense'));
        $this->assertNull(SubLedger::parse(null));
    }

    public function test_the_journal_form_rebuilds_a_line_from_its_select_value(): void
    {
        $guest = Guest::class;

        $line = JournalEntryForm::normaliseLine([
            'account_id' => 3,
            'party' => $guest.':9',
            'debit' => 10,
            'credit' => null,
            'description' => 'On account',
        ]);

        $this->assertSame(3, $line['account_id']);
        $this->assertSame($guest, $line['party_type']);
        $this->assertSame(9, $line['party_id']);
        $this->assertSame(10.0, $line['debit']);
        $this->assertSame(0.0, $line['credit']);
        $this->assertSame('On account', $line['description']);
    }

    public function test_a_line_with_no_party_stores_nulls(): void
    {
        $line = JournalEntryForm::normaliseLine([
            'account_id' => 3,
            'party' => null,
            'debit' => 10,
            'credit' => 0,
        ]);

        $this->assertNull($line['party_type']);
        $this->assertNull($line['party_id']);
    }

    public function test_the_form_renders_a_party_as_a_class_and_id_pair(): void
    {
        $guest = Guest::class;

        $this->assertSame($guest.':9', JournalEntryForm::partyValue($guest, 9));
        $this->assertNull(JournalEntryForm::partyValue(null, 9));
        $this->assertNull(JournalEntryForm::partyValue($guest, null));
    }
}
