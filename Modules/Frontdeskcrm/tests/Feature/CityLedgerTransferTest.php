<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Modules\Frontdeskcrm\Services\FolioService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Moving a guest's folio balance onto a city ledger account.
 *
 * The transfer posts an accounts receivable charge on the account for exactly
 * what the folio still shows as owed and, on the same transaction, records a
 * matching zero-cash payment on the guest's stay. The guest side must stop
 * reporting a balance, the account side must start owing it, and neither side
 * may be posted twice.
 */
class CityLedgerTransferTest extends TestCase
{
    use DatabaseTransactions;

    private CityLedgerService $service;

    private FolioService $folioService;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['1200', 'Accounts Receivable', 'asset', 'debit'],
            ['4000', 'Room Revenue', 'income', 'credit'],
        ] as [$code, $name, $type, $normal]) {
            ChartOfAccount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'active' => true]
            );
        }

        $this->service = app(CityLedgerService::class);
        $this->folioService = app(FolioService::class);
        $this->user = User::factory()->create();

        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
    }

    private function makeAccount(): CityLedgerAccount
    {
        return $this->service->createAccount([
            'name' => 'Transcorp Hotels',
            'contact_person' => 'Jane Doe',
            'credit_limit' => 5_000_000,
            'payment_terms' => 'net30',
        ], $this->user->id);
    }

    private function makeRegistration(): Registration
    {
        return Registration::create([
            'title' => 'Mr',
            'full_name' => 'Emeka Nwosu',
            'contact_number' => '08057770001',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'stay_status' => 'checked_in',
            'total_amount' => 300_000,
        ]);
    }

    private function makeStayWithBalance(float $charges, float $alreadyPaid = 0): Registration
    {
        $registration = $this->makeRegistration();

        $folio = $this->folioService->ensureFolio($registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => $charges,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        if ($alreadyPaid > 0) {
            RegistrationPayment::create([
                'registration_id' => $registration->id,
                'amount' => $alreadyPaid,
                'payment_method' => 'cash',
                'payment_date' => Carbon::today(),
                'received_by' => $this->user->id,
            ]);
        }

        return $registration->fresh();
    }

    public function test_transfer_posts_the_outstanding_to_the_account(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 100_000, alreadyPaid: 20_000);

        $transaction = $this->service->postOutstandingToLedger($account, $registration, $this->user->id);

        $this->assertEqualsWithDelta(80_000, $account->fresh()->balance, 0.01);
        $this->assertSame($registration->id, $transaction->registration_id);
        $this->assertSame('REG-'.$registration->id, $transaction->reference);
        $this->assertStringContainsString('Emeka Nwosu', $transaction->description);

        $lines = DB::table('finance_journal_lines')
            ->join('finance_journal_entries', 'finance_journal_entries.id', '=', 'finance_journal_lines.journal_entry_id')
            ->join('finance_chart_of_accounts', 'finance_chart_of_accounts.id', '=', 'finance_journal_lines.account_id')
            ->where('finance_journal_entries.entry_number', 'CLCHARGE-'.$transaction->id)
            ->get(['finance_chart_of_accounts.code', 'finance_journal_lines.debit', 'finance_journal_lines.credit']);

        $this->assertSame(1, $lines->where('code', '1200')->count());
        $this->assertEqualsWithDelta(80_000, (float) $lines->where('code', '1200')->first()->debit, 0.01);
        $this->assertEqualsWithDelta(80_000, (float) $lines->where('code', '4000')->first()->credit, 0.01);
    }

    public function test_transfer_settles_the_guest_folio(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 100_000, alreadyPaid: 20_000);

        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);

        $registration = $registration->fresh();

        $this->assertEquals(0, $this->folioService->getOutstandingBalance($registration));

        $transferPayment = $registration->payments()
            ->where('payment_method', 'city_ledger')
            ->firstOrFail();

        $this->assertEqualsWithDelta(80_000, (float) $transferPayment->amount, 0.01);
        $this->assertStringContainsString($account->code, (string) $transferPayment->reference);

        $this->assertSame($account->id, $registration->city_ledger_account_id);
        $this->assertNotNull($registration->city_ledger_transferred_at);
    }

    public function test_transfer_is_refused_when_nothing_is_outstanding(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 50_000, alreadyPaid: 50_000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no outstanding balance');

        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);
    }

    public function test_transfer_is_refused_twice(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 90_000);

        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already been transferred');

        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);
    }

    public function test_controller_moves_the_balance_to_the_account(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 75_000);

        $response = $this->actingAs($this->user)
            ->from(route('frontdesk.registrations.show', $registration))
            ->post(route('frontdesk.registrations.post-to-city-ledger', $registration), [
                'city_ledger_account_id' => $account->id,
            ]);

        $response->assertRedirect(route('frontdesk.registrations.show', $registration));
        $response->assertSessionHas('success');

        $this->assertEqualsWithDelta(75_000, $account->fresh()->balance, 0.01);
        $this->assertEquals(0, $this->folioService->getOutstandingBalance($registration->fresh()));
    }

    public function test_controller_reports_a_rejected_transfer(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 60_000, alreadyPaid: 60_000);

        $response = $this->actingAs($this->user)
            ->from(route('frontdesk.registrations.show', $registration))
            ->post(route('frontdesk.registrations.post-to-city-ledger', $registration), [
                'city_ledger_account_id' => $account->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEqualsWithDelta(0.0, $account->fresh()->balance, 0.01);
        $this->assertSame(0, DB::table('registration_payments')->where('payment_method', 'city_ledger')->count());
    }

    public function test_controller_creates_and_links_an_account_from_the_stay(): void
    {
        $registration = $this->makeRegistration();
        $registration->update([
            'company_name' => 'Acme Oil Ltd',
            'email' => 'ap@acme.test',
            'home_address' => '12 Marina, Lagos',
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('frontdesk.registrations.show', $registration))
            ->post(route('frontdesk.registrations.create-city-ledger-account', $registration), [
                'name' => 'Acme Oil Ltd',
                'contact_person' => 'Emeka Nwosu',
                'email' => 'ap@acme.test',
                'address' => '12 Marina, Lagos',
                'credit_limit' => 0,
                'payment_terms' => 'net30',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('frontdesk.registrations.show', $registration));
        $response->assertSessionHas('success');

        $registration->refresh();

        $this->assertNotNull($registration->city_ledger_account_id);

        $account = CityLedgerAccount::find($registration->city_ledger_account_id);

        $this->assertSame('Acme Oil Ltd', $account->name);
        $this->assertSame('12 Marina, Lagos', $account->address);
        $this->assertSame('ap@acme.test', $account->email);
    }

    public function test_controller_reuses_an_existing_account_with_the_same_name_and_email(): void
    {
        $existing = $this->service->createAccount([
            'name' => 'Acme Oil Ltd',
            'email' => 'ap@acme.test',
        ], $this->user->id);

        $registration = $this->makeRegistration();

        $this->actingAs($this->user)
            ->from(route('frontdesk.registrations.show', $registration))
            ->post(route('frontdesk.registrations.create-city-ledger-account', $registration), [
                'name' => 'Acme Oil Ltd',
                'email' => 'ap@acme.test',
            ])
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame($existing->id, $registration->city_ledger_account_id);
        $this->assertSame(1, CityLedgerAccount::where('name', 'Acme Oil Ltd')->count());
    }

    public function test_controller_refuses_to_link_an_account_once_the_stay_is_transferred(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 50_000);
        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);

        $response = $this->actingAs($this->user)
            ->from(route('frontdesk.registrations.show', $registration))
            ->post(route('frontdesk.registrations.create-city-ledger-account', $registration), [
                'name' => 'Another Co',
            ]);

        $response->assertSessionHas('error');
        $this->assertNull(CityLedgerAccount::where('name', 'Another Co')->first());
    }

    public function test_show_page_offers_the_create_city_ledger_account_button(): void
    {
        $registration = $this->makeRegistration();

        $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.show', $registration))
            ->assertOk()
            ->assertSee('Create City Ledger Account')
            ->assertSee('createCityLedgerModal');
    }

    public function test_show_page_hides_the_button_once_the_stay_is_transferred(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStayWithBalance(charges: 10_000);
        $this->service->postOutstandingToLedger($account, $registration, $this->user->id);

        $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.show', $registration->fresh()))
            ->assertOk()
            ->assertDontSee('createCityLedgerModal');
    }
}
