<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Services\BookingEngine;
use App\Services\PropertyService;
use App\Values\BookingEngineRequest;
use Carbon\Carbon;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;
use Modules\Website\Models\Booking;

class BookingEngineTest extends ModuleTestCase
{
    private BookingEngine $engine;
    private Property $property;
    private RoomType $roomType;
    private RoomUnit $roomUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->property = Property::factory()->create();

        $propertyService = app(PropertyService::class);
        $propertyService->setCurrent($this->property);

        $this->roomType = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'price' => 15000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $this->roomUnit = RoomUnit::factory()->create([
            'room_type_id' => $this->roomType->id,
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        $this->engine = app(BookingEngine::class);
    }

    public function test_can_search_availability()
    {
        $result = $this->engine->search([
            'property_id' => $this->property->id,
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(7)->format('Y-m-d'),
            'adults' => 1,
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey($this->property->id, $result['properties']);
        $this->assertCount(1, $result['properties'][$this->property->id]['room_types']);
        $this->assertEquals($this->roomType->name, $result['properties'][$this->property->id]['room_types'][0]['name']);
    }

    public function test_search_returns_empty_when_no_units_available()
    {
        $this->roomUnit->update(['status' => 'maintenance']);

        $result = $this->engine->search([
            'property_id' => $this->property->id,
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(0, $result['properties'][$this->property->id]['room_types']);
    }

    public function test_can_create_booking()
    {
        $request = new BookingEngineRequest([
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '08012345678',
            'payment_method' => 'pay_on_arrival',
            'rooms' => [
                [
                    'room_type_id' => $this->roomType->id,
                    'check_in' => now()->addDays(5)->format('Y-m-d'),
                    'check_out' => now()->addDays(7)->format('Y-m-d'),
                    'adults' => 1,
                    'quantity' => 1,
                ],
            ],
        ]);

        $result = $this->engine->createBooking($request);

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->bookings);
        $this->assertDatabaseHas('bookings', [
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'room_type_id' => $this->roomType->id,
        ]);
    }

    public function test_create_booking_fails_when_unavailable()
    {
        $this->roomUnit->update(['status' => 'maintenance']);

        $request = new BookingEngineRequest([
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '08098765432',
            'payment_method' => 'pay_on_arrival',
            'rooms' => [
                [
                    'room_type_id' => $this->roomType->id,
                    'check_in' => now()->addDays(5)->format('Y-m-d'),
                    'check_out' => now()->addDays(7)->format('Y-m-d'),
                    'adults' => 1,
                    'quantity' => 1,
                ],
            ],
        ]);

        $result = $this->engine->createBooking($request);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->error);
    }

    public function test_can_create_multi_room_booking()
    {
        $roomType2 = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'price' => 20000,
            'capacity' => 3,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $roomType2->id,
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        $request = new BookingEngineRequest([
            'guest_name' => 'Mike Smith',
            'guest_email' => 'mike@example.com',
            'guest_phone' => '08011112222',
            'payment_method' => 'pay_on_arrival',
            'rooms' => [
                [
                    'room_type_id' => $this->roomType->id,
                    'check_in' => now()->addDays(5)->format('Y-m-d'),
                    'check_out' => now()->addDays(7)->format('Y-m-d'),
                    'adults' => 1,
                    'quantity' => 1,
                ],
                [
                    'room_type_id' => $roomType2->id,
                    'check_in' => now()->addDays(5)->format('Y-m-d'),
                    'check_out' => now()->addDays(7)->format('Y-m-d'),
                    'adults' => 2,
                    'quantity' => 1,
                ],
            ],
        ]);

        $result = $this->engine->createBooking($request);

        $this->assertTrue($result->success);
        $this->assertCount(2, $result->bookings);
        $this->assertNotNull($result->bookingGroupId);
        $this->assertEquals(70000, $result->totalAmount);
    }

    public function test_can_confirm_booking()
    {
        $booking = Booking::create([
            'booking_reference' => 'BK-TEST',
            'guest_name' => 'Test Guest',
            'guest_email' => 'test@example.com',
            'guest_phone' => '08000000000',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
            'total_amount' => 30000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'property_id' => $this->property->id,
        ]);

        $result = $this->engine->confirmBooking($booking);

        $this->assertTrue($result);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function test_confirm_already_paid_booking_is_idempotent()
    {
        $booking = Booking::create([
            'booking_reference' => 'BK-PAID',
            'guest_name' => 'Paid Guest',
            'guest_email' => 'paid@example.com',
            'guest_phone' => '08011111111',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
            'total_amount' => 30000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'property_id' => $this->property->id,
        ]);

        $result = $this->engine->confirmBooking($booking);

        $this->assertTrue($result);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function test_can_cancel_booking()
    {
        $booking = Booking::create([
            'booking_reference' => 'BK-CANCEL',
            'guest_name' => 'Cancel Guest',
            'guest_email' => 'cancel@example.com',
            'guest_phone' => '08022222222',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
            'total_amount' => 30000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'property_id' => $this->property->id,
        ]);

        $result = $this->engine->cancelBooking($booking, 'Guest requested cancellation');

        $this->assertTrue($result);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->assertStringContainsString('Guest requested cancellation', $booking->fresh()->admin_notes);
    }

    public function test_can_create_walkin_registration()
    {
        $registration = $this->engine->createRegistration([
            'full_name' => 'Walkin Guest',
            'contact_number' => '08033334444',
            'email' => 'walkin@example.com',
            'gender' => 'male',
            'room_type_id' => $this->roomType->id,
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'room_rate' => 15000,
            'billing_type' => 'consolidate',
            'payment_method' => 'cash',
            'front_desk_agent' => 'Agent Smith',
        ]);

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'full_name' => 'Walkin Guest',
            'contact_number' => '08033334444',
        ]);
        $this->assertEquals('draft_by_guest', $registration->stay_status);
        $this->assertStringStartsWith('FD', $registration->reservation_code);
    }

    public function test_create_walkin_registration_for_future_date()
    {
        $registration = $this->engine->createRegistration([
            'full_name' => 'Future Guest',
            'contact_number' => '08044445555',
            'email' => 'future@example.com',
            'room_type_id' => $this->roomType->id,
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'no_of_guests' => 2,
            'room_rate' => 15000,
            'front_desk_agent' => 'Agent Smith',
        ]);

        $this->assertEquals('reserved', $registration->stay_status);
    }

    public function test_create_registration_throws_on_maintenance_unit()
    {
        $this->roomUnit->update(['status' => 'maintenance']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('currently unavailable');

        $this->engine->createRegistration([
            'full_name' => 'Fail Guest',
            'contact_number' => '08055556666',
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'front_desk_agent' => 'Agent',
        ]);
    }

    public function test_create_registration_finds_existing_guest()
    {
        $existingGuest = Guest::factory()->create([
            'contact_number' => '08066667777',
            'full_name' => 'Original Name',
            'email' => 'existing@example.com',
        ]);

        $registration = $this->engine->createRegistration([
            'full_name' => 'Updated Name',
            'contact_number' => '08066667777',
            'email' => 'existing@example.com',
            'room_type_id' => $this->roomType->id,
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'room_rate' => 15000,
            'front_desk_agent' => 'Agent',
        ]);

        $this->assertEquals($existingGuest->id, $registration->guest_id);
        $this->assertDatabaseHas('guests', [
            'id' => $existingGuest->id,
            'full_name' => 'Updated Name',
        ]);
    }

    public function test_create_registration_resolves_room_allocation_from_unit()
    {
        $registration = $this->engine->createRegistration([
            'full_name' => 'Room Allocation Guest',
            'contact_number' => '08077778888',
            'room_unit_id' => $this->roomUnit->id,
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
            'no_of_guests' => 1,
            'room_rate' => 15000,
            'front_desk_agent' => 'Agent',
        ]);

        $this->assertStringContainsString('Room '.$this->roomUnit->room_number, $registration->room_allocation);
    }

    public function test_create_booking_with_existing_guest_profile()
    {
        $guest = Guest::factory()->create();

        $request = new BookingEngineRequest([
            'guest_name' => $guest->full_name,
            'guest_email' => $guest->email,
            'guest_phone' => $guest->contact_number,
            'guest_profile_id' => $guest->id,
            'payment_method' => 'pay_on_arrival',
            'rooms' => [
                [
                    'room_type_id' => $this->roomType->id,
                    'check_in' => now()->addDays(10)->format('Y-m-d'),
                    'check_out' => now()->addDays(12)->format('Y-m-d'),
                    'adults' => 1,
                    'quantity' => 1,
                ],
            ],
        ]);

        $result = $this->engine->createBooking($request);

        $this->assertTrue($result->success);
        $this->assertDatabaseHas('bookings', [
            'guest_profile_id' => $guest->id,
        ]);
    }

    public function test_search_all_properties()
    {
        $this->engine->search([
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(7)->format('Y-m-d'),
            'adults' => 1,
        ]);

        $this->expectNotToPerformAssertions();
    }
}
