<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\User;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class UpgradeTest extends ModuleTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
    }

    public function test_can_get_upgrade_options()
    {
        $currentRoomType = RoomType::factory()->create(['name' => 'Standard', 'price' => 10000]);
        $betterRoomType = RoomType::factory()->create(['name' => 'Deluxe', 'price' => 20000]);
        $bestRoomType = RoomType::factory()->create(['name' => 'Suite', 'price' => 35000]);

        $roomUnit = RoomUnit::factory()->create([
            'room_type_id' => $currentRoomType->id,
            'status' => 'occupied',
        ]);
        RoomUnit::factory()->create(['room_type_id' => $betterRoomType->id, 'status' => 'available']);
        RoomUnit::factory()->create(['room_type_id' => $bestRoomType->id, 'status' => 'available']);

        $registration = Registration::factory()->checkedIn()->create([
            'room_type_id' => $currentRoomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 10000,
        ]);

        $response = $this->get(route('frontdesk.registrations.upgrade-options', $registration));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'Deluxe']);
        $response->assertJsonFragment(['name' => 'Suite']);
    }

    public function test_can_process_upgrade()
    {
        $currentRoomType = RoomType::factory()->create(['name' => 'Standard', 'price' => 10000]);
        $betterRoomType = RoomType::factory()->create(['name' => 'Deluxe', 'price' => 20000]);

        $oldUnit = RoomUnit::factory()->create([
            'room_type_id' => $currentRoomType->id,
            'status' => 'occupied',
        ]);
        $newUnit = RoomUnit::factory()->create([
            'room_type_id' => $betterRoomType->id,
            'status' => 'available',
        ]);

        ChargeType::factory()->roomUpgrade()->create();
        ChargeType::factory()->roomNight()->create();

        $registration = Registration::factory()->checkedIn()->create([
            'room_type_id' => $currentRoomType->id,
            'room_unit_id' => $oldUnit->id,
            'room_rate' => 10000,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_nights' => 2,
        ]);

        $response = $this->post(route('frontdesk.registrations.upgrade', $registration), [
            'room_unit_id' => $newUnit->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('room_units', ['id' => $oldUnit->id, 'cleaning_status' => 'dirty']);
        $this->assertDatabaseHas('room_units', ['id' => $newUnit->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('folio_charges', ['registration_id' => $registration->id]);
    }
}
