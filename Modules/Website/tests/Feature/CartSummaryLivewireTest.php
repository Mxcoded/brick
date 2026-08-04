<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Modules\Website\Livewire\CartSummary;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\BookingCartService;
use Tests\TestCase;

class CartSummaryLivewireTest extends TestCase
{
    use DatabaseTransactions;

    public function test_empty_cart_renders_placeholder_not_crash(): void
    {
        Livewire::test(CartSummary::class)
            ->assertOk();
    }

    public function test_cart_summary_renders_items_and_total(): void
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-'.uniqid(),
            'price' => 20000,
            'capacity' => 4,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2000,
            'is_active' => true,
        ]);

        Session::put(BookingCartService::SESSION_KEY, [
            'check_in' => '2026-08-10',
            'check_out' => '2026-08-13',
            'nights' => 3,
            'items' => [
                $roomType->id => [
                    'room_type_id' => $roomType->id,
                    'room_type_name' => $roomType->name,
                    'quantity' => 2,
                    'price_per_night' => 20000,
                    'base_total' => 60000,
                    'guest_fee_per_night' => 0,
                    'guest_fee_total' => 0,
                    'total_rate' => 60000,
                    'rate_code_id' => null,
                    'capacity' => 4,
                    'adults' => 1,
                    'children' => 0,
                    'image_url' => null,
                    'nights' => 3,
                    'subtotal' => 120000,
                ],
            ],
        ]);

        Livewire::test(CartSummary::class)
            ->assertSee('Deluxe Suite')
            ->assertSee('2 room')
            ->assertSee('3 nights')
            ->assertSee('₦120,000.00')
            ->assertSee('2')
            ->assertSee('Aug 10, 2026')
            ->assertSee('Aug 13, 2026');
    }
}
