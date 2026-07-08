<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\User;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class GroupBookingTest extends ModuleTestCase
{
    private User $user;
    private string $groupId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
        $this->groupId = 'GRP-'.strtoupper(fake()->bothify('??##??##'));
    }

    public function test_can_create_group_lead()
    {
        $response = $this->post(route('frontdesk.registrations.storeWalkin'), [
            'full_name' => 'Group Lead',
            'contact_number' => '08011111111',
            'email' => 'lead@example.com',
            'room_type_id' => RoomType::factory()->create()->id,
            'room_unit_id' => RoomUnit::factory()->create()->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'room_rate' => 15000,
            'no_of_nights' => 2,
            'total_amount' => 30000,
            'agreed_to_policies' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('registrations', ['full_name' => 'Group Lead']);
    }

    public function test_group_index_shows_groups()
    {
        $lead = Registration::factory()->checkedIn()->create([
            'is_group_lead' => true,
            'booking_group_id' => $this->groupId,
            'total_amount' => 50000,
        ]);
        Registration::factory()->checkedIn()->create([
            'booking_group_id' => $this->groupId,
            'parent_registration_id' => $lead->id,
            'total_amount' => 30000,
        ]);

        $response = $this->get(route('frontdesk.groups.index'));

        $response->assertOk();
    }

    public function test_can_bulk_checkin_group()
    {
        $roomType = RoomType::factory()->create();
        $roomUnit = RoomUnit::factory()->create(['room_type_id' => $roomType->id]);
        $memberRoomUnit = RoomUnit::factory()->create(['room_type_id' => $roomType->id]);

        $lead = Registration::factory()->checkedIn()->create([
            'is_group_lead' => true,
            'booking_group_id' => $this->groupId,
            'room_unit_id' => $roomUnit->id,
        ]);
        $member = Registration::factory()->pending()->create([
            'booking_group_id' => $this->groupId,
            'parent_registration_id' => $lead->id,
            'room_unit_id' => $memberRoomUnit->id,
        ]);

        $response = $this->post(route('frontdesk.groups.bulk-checkin', $lead));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('registrations', [
            'id' => $member->id,
            'stay_status' => 'checked_in',
        ]);
    }

    public function test_can_bulk_checkout_group()
    {
        $roomType = RoomType::factory()->create();
        $roomUnit = RoomUnit::factory()->create(['room_type_id' => $roomType->id]);
        $memberRoomUnit = RoomUnit::factory()->create(['room_type_id' => $roomType->id]);

        $lead = Registration::factory()->checkedIn()->create([
            'is_group_lead' => true,
            'booking_group_id' => $this->groupId,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 10000,
            'total_amount' => 20000,
        ]);
        $member = Registration::factory()->checkedIn()->create([
            'booking_group_id' => $this->groupId,
            'parent_registration_id' => $lead->id,
            'room_unit_id' => $memberRoomUnit->id,
            'room_rate' => 10000,
            'total_amount' => 20000,
        ]);

        $response = $this->post(route('frontdesk.groups.bulk-checkout', $lead), [
            'checkout_lead' => true,
        ]);

        $response->assertSessionHas('success');
    }

    public function test_can_delete_empty_group()
    {
        Registration::factory()->pending()->create([
            'is_group_lead' => true,
            'booking_group_id' => $this->groupId,
        ]);

        $lead = Registration::where('booking_group_id', $this->groupId)->first();

        $response = $this->delete(route('frontdesk.groups.destroy', $lead));

        $response->assertSessionHas('success');
    }
}
