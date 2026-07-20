<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceUiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
        (new FinancePermissionSeeder)->run();

        JournalEntry::query()->delete();

        $this->user = User::factory()->create();
        $this->user->assignRole(Role::findOrCreate('finance', 'web'));
    }

    public function test_finance_pages_render_for_finance_user(): void
    {
        $routes = [
            'finance.index',
            'finance.coa.index',
            'finance.coa.create',
            'finance.journal.index',
            'finance.reports.index',
            'finance.reports.trial-balance',
            'finance.reports.profit-loss',
            'finance.reports.balance-sheet',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->user)
                ->get(route($route))
                ->assertStatus(200);
        }
    }

    public function test_posted_entry_appears_in_trial_balance(): void
    {
        app(PostingService::class)
            ->recordSale('restaurant', 1000, 'cash', 'restaurant_payment', 1);

        $this->actingAs($this->user)
            ->get(route('finance.reports.trial-balance'))
            ->assertStatus(200)
            ->assertSee('1,000.00');
    }

    public function test_journal_show_displays_lines(): void
    {
        $entry = app(PostingService::class)
            ->recordSale('restaurant', 500, 'card', 'restaurant_payment', 2);

        $this->actingAs($this->user)
            ->get(route('finance.journal.show', $entry))
            ->assertStatus(200)
            ->assertSee($entry->entry_number)
            ->assertSee('500.00');
    }
}
