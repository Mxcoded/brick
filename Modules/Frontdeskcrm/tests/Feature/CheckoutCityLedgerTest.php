<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\CheckoutService;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Modules\Frontdeskcrm\Services\FolioService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Checking out a stay that is flagged to a city ledger account.
 *
 * The desk should not have to remember a second button after checkout: a stay
 * booked against a company account must have its unpaid balance posted to that
 * account as part of closing it. A stay with no account keeps its balance as an
 * ordinary guest debt, and the checkout says so rather than staying silent.
 */
class CheckoutCityLedgerTest extends TestCase
{
    use DatabaseTransactions;

    private CheckoutService $checkout;

    private CityLedgerService $cityLedger;

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

        $this->checkout = app(CheckoutService::class);
        $this->cityLedger = app(CityLedgerService::class);
        $this->folioService = app(FolioService::class);
        $this->user = User::factory()->create();

        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
    }

    private function makeAccount(): CityLedgerAccount
    {
        return $this->cityLedger->createAccount([
            'name' => 'Transcorp Hotels',
            'credit_limit' => 5_000_000,
            'payment_terms' => 'net30',
        ], $this->user->id);
    }

    private function makeStay(float $charges, ?CityLedgerAccount $account = null): Registration
    {
        $registration = Registration::create([
            'title' => 'Mr',
            'full_name' => 'Emeka Nwosu',
            'contact_number' => '08057770001',
            'check_in' => Carbon::today()->subDays(2)->toDateString(),
            'check_out' => Carbon::today()->addDay()->toDateString(),
            'stay_status' => 'checked_in',
            'room_rate' => 50_000,
            'total_amount' => 150_000,
            'city_ledger_account_id' => $account?->id,
        ]);

        $folio = $this->folioService->ensureFolio($registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => $charges,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        return $registration->fresh();
    }

    public function test_checkout_auto_posts_a_flagged_balance_to_the_account(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStay(charges: 100_000, account: $account);

        $result = $this->checkout->checkout($registration, $this->user->id);

        $this->assertTrue($result['city_ledger_posted']);
        $this->assertSame([], $result['warnings']);

        $registration->refresh();
        $this->assertSame('checked_out', $registration->stay_status);
        $this->assertNotNull($registration->city_ledger_transferred_at);

        $this->assertEqualsWithDelta(100_000, $account->fresh()->balance, 0.01);
        $this->assertEquals(0, $this->folioService->getOutstandingBalance($registration));
    }

    public function test_checkout_leaves_an_unflagged_balance_and_warns(): void
    {
        $registration = $this->makeStay(charges: 75_000);

        $result = $this->checkout->checkout($registration, $this->user->id);

        $this->assertFalse($result['city_ledger_posted']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertEqualsWithDelta(75_000, $result['outstanding'], 0.01);

        $registration->refresh();
        $this->assertSame('checked_out', $registration->stay_status);
        $this->assertNull($registration->city_ledger_transferred_at);
        $this->assertEqualsWithDelta(75_000, $this->folioService->getOutstandingBalance($registration), 0.01);
    }

    public function test_checkout_of_a_settled_flagged_stay_posts_nothing(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStay(charges: 0, account: $account);

        $result = $this->checkout->checkout($registration, $this->user->id);

        $this->assertFalse($result['city_ledger_posted']);
        $this->assertSame([], $result['warnings']);
        $this->assertEqualsWithDelta(0.0, $account->fresh()->balance, 0.01);
    }

    public function test_checkout_refuses_a_stay_that_is_not_checked_in(): void
    {
        $registration = $this->makeStay(charges: 10_000);
        $registration->update(['stay_status' => 'reserved']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not currently checked-in');

        $this->checkout->checkout($registration->fresh(), $this->user->id);
    }

    public function test_controller_checkout_auto_posts_the_flagged_balance(): void
    {
        $account = $this->makeAccount();
        $registration = $this->makeStay(charges: 60_000, account: $account);

        $response = $this->actingAs($this->user)
            ->post(route('frontdesk.registrations.checkout', $registration));

        $response->assertSessionHas('success');

        $this->assertEqualsWithDelta(60_000, $account->fresh()->balance, 0.01);
        $this->assertSame('checked_out', $registration->fresh()->stay_status);
    }
}
