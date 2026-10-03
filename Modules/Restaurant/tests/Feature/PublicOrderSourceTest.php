<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Table;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

/**
 * The three ways into the restaurant do not share an audience.
 *
 * These tests pin the rule that AuthorizePublicOrderSource enforces on the
 * shared `/restaurant/{type}/{source}/...` routes: online is public, room is
 * limited to the guest actually staying in that room, and table ordering is
 * not a public flow at all.
 */
class PublicOrderSourceTest extends TestCase
{
    use DatabaseTransactions;

    private MenuItem $burger;

    private Room $room;

    private User $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $category = MenuCategory::create(['name' => 'Source Test']);
        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1500.00,
            'is_available' => true,
        ]);

        $this->room = Room::create([
            'name' => 'Room '.uniqid(),
            'slug' => 'room-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'status' => 'available',
        ]);

        $this->guest = User::factory()->create([
            'type' => 'guest',
            'status' => 'active',
        ]);
    }

    private function checkGuestInto(Room $room, ?User $user = null): Registration
    {
        $user ??= $this->guest;

        $roomType = RoomType::create([
            'name' => 'Source Test Type',
            'slug' => 'source-type-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'SR-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);

        $guest = Guest::create([
            'full_name' => 'Source Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => $user->email,
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $user->name,
            'contact_number' => $guest->contact_number,
            'email' => $user->email,
            'nationality' => 'Nigerian',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => now()->subDay(),
            'check_out' => now()->addDay(),
            'stay_status' => 'checked_in',
            'no_of_nights' => 2,
            'total_amount' => 100000,
        ]);
    }

    public function test_the_online_menu_stays_open_to_the_public(): void
    {
        $this->get('/restaurant/online/menu')->assertOk()->assertSee('Burger');
    }

    public function test_the_landing_page_only_starts_online_ordering(): void
    {
        // The public landing used to carry a table and a room picker. Both are
        // gone: a visitor cannot choose a table to order at, and room service
        // belongs to the guest dashboard, not this page.
        $this->get(route('restaurant.landing'))
            ->assertOk()
            ->assertSee('Order Online Now')
            ->assertDontSee('Select Table')
            ->assertDontSee('Select Room');
    }

    public function test_the_table_menu_is_not_reachable_by_the_public(): void
    {
        $table = Table::create(['number' => 'T-'.uniqid()]);

        $this->get("/restaurant/table/{$table->id}/menu")->assertNotFound();
    }

    public function test_a_visitor_cannot_order_room_service(): void
    {
        $this->get("/restaurant/room/{$this->room->id}/menu")->assertForbidden();
    }

    public function test_a_signed_in_guest_cannot_order_to_a_room_they_are_not_in(): void
    {
        $other = User::factory()->create(['type' => 'guest', 'status' => 'active']);

        $this->actingAs($other)
            ->get("/restaurant/room/{$this->room->id}/menu")
            ->assertForbidden();
    }

    public function test_a_checked_in_guest_can_order_to_their_own_room(): void
    {
        $this->checkGuestInto($this->room);

        $this->actingAs($this->guest)
            ->get("/restaurant/room/{$this->room->id}/menu")
            ->assertOk()
            ->assertSee('Burger');
    }

    public function test_a_checked_in_guest_cannot_reach_another_room(): void
    {
        $this->checkGuestInto($this->room);

        $otherRoom = Room::create([
            'name' => 'Room '.uniqid(),
            'slug' => 'room-'.uniqid(),
            'price' => 60000,
            'capacity' => 2,
            'status' => 'available',
        ]);

        $this->actingAs($this->guest)
            ->get("/restaurant/room/{$otherRoom->id}/menu")
            ->assertForbidden();
    }

    public function test_guest_dashboard_offers_room_service_only_for_the_guests_own_room(): void
    {
        $ownRoomUrl = route('restaurant.menu', ['type' => 'room', 'source' => $this->room->id]);

        // Not checked in yet: no room-service link at all.
        $this->actingAs($this->guest)
            ->get(route('guest.dashboard'))
            ->assertOk()
            ->assertDontSee($ownRoomUrl);

        // Checked in: the dashboard points at the guest's own room.
        $this->checkGuestInto($this->room);

        $this->actingAs($this->guest)
            ->get(route('guest.dashboard'))
            ->assertOk()
            ->assertSee($ownRoomUrl);
    }
}
