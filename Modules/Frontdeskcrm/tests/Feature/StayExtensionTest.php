<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages\ViewRegistration;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\StayService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StayExtensionTest extends TestCase
{
    use DatabaseTransactions;

    protected StayService $stayService;

    protected User $user;

    protected array $rooms = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->stayService = app(StayService::class);
        $this->user = User::factory()->create();

        foreach (['access_frontdesk_dashboard', 'registrations.read', 'registrations.adjust_stay'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->user->givePermissionTo(['access_frontdesk_dashboard', 'registrations.read']);
    }

    private function makeRoomUnit(string $label): RoomUnit
    {
        $roomType = RoomType::create([
            'name' => 'Stay Suite '.$label.' '.uniqid(),
            'slug' => 'stay-suite-'.strtolower($label).'-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        return RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'ST-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
    }

    private function makeRegistration(array $overrides = []): Registration
    {
        $roomUnit = $this->makeRoomUnit('T');

        $guest = Guest::create([
            'full_name' => 'Stay Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'stay'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create(array_merge([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'room_type_id' => $roomUnit->room_type_id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(2),
            'check_out' => Carbon::today()->addDays(3),
            'no_of_nights' => 5,
            'total_amount' => 250000,
            'stay_status' => 'checked_in',
            'is_group_lead' => false,
        ], $overrides));
    }

    public function test_filament_view_page_can_extend_a_stay(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        $newCheckOut = Carbon::today()->addDays(5);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => $newCheckOut->format('Y-m-d'),
            ])
            ->assertNotified();

        $registration->refresh();

        $this->assertTrue($newCheckOut->isSameDay($registration->check_out));
        $this->assertSame(7, $registration->no_of_nights);
        $this->assertSame(350000.0, (float) $registration->total_amount);
    }

    public function test_extend_stay_action_hidden_without_adjust_stay_permission(): void
    {
        $registration = $this->makeRegistration();

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionHidden('extendStay');
    }

    public function test_extend_stay_action_hidden_when_not_checked_in(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration(['stay_status' => 'reserved']);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionHidden('extendStay');
    }

    public function test_extension_refused_when_room_is_double_booked(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        $blocker = $this->makeRegistration([
            'full_name' => 'Blocker Guest',
            'email' => 'blocker'.uniqid().'@example.com',
            'room_unit_id' => $registration->room_unit_id,
            'check_in' => Carbon::today(),
            'check_out' => Carbon::today()->addDays(10),
            'no_of_nights' => 10,
            'total_amount' => 500000,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => Carbon::today()->addDays(7)->format('Y-m-d'),
            ])
            ->assertNotified();

        $registration->refresh();
        $blocker->refresh();

        $this->assertTrue($registration->check_out->isSameDay(Carbon::today()->addDays(3)));
        $this->assertSame(250000.0, (float) $registration->total_amount);
    }

    public function test_extension_refused_when_room_is_reserved_for_a_future_guest(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        $blocker = $this->makeRegistration([
            'full_name' => 'Future Guest',
            'email' => 'future'.uniqid().'@example.com',
            'room_unit_id' => $registration->room_unit_id,
            'check_in' => Carbon::today()->addDays(4),
            'check_out' => Carbon::today()->addDays(9),
            'no_of_nights' => 5,
            'total_amount' => 250000,
            'stay_status' => 'reserved',
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => Carbon::today()->addDays(7)->format('Y-m-d'),
            ])
            ->assertNotified();

        $registration->refresh();
        $blocker->refresh();

        $this->assertTrue($registration->check_out->isSameDay(Carbon::today()->addDays(3)));
        $this->assertSame(250000.0, (float) $registration->total_amount);
    }

    public function test_extension_refused_when_room_has_a_website_booking(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        Booking::create([
            'guest_name' => 'Online Blocker',
            'guest_email' => 'online'.uniqid().'@example.com',
            'room_type_id' => $registration->room_type_id,
            'room_unit_id' => $registration->room_unit_id,
            'check_in_date' => $registration->check_out->format('Y-m-d'),
            'check_out_date' => Carbon::today()->addDays(9)->format('Y-m-d'),
            'status' => 'confirmed',
            'total_amount' => 300000,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => Carbon::today()->addDays(7)->format('Y-m-d'),
            ])
            ->assertNotified();

        $registration->refresh();

        $this->assertTrue($registration->check_out->isSameDay(Carbon::today()->addDays(3)));
        $this->assertSame(250000.0, (float) $registration->total_amount);
    }

    public function test_guest_can_extend_by_moving_to_a_free_room(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        $oldUnitId = $registration->room_unit_id;
        $targetUnit = RoomUnit::create([
            'room_type_id' => $registration->room_type_id,
            'room_number' => 'MT-'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);

        // The current room is taken for the extension, so the guest must move.
        $this->makeRegistration([
            'full_name' => 'Future Guest',
            'email' => 'future2'.uniqid().'@example.com',
            'room_unit_id' => $registration->room_unit_id,
            'check_in' => Carbon::today()->addDays(4),
            'check_out' => Carbon::today()->addDays(9),
            'no_of_nights' => 5,
            'total_amount' => 250000,
            'stay_status' => 'reserved',
        ]);

        $newCheckOut = Carbon::today()->addDays(5);

        $units = $this->stayService->availableMoveUnits($registration, $newCheckOut);
        $this->assertTrue($units->doesntContain(fn ($unit) => $unit->id === $oldUnitId));
        $this->assertTrue($units->contains('id', $targetUnit->id));

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $registration->getRouteKey()])
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => $newCheckOut->format('Y-m-d'),
                'room_unit_id' => (string) $targetUnit->id,
            ])
            ->assertNotified();

        $registration->refresh();

        $this->assertTrue($newCheckOut->isSameDay($registration->check_out));
        $this->assertSame(7, $registration->no_of_nights);
        $this->assertSame(350000.0, (float) $registration->total_amount);
        $this->assertSame($targetUnit->id, $registration->room_unit_id);
        $this->assertSame('occupied', $targetUnit->refresh()->status);
        $this->assertSame('available', RoomUnit::find($oldUnitId)->status);
    }

    public function test_available_move_units_keeps_current_room_when_free(): void
    {
        $registration = $this->makeRegistration();

        $units = $this->stayService->availableMoveUnits($registration, Carbon::today()->addDays(5));

        $this->assertTrue($units->contains('id', $registration->room_unit_id));
        $this->assertSame($registration->room_unit_id, $units->first()->id);
    }

    public function test_group_lead_extension_updates_members_and_recalculates_lead_bill(): void
    {
        $this->user->givePermissionTo('registrations.adjust_stay');

        $lead = $this->makeRegistration(['is_group_lead' => true]);
        $member = $this->makeRegistration([
            'full_name' => 'Member Guest',
            'email' => 'member'.uniqid().'@example.com',
            'parent_registration_id' => $lead->id,
            'room_rate' => 30000,
            'total_amount' => 150000,
        ]);
        $newCheckOut = Carbon::today()->addDays(5);

        Livewire::actingAs($this->user)
            ->test(ViewRegistration::class, ['record' => $lead->getRouteKey()])
            ->assertActionVisible('extendStay')
            ->callAction('extendStay', [
                'new_check_out' => $newCheckOut->format('Y-m-d'),
                'members_to_extend' => [$member->id],
            ])
            ->assertNotified();

        $lead->refresh();
        $member->refresh();

        // Lead: 7 nights at 50,000 = 350,000. Member: 7 nights at 30,000 = 210,000.
        $this->assertSame(7, $lead->no_of_nights);
        $this->assertSame(7, $member->no_of_nights);
        $this->assertSame(210000.0, (float) $member->total_amount);
        $this->assertSame(560000.0, (float) $lead->total_amount);
    }

    public function test_blade_adjust_stay_route_updates_the_stay(): void
    {
        Mail::fake();

        $this->user->givePermissionTo('registrations.adjust_stay');

        $registration = $this->makeRegistration();
        $newCheckOut = Carbon::today()->addDays(5);

        $this->actingAs($this->user)
            ->put(route('frontdesk.registrations.adjust-stay', $registration), [
                'new_check_out' => $newCheckOut->format('Y-m-d'),
            ])
            ->assertRedirect();

        $registration->refresh();

        $this->assertTrue($newCheckOut->isSameDay($registration->check_out));
        $this->assertSame(7, $registration->no_of_nights);
        $this->assertSame(350000.0, (float) $registration->total_amount);
    }

    public function test_service_refuses_extension_into_an_occupied_period(): void
    {
        $registration = $this->makeRegistration();
        $this->makeRegistration([
            'full_name' => 'Blocker Guest',
            'email' => 'blocker2'.uniqid().'@example.com',
            'room_unit_id' => $registration->room_unit_id,
            'check_in' => Carbon::today(),
            'check_out' => Carbon::today()->addDays(10),
            'no_of_nights' => 10,
            'total_amount' => 500000,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $this->stayService->adjustStay($registration, Carbon::today()->addDays(7));
    }
}
