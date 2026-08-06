<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Modules\Website\Livewire\CartSidebar;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingCartService;
use Tests\TestCase;

class CartSidebarLivewireTest extends TestCase
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
            'price' => 20000,
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

    private function seedCartItem(): void
    {
        Session::put(BookingCartService::SESSION_KEY, [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'nights' => 2,
            'items' => [
                $this->roomType->id => [
                    'room_type_id' => $this->roomType->id,
                    'room_type_name' => $this->roomType->name,
                    'quantity' => 1,
                    'price_per_night' => 20000,
                    'base_total' => 40000,
                    'guest_fee_per_night' => 0,
                    'guest_fee_total' => 0,
                    'total_rate' => 40000,
                    'rate_code_id' => null,
                    'capacity' => 2,
                    'adults' => 1,
                    'children' => 0,
                    'image_url' => null,
                    'nights' => 2,
                    'subtotal' => 40000,
                ],
            ],
        ]);
    }

    public function test_empty_cart_renders_placeholder(): void
    {
        Livewire::test(CartSidebar::class)
            ->assertOk()
            ->assertSee('No rooms selected yet')
            ->assertSee('Selected Rooms');
    }

    public function test_renders_items_and_totals(): void
    {
        $this->seedCartItem();

        Livewire::test(CartSidebar::class)
            ->assertSee('Deluxe Suite')
            ->assertSee('1 room')
            ->assertSee('2 nights')
            ->assertSee('₦40,000.00');
    }

    public function test_add_room_puts_item_in_cart_and_dispatches_event(): void
    {
        $checkIn = now()->addDays(1)->format('Y-m-d');
        $checkOut = now()->addDays(3)->format('Y-m-d');

        Livewire::test(CartSidebar::class)
            ->call('add', $this->roomType->id, 1, $checkIn, $checkOut, 1, 0)
            ->assertDispatched('cart-updated')
            ->assertSee('Deluxe Suite');

        $cart = Session::get(BookingCartService::SESSION_KEY);
        $this->assertNotEmpty($cart['items']);
        $this->assertSame($this->roomType->id, $cart['items'][$this->roomType->id]['room_type_id']);
    }

    public function test_add_with_over_capacity_dispatches_error(): void
    {
        $checkIn = now()->addDays(1)->format('Y-m-d');
        $checkOut = now()->addDays(3)->format('Y-m-d');

        Livewire::test(CartSidebar::class)
            ->call('add', $this->roomType->id, 1, $checkIn, $checkOut, 5, 0)
            ->assertDispatched('cart-error');

        $this->assertEmpty(Session::get(BookingCartService::SESSION_KEY, [])['items'] ?? []);
    }

    public function test_remove_removes_item_and_dispatches_event(): void
    {
        $this->seedCartItem();

        Livewire::test(CartSidebar::class)
            ->call('remove', $this->roomType->id)
            ->assertDispatched('cart-updated')
            ->assertSee('No rooms selected yet');

        $this->assertEmpty(Session::get(BookingCartService::SESSION_KEY, [])['items'] ?? []);
    }

    public function test_clear_empties_cart_and_dispatches_event(): void
    {
        $this->seedCartItem();

        Livewire::test(CartSidebar::class)
            ->call('clear')
            ->assertDispatched('cart-updated')
            ->assertSee('No rooms selected yet');

        $this->assertEmpty(Session::get(BookingCartService::SESSION_KEY, [])['items'] ?? []);
    }

    public function test_book_page_renders_livewire_cart_sidebar(): void
    {
        $response = $this->get(route('website.book', [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee('Selected Rooms');
        $response->assertSee('No rooms selected yet');
        $response->assertSee('Deluxe Suite');
        $response->assertSee('Select Room');
    }
}
