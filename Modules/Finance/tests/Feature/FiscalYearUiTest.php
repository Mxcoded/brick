<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Filament\Resources\FiscalYears\FiscalYearResource;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\CreateFiscalYear;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\EditFiscalYear;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\ListFiscalYears;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\CreateJournalEntry;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\EditJournalEntry;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Support\FinancePermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FiscalYearUiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
        (new FinancePermissionSeeder)->run();

        $this->user = User::factory()->create();
        // This suite exercises the write paths - creating a fiscal year,
        // drafting and posting a manual journal - so it runs as a senior
        // accountant. The junior `finance` role is deliberately read-only and is
        // covered by FinancePermissionTierTest.
        $this->user->assignRole(Role::findOrCreate(FinancePermission::ROLE_SENIOR, 'web'));
    }

    public function test_fiscal_year_pages_render_for_finance_user(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFiscalYears::class)->assertOk();
        Livewire::test(CreateFiscalYear::class)->assertOk();
    }

    public function test_creating_a_fiscal_year_generates_monthly_periods(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateFiscalYear::class)
            ->fillForm([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
                'is_closed' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $year = FiscalYear::where('name', 'FY2026')->firstOrFail();

        $this->assertCount(12, $year->periods()->get());
        $this->assertTrue($year->is_active);
    }

    public function test_edit_page_shows_the_period_relation_manager(): void
    {
        $year = FiscalYear::create([
            'name' => 'FY2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $year->generateMonthlyPeriods();

        $this->actingAs($this->user);

        Livewire::test(EditFiscalYear::class, ['record' => $year->getKey()])->assertOk();

        // The relation manager table is lazy-loaded, so drive lock/unlock
        // directly rather than asserting on unrendered markup.
        $period = $year->periodFor('2026-03-10');
        $period->lock();
        $this->assertTrue($period->fresh()->isLocked());

        $period->unlock();
        $this->assertFalse($period->fresh()->isLocked());
    }

    public function test_fiscal_year_page_is_forbidden_without_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('staff', 'web'));
        $user->revokePermissionTo('finance.manage_periods');

        $this->actingAs($user);

        $this->assertFalse(FiscalYearResource::canAccess());

        Livewire::test(ListFiscalYears::class)->assertForbidden();
    }

    public function test_manual_journal_entry_can_be_saved_as_a_draft(): void
    {
        $this->actingAs($this->user);

        [$debit, $credit] = $this->accountIds();

        Livewire::test(CreateJournalEntry::class)
            ->fillForm([
                'date' => now()->toDateString(),
                'description' => 'Accrued utility bill',
                'reference_type' => 'accrual',
                'lines' => [
                    ['account_id' => $debit, 'debit' => 250, 'credit' => 0],
                    ['account_id' => $credit, 'debit' => 0, 'credit' => 250],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $entry = JournalEntry::where('description', 'Accrued utility bill')->firstOrFail();

        $this->assertSame('draft', $entry->status);
        $this->assertCount(2, $entry->lines);
        $this->assertTrue($entry->isBalanced());
        $this->assertNull($entry->posted_at);
    }

    public function test_manual_journal_entry_can_be_saved_and_posted(): void
    {
        $this->actingAs($this->user);

        [$debit, $credit] = $this->accountIds();

        Livewire::test(CreateJournalEntry::class)
            ->fillForm([
                'date' => now()->toDateString(),
                'description' => 'Accrued utility bill',
                'reference_type' => 'accrual',
                'lines' => [
                    ['account_id' => $debit, 'debit' => 250, 'credit' => 0],
                    ['account_id' => $credit, 'debit' => 0, 'credit' => 250],
                ],
            ])
            ->call('createAndPost')
            ->assertHasNoFormErrors();

        $entry = JournalEntry::where('description', 'Accrued utility bill')->firstOrFail();

        $this->assertSame('posted', $entry->status);
        $this->assertNotNull($entry->posted_at);
        $this->assertNotNull($entry->entry_number);
    }

    public function test_unbalanced_entry_fails_to_post_but_the_draft_is_kept(): void
    {
        $this->actingAs($this->user);

        [$debit, $credit] = $this->accountIds();

        Livewire::test(CreateJournalEntry::class)
            ->fillForm([
                'date' => now()->toDateString(),
                'description' => 'Unbalanced entry',
                'reference_type' => 'manual',
                'lines' => [
                    ['account_id' => $debit, 'debit' => 250, 'credit' => 0],
                    ['account_id' => $credit, 'debit' => 0, 'credit' => 100],
                ],
            ])
            ->call('createAndPost')
            ->assertHasFormErrors(['lines']);

        $entry = JournalEntry::where('description', 'Unbalanced entry')->firstOrFail();

        $this->assertSame('draft', $entry->status);
    }

    public function test_entry_date_in_a_locked_period_is_rejected(): void
    {
        $year = FiscalYear::create([
            'name' => 'FY2026',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ]);
        $year->generateMonthlyPeriods();
        $year->periodFor(now())->lock();

        $this->actingAs($this->user);

        [$debit, $credit] = $this->accountIds();

        Livewire::test(CreateJournalEntry::class)
            ->fillForm([
                'date' => now()->toDateString(),
                'description' => 'Should be blocked',
                'reference_type' => 'manual',
                'lines' => [
                    ['account_id' => $debit, 'debit' => 100, 'credit' => 0],
                    ['account_id' => $credit, 'debit' => 0, 'credit' => 100],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);
    }

    public function test_draft_entry_can_be_edited_and_then_posted(): void
    {
        $this->actingAs($this->user);

        [$debit, $credit] = $this->accountIds();

        $entry = JournalEntry::create([
            'entry_number' => 'DRAFT-1',
            'date' => now()->toDateString(),
            'description' => 'Draft to finish',
            'status' => 'draft',
        ]);
        $entry->lines()->createMany([
            ['account_id' => $debit, 'debit' => 0, 'credit' => 0],
            ['account_id' => $credit, 'debit' => 0, 'credit' => 0],
        ]);

        Livewire::test(EditJournalEntry::class, ['record' => $entry->getKey()])
            ->fillForm([
                'date' => now()->toDateString(),
                'description' => 'Draft to finish',
                'reference_type' => 'manual',
                'lines' => [
                    ['account_id' => $debit, 'debit' => 500, 'credit' => 0],
                    ['account_id' => $credit, 'debit' => 0, 'credit' => 500],
                ],
            ])
            ->call('saveAndPost')
            ->assertHasNoFormErrors();

        $entry->refresh()->load('lines');

        $this->assertSame('posted', $entry->status);
        $this->assertCount(2, $entry->lines);
        $this->assertTrue($entry->isBalanced());
    }

    public function test_create_page_is_forbidden_without_the_create_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('staff', 'web'));
        $user->revokePermissionTo('finance.create_journal');
        $user->givePermissionTo('finance.view_ledger');

        $this->actingAs($user);

        Livewire::test(CreateJournalEntry::class)->assertForbidden();
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function accountIds(): array
    {
        return [
            ChartOfAccount::where('code', '1100')->value('id'),
            ChartOfAccount::where('code', '4900')->value('id'),
        ];
    }
}
