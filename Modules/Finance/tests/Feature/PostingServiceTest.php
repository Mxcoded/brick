<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Tests\TestCase;

class PostingServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
    }

    public function test_sale_posts_balanced_entry_and_maps_method_to_asset(): void
    {
        $entry = app(PostingService::class)
            ->recordSale('restaurant', 1000, 'cash', 'restaurant_payment', 1);

        $this->assertNotNull($entry);
        $this->assertSame('posted', $entry->status);
        $this->assertSame(1000.0, (float) $entry->lines->sum('debit'));
        $this->assertSame(1000.0, (float) $entry->lines->sum('credit'));

        $assetLine = $entry->lines->firstWhere('debit', '>', 0);
        $this->assertSame('1000', $assetLine->account->code); // Cash

        $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
        $this->assertSame('4100', $revenueLine->account->code); // Restaurant Revenue
    }

    public function test_card_method_maps_to_bank_account(): void
    {
        $entry = app(PostingService::class)
            ->recordSale('gym', 500, 'card', 'gym_payment', 2);

        $assetLine = $entry->lines->firstWhere('debit', '>', 0);
        $this->assertSame('1100', $assetLine->account->code); // Bank
    }

    public function test_a_gross_sale_is_split_across_revenue_tax_and_service_charge(): void
    {
        $entry = app(PostingService::class)->recordSale(
            'restaurant',
            1175,
            'cash',
            'restaurant_payment',
            71,
            null,
            null,
            75.0,
            100.0
        );

        $this->assertTrue($entry->isBalanced());
        $this->assertSame('1000', $entry->lines->firstWhere('debit', '>', 0)->account->code);

        $credits = $entry->lines->where('credit', '>', 0)
            ->mapWithKeys(fn ($line) => [$line->account->code => (float) $line->credit])
            ->all();

        $this->assertSame(['4100' => 1000.0, '2200' => 75.0, '4400' => 100.0], $credits);
    }

    public function test_a_sale_with_no_components_stays_two_lines(): void
    {
        // Modules without tax or a service charge must keep the entry they
        // always posted, and must not need 2200 or 4400 to exist at all.
        $entry = app(PostingService::class)
            ->recordSale('gym', 500, 'cash', 'gym_payment', 72);

        $this->assertCount(2, $entry->lines);
    }

    public function test_a_split_cannot_exceed_what_was_collected(): void
    {
        // A caller passing an order's full tax against a part payment would
        // otherwise post credits with no debit to match them.
        $entry = app(PostingService::class)->recordSale(
            'restaurant',
            500,
            'cash',
            'restaurant_payment',
            73,
            null,
            null,
            750.0,
            750.0
        );

        $this->assertTrue($entry->isBalanced());
        $this->assertSame(500.0, (float) $entry->lines->sum('credit'));
    }

    public function test_a_refund_mirrors_the_sales_accounts(): void
    {
        $posting = app(PostingService::class);

        $posting->recordSale('restaurant', 1175, 'cash', 'restaurant_payment', 74, null, null, 75.0, 100.0);
        $refund = $posting->recordRefund('restaurant', 1175, 'cash', 'restaurant_payment', 74, null, null, null, 75.0, 100.0);

        $this->assertTrue($refund->isBalanced());

        $debits = $refund->lines->where('debit', '>', 0)
            ->mapWithKeys(fn ($line) => [$line->account->code => (float) $line->debit])
            ->all();

        $this->assertSame(['4100' => 1000.0, '2200' => 75.0, '4400' => 100.0], $debits);
        $this->assertSame('1000', $refund->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_expense_posts_debit_expense_credit_asset(): void
    {
        $entry = app(PostingService::class)
            ->recordExpense('gym', 300, 'bank_transfer', 'gym_trainer_payment', 3);

        $this->assertSame(300.0, (float) $entry->lines->sum('debit'));
        $this->assertSame('5100', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('1100', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_ap_liability_posts_debit_expense_credit_ap(): void
    {
        $entry = app(PostingService::class)
            ->recordApLiability(750, '5000', 'purchase_order', 5);

        $this->assertSame('5000', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('2000', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_posting_is_idempotent_per_reference(): void
    {
        $posting = app(PostingService::class);
        $first = $posting->recordSale('restaurant', 100, 'cash', 'restaurant_payment', 99);
        $second = $posting->recordSale('restaurant', 100, 'cash', 'restaurant_payment', 99);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, JournalEntry::where('entry_number', $first->entry_number)->count());
    }

    public function test_zero_amount_is_skipped(): void
    {
        $this->assertNull(
            app(PostingService::class)->recordSale('restaurant', 0, 'cash', 'restaurant_payment', 100)
        );
    }
}
