<?php

namespace Modules\Website\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class BookingDraftTest extends TestCase
{
    use DatabaseTransactions;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite-'.uniqid(),
            'price' => 25000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);
    }

    public function test_guest_can_save_booking_draft(): void
    {
        $response = $this->post(route('website.booking.draft'), [
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'adults' => 2,
            'children' => 1,
            'room_type_id' => (string) $this->roomType->id,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertOk()->assertJson(['saved' => true]);

        $this->assertSame('John Doe', session('booking_draft.guest_name'));
        $this->assertSame(2, session('booking_draft.adults'));
        $this->assertSame(1, session('booking_draft.children'));
        $this->assertSame('pay_on_arrival', session('booking_draft.payment_method'));
        $this->assertSame($this->roomType->id, session('booking_draft.room_type_id'));
    }

    public function test_draft_sanitizes_fields_and_never_persists_passwords(): void
    {
        $this->post(route('website.booking.draft'), [
            'guest_name' => '  Jane  ',
            'password' => 'secret-password',
            'website' => 'spam-honeypot',
            'payment_method' => 'crypto',
            'adults' => '3',
        ]);

        $draft = session('booking_draft', []);

        $this->assertSame('Jane', $draft['guest_name'] ?? null);
        $this->assertSame(3, $draft['adults'] ?? null);
        $this->assertSame('paystack', $draft['payment_method'] ?? null);
        $this->assertArrayNotHasKey('password', $draft);
        $this->assertArrayNotHasKey('website', $draft);
    }

    public function test_draft_restores_booking_page_for_anonymous_guest(): void
    {
        $this->withSession([
            'booking_draft' => [
                'guest_name' => 'John Doe',
                'guest_email' => 'john@example.com',
                'room_type_id' => $this->roomType->id,
                'check_in_date' => now()->addDays(1)->format('Y-m-d'),
                'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            ],
        ]);

        $response = $this->get(route('website.booking'));

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee($this->roomType->name);
        $response->assertSee('Deluxe Suite — room for 2 guests');
    }

    public function test_explicit_url_room_selection_wins_over_draft(): void
    {
        $otherRoom = RoomType::create([
            'name' => 'Standard Room',
            'slug' => 'standard-room-'.uniqid(),
            'price' => 15000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $this->withSession([
            'booking_draft' => ['room_type_id' => $this->roomType->id],
        ]);

        $response = $this->get(route('website.booking', ['room_type_id' => $otherRoom->id]));

        $response->assertOk();
        $response->assertSee('Standard Room — room for 2 guests');
        $response->assertDontSee('Deluxe Suite — room for 2 guests');
    }

    public function test_draft_does_not_override_logged_in_guest_profile(): void
    {
        $user = User::create([
            'name' => 'Profile Owner',
            'email' => 'profile@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'guest',
        ]);

        Guest::create([
            'user_id' => $user->id,
            'full_name' => 'Profile Owner',
            'email' => 'profile@example.com',
            'contact_number' => '08012345678',
            'nationality' => 'Nigerian',
        ]);

        $this->actingAs($user)
            ->withSession([
                'booking_draft' => ['guest_name' => 'Draft Name'],
            ]);

        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('Profile Owner');
        $response->assertDontSee('Draft Name');
    }

    public function test_draft_is_cleared_when_confirmation_page_is_viewed(): void
    {
        $booking = Booking::create([
            'booking_reference' => 'BK'.now()->year.strtoupper(substr(uniqid(), -4)),
            'room_type_id' => $this->roomType->id,
            'source' => 'website',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 50000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'pay_on_arrival',
        ]);

        $this->withSession([
            'just_booked_ref' => $booking->booking_reference,
            'booking_draft' => ['guest_name' => 'John Doe'],
        ]);

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertOk();
        $response->assertSessionMissing('booking_draft');
    }
}
