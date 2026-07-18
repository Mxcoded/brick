<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\User;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class RegistrationFlowTest extends ModuleTestCase
{
    private User $user;

    private Guest $guest;

    private ChargeType $chargeType;

    private RoomType $roomType;

    private RoomUnit $roomUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
        $this->guest = Guest::factory()->create();
        $this->chargeType = ChargeType::factory()->create();
        $this->roomType = RoomType::factory()->create();
        $this->roomUnit = RoomUnit::factory()->create(['room_type_id' => $this->roomType->id]);
    }

    public function test_can_create_walk_in_registration()
    {
        $response = $this->post(route('frontdesk.registrations.storeWalkin'), [
            'guest_id' => $this->guest->id,
            'full_name' => 'John Doe',
            'contact_number' => '08012345678',
            'email' => 'john@example.com',
            'room_type_id' => $this->roomType->id,
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'room_rate' => 10000,
            'no_of_nights' => 2,
            'total_amount' => 20000,
            'agreed_to_policies' => true,
        ]);

        $this->assertDatabaseHas('registrations', ['full_name' => 'John Doe']);
        $response->assertSessionHas('success');
    }

    public function test_can_post_folio_charge()
    {
        $registration = Registration::factory()->checkedIn()->create();

        $response = $this->post(route('frontdesk.registrations.charges.store', $registration), [
            'charge_type_id' => $this->chargeType->id,
            'description' => 'Mini Bar',
            'quantity' => 2,
            'unit_price' => 1500,
            'amount' => 3000,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('folio_charges', [
            'registration_id' => $registration->id,
            'amount' => 3000,
        ]);
    }

    public function test_can_checkout_registration()
    {
        $registration = Registration::factory()->checkedIn()->create([
            'total_amount' => 20000,
            'room_rate' => 10000,
        ]);

        $response = $this->post(route('frontdesk.registrations.checkout', $registration), [
            'payment_method' => 'cash',
            'amount_paid' => 20000,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'stay_status' => 'checked_out',
        ]);
    }

    public function test_can_create_night_audit()
    {
        ChargeType::factory()->roomNight()->create();

        $response = $this->post(route('frontdesk.audit.store'), [
            'audit_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('night_audits', ['status' => 'completed']);
    }

    public function test_can_complete_night_audit()
    {
        $registration = Registration::factory()->checkedIn()->create([
            'room_rate' => 15000,
            'total_amount' => 15000,
            'room_unit_id' => $this->roomUnit->id,
        ]);

        ChargeType::factory()->roomNight()->create();

        $response = $this->post(route('frontdesk.audit.store'), [
            'audit_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('night_audits', ['status' => 'completed']);
    }
}
