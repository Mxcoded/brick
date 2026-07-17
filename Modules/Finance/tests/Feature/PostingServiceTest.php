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

    public function test_expense_posts_debit_expense_credit_asset(): void
    {
        $entry = app(PostingService::class)
            ->recordExpense('gym', 300, 'bank_transfer', 'gym_trainer_payment', 3);

        $this->assertSame(300.0, (float) $entry->lines->sum('debit'));
        $this->assertSame('5400', $entry->lines->firstWhere('debit', '>', 0)->account->code);
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
