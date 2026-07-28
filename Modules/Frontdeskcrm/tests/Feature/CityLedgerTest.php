<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CityLedgerTest extends TestCase
{
    use DatabaseTransactions;

    protected CityLedgerService $cityLedgerService;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cityLedgerService = app(CityLedgerService::class);
        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');

        ChartOfAccount::updateOrCreate(
            ['code' => '1200'],
            ['name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true]
        );
        ChartOfAccount::updateOrCreate(
            ['code' => '4000'],
            ['name' => 'Room Revenue', 'type' => 'income', 'normal_balance' => 'credit', 'active' => true]
        );
        ChartOfAccount::updateOrCreate(
            ['code' => '1000'],
            ['name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true]
        );
    }

    private function makeAccount(): CityLedgerAccount
    {
        return $this->cityLedgerService->createAccount([
            'name' => 'Acme Corp',
            'contact_person' => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '08012345678',
            'credit_limit' => 500000,
            'payment_terms' => 'net30',
        ], $this->user->id);
    }

    public function test_create_account(): void
    {
        $account = $this->makeAccount();

        $this->assertNotNull($account);
        $this->assertEquals('Acme Corp', $account->name);
        $this->assertEquals('net30', $account->payment_terms);
        $this->assertEquals(0, $account->balance);
        $this->assertStringStartsWith('CL-', $account->code);
    }

    public function test_post_charge_increases_balance(): void
    {
        $account = $this->makeAccount();

        $this->cityLedgerService->postCharge(
            $account, 100000, 'Room charges for Q1', 'INV-001', null, null, $this->user->id
        );

        $account->refresh();
        $this->assertEquals(100000, $account->balance);
        $this->assertEquals(1, $account->transactions()->count());
    }

    public function test_record_payment_decreases_balance(): void
    {
        $account = $this->makeAccount();

        $this->cityLedgerService->postCharge($account, 200000, 'Room charges', 'INV-001', null, null, $this->user->id);
        $account->refresh();
        $this->assertEquals(200000, $account->balance);

        $this->cityLedgerService->recordPayment(
            $account, 80000, 'bank_transfer', 'Payment received', 'PAY-001', $this->user->id
        );

        $account->refresh();
        $this->assertEquals(120000, $account->balance);
    }

    public function test_credit_note_decreases_balance(): void
    {
        $account = $this->makeAccount();

        $this->cityLedgerService->postCharge($account, 50000, 'Charge', 'REF-001', null, null, $this->user->id);
        $account->refresh();
        $this->assertEquals(50000, $account->balance);

        $this->cityLedgerService->createCreditNote($account, 10000, 'Adjustment', 'CN-001', $this->user->id);

        $account->refresh();
        $this->assertEquals(40000, $account->balance);
    }

    public function test_aging_report_returns_accounts_with_balance(): void
    {
        $account = $this->makeAccount();

        $this->cityLedgerService->postCharge($account, 75000, 'Charges', 'INV-001', null, null, $this->user->id);

        $report = $this->cityLedgerService->getAgingReport();

        $this->assertCount(1, $report);
        $this->assertEquals($account->id, $report[0]['account']->id);
        $this->assertEquals(75000, $report[0]['total_outstanding']);
    }

    public function test_city_ledger_page_loads(): void
    {
        $account = $this->makeAccount();

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.city-ledger.index'));

        $response->assertOk();
        $response->assertSee('City Ledger Accounts');
    }

    public function test_city_ledger_show_page_loads(): void
    {
        $account = $this->makeAccount();
        $this->cityLedgerService->postCharge($account, 30000, 'Test charge', null, null, null, $this->user->id);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.city-ledger.show', $account));

        $response->assertOk();
        $response->assertSee($account->name);
        $response->assertSee('30,000');
    }
}
