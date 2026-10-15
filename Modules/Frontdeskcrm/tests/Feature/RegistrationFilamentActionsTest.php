<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages\ViewRegistration;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The desk lives in the Filament panel, so check-in, check-out and the city
 * ledger transfer have to be usable there - not only on the legacy Blade
 * screens. These tests drive the ViewRegistration actions the operator clicks.
 */
class RegistrationFilamentActionsTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private CityLedgerService $cityLedger;

    private FolioService $folios;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        foreach ([
            ['1200', 'Accounts Receivable', 'asset', 'debit'],
            ['4000', 'Room Revenue', 'income', 'credit'],
        ] as [$code, $name, $type, $normal]) {
            ChartOfAccount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'active' => true]
            );
        }

        $this->cityLedger = app(CityLedgerService::class);
        $this->folios = app(FolioService::class);
        $this->user = User::factory()->create();

        foreach ([
            'access_frontdesk_dashboard',
            'registrations.read',
            'registrations.check_in',
            'registrations.check_out',
            'registrations.manage',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->user->givePermissionTo(['access_frontdesk_dashboard', 'registrations.read']);
    }

    private function makeRoomUnit(): RoomUnit
    {
        $roomType = RoomType::create([
            'name' => 'Panel Suite '.uniqid(),
            'slug' => 'panel-suite-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        return RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'PN-'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);
    }

    private function makeAccount(): CityLedgerAccount
    {
        return $this->cityLedger->createAccount([
            'name' => 'Panel Ledger Co',
            'credit_limit' => 5_000_000,
            'payment_terms' => 'net30',
        ], $this->user->id);
    }

    private function makeRegistration(array $overrides = []): Registration
    {
        $guest = Guest::create([
            'full_name' => 'Panel Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'panel'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create(array_merge([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::today()->addDay()->toDateString(),
            'no_of_nights' => 1,
            'stay_status' => 'reserved',
            'is_group_lead' => false,
        ], $overrides));
    }

    private function postCharge(Registration $registration, float $amount): void
    {
        $folio = $this->folios->ensureFolio($registration);
        $this->folios->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => $amount,
            'post_date' => Carbon::today(),
        ], $this->user->id);
    }

    public function test_check_in_action_places_a_reserved_guest_in_a_room(): void
    {
        $this->user->givePermissionTo('registrations.check_in');

        $unit = $this->makeRoomUnit();
        $registration = $this->makeRegistration(['room_type_id' => $unit->room_type_id]);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('checkIn')
            ->callAction('checkIn', [
                'room_unit_id' => (string) $unit->id,
                'room_rate' => 50_000,
            ])
            ->assertNotified();

        $registration->refresh();

        $this->assertSame('checked_in', $registration->stay_status);
        $this->assertSame($unit->id, $registration->room_unit_id);
        $this->assertSame('occupied', $unit->fresh()->status);
    }

    public function test_check_out_action_releases_the_room_and_posts_a_flagged_balance(): void
    {
        $this->user->givePermissionTo('registrations.check_out');

        $unit = $this->makeRoomUnit();
        $account = $this->makeAccount();

        $registration = $this->makeRegistration([
            'room_type_id' => $unit->room_type_id,
            'room_unit_id' => $unit->id,
            'room_rate' => 50_000,
            'stay_status' => 'checked_in',
            'city_ledger_account_id' => $account->id,
        ]);

        $this->postCharge($registration, 90_000);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('checkOut')
            ->callAction('checkOut')
            ->assertNotified();

        $registration->refresh();

        $this->assertSame('checked_out', $registration->stay_status);
        $this->assertNotNull($registration->city_ledger_transferred_at);
        $this->assertEqualsWithDelta(90_000, $account->fresh()->balance, 0.01);
        $this->assertEqualsWithDelta(0.0, $this->folios->getOutstandingBalance($registration), 0.01);
    }

    public function test_post_to_city_ledger_action_moves_the_outstanding_balance(): void
    {
        $this->user->givePermissionTo('registrations.manage');

        $account = $this->makeAccount();
        $registration = $this->makeRegistration(['stay_status' => 'checked_in']);

        $this->postCharge($registration, 40_000);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('postToCityLedger')
            ->callAction('postToCityLedger', [
                'city_ledger_account_id' => (string) $account->id,
            ])
            ->assertNotified();

        $registration->refresh();

        $this->assertNotNull($registration->city_ledger_transferred_at);
        $this->assertEqualsWithDelta(40_000, $account->fresh()->balance, 0.01);
    }

    public function test_operational_actions_are_hidden_without_permission(): void
    {
        $unit = $this->makeRoomUnit();
        $registration = $this->makeRegistration([
            'room_type_id' => $unit->room_type_id,
            'stay_status' => 'checked_in',
        ]);

        $this->postCharge($registration, 10_000);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionHidden('checkIn')
            ->assertActionHidden('checkOut')
            ->assertActionHidden('postToCityLedger')
            ->assertActionHidden('createCityLedgerAccount');
    }

    public function test_create_city_ledger_account_action_uses_the_company_and_links_the_stay(): void
    {
        $this->user->givePermissionTo('registrations.manage');

        $registration = $this->makeRegistration([
            'company_name' => 'Acme Oil Ltd',
            'home_address' => '12 Marina, Lagos',
            'stay_status' => 'checked_in',
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('createCityLedgerAccount')
            ->mountAction('createCityLedgerAccount')
            ->assertSchemaStateSet(['name' => 'Acme Oil Ltd', 'address' => '12 Marina, Lagos'])
            ->callMountedAction()
            ->assertNotified();

        $registration->refresh();

        $this->assertNotNull($registration->city_ledger_account_id);

        $account = CityLedgerAccount::find($registration->city_ledger_account_id);

        $this->assertSame('Acme Oil Ltd', $account->name);
        $this->assertSame('12 Marina, Lagos', $account->address);
        $this->assertSame($registration->email, $account->email);
    }

    public function test_create_account_reuses_an_existing_account_for_the_same_name_and_email(): void
    {
        $existing = $this->cityLedger->createAccount([
            'name' => 'Acme Oil Ltd',
            'email' => 'ap@acme.test',
        ], $this->user->id);

        $registration = $this->makeRegistration([
            'company_name' => 'Acme Oil Ltd',
            'email' => 'ap@acme.test',
        ]);

        $result = $this->cityLedger->findOrCreateFromRegistration($registration, [
            'name' => 'Acme Oil Ltd',
            'email' => 'ap@acme.test',
        ], $this->user->id);

        $this->assertFalse($result['created']);
        $this->assertSame($existing->id, $result['account']->id);
    }

    public function test_create_account_falls_back_to_the_guest_name_without_a_company(): void
    {
        $registration = $this->makeRegistration([
            'full_name' => 'Jane Doe',
            'company_name' => null,
        ]);

        $result = $this->cityLedger->findOrCreateFromRegistration($registration, [], $this->user->id);

        $this->assertTrue($result['created']);
        $this->assertSame('Jane Doe', $result['account']->name);
        $this->assertNotEmpty($result['account']->code);
    }
}
