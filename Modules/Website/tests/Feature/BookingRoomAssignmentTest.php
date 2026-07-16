<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BookingRoomAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private RoomType $roomType;

    private RoomUnit $unitA;

    private RoomUnit $unitB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::WEBSITE_ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'website.bookings.update', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);
        $this->actingAs($this->admin);

        $this->roomType = RoomType::create([
            'name' => 'Assignment Suite',
            'slug' => 'assignment-suite',
            'price' => 20000,
            'capacity' => 2,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->unitA = RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '201',
            'floor' => 2,
            'status' => 'available',
        ]);

        $this->unitB = RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '202',
            'floor' => 2,
            'status' => 'available',
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'booking_reference' => 'BK'.uniqid(),
            'room_type_id' => $this->roomType->id,
            'room_unit_id' => null,
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addDays(12)->format('Y-m-d'),
            'adults' => 2,
            'total_amount' => 40000,
            'payment_status' => 'pending',
            'status' => 'confirmed',
        ], $overrides));
    }

    /**
     * Reproduces the reported bug: an available room unit cannot be assigned to
     * a confirmed booking simply because other unassigned bookings consume
     * pool "slots" ahead of it in the availability calculation.
     */
    public function test_available_unit_can_be_assigned_even_with_other_unassigned_bookings()
    {
        // An unassigned booking for the same dates consumes a pool slot.
        $this->makeBooking(['room_unit_id' => null, 'booking_reference' => 'BK'.uniqid()]);

        $booking = $this->makeBooking(['status' => 'confirmed']);

        $response = $this->post(route('website.admin.bookings.assign-room', $booking->id), [
            'room_unit_id' => $this->unitA->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_unit_id' => $this->unitA->id,
        ]);
    }

    /**
     * A unit that genuinely conflicts with another booking must still be rejected.
     */
    public function test_unit_with_conflicting_booking_cannot_be_assigned()
    {
        // Unit B is already assigned to another confirmed booking in the range.
        $this->makeBooking([
            'booking_reference' => 'BK'.uniqid(),
            'room_unit_id' => $this->unitB->id,
            'status' => 'confirmed',
        ]);

        $booking = $this->makeBooking(['status' => 'confirmed']);

        $response = $this->post(route('website.admin.bookings.assign-room', $booking->id), [
            'room_unit_id' => $this->unitB->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_unit_id' => null,
        ]);
    }

    /**
     * A unit in maintenance (hard-blocked status) must still be rejected.
     */
    public function test_unit_in_maintenance_cannot_be_assigned()
    {
        $this->unitA->update(['status' => 'maintenance']);

        $booking = $this->makeBooking(['status' => 'confirmed']);

        $response = $this->post(route('website.admin.bookings.assign-room', $booking->id), [
            'room_unit_id' => $this->unitA->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_unit_id' => null,
        ]);
    }
}
