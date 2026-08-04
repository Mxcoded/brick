<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Website\Livewire\BookingSummary;
use Modules\Website\Models\RoomType;
use Tests\TestCase;

class BookingSummaryLivewireTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRoomType(array $overrides = []): RoomType
    {
        return RoomType::create(array_merge([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-'.uniqid(),
            'price' => 20000,
            'capacity' => 4,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2000,
            'is_active' => true,
        ], $overrides));
    }

    public function test_mount_renders_initial_totals(): void
    {
        $roomType = $this->makeRoomType();

        $component = Livewire::test(BookingSummary::class, [
            'roomTypeId' => $roomType->id,
            'checkIn' => '2026-08-10',
            'checkOut' => '2026-08-13',
            'adults' => 2,
            'children' => 0,
        ]);

        $component
            ->assertSee('Deluxe Suite')
            ->assertSee('₦60,000.00');
    }

    public function test_summary_updated_event_recomputes_guest_fee(): void
    {
        $roomType = $this->makeRoomType(['extra_adult_fee' => 5000]);

        Livewire::test(BookingSummary::class, [
            'roomTypeId' => $roomType->id,
            'checkIn' => '2026-08-10',
            'checkOut' => '2026-08-11',
            'adults' => 1,
            'children' => 0,
        ])
            ->dispatch('summaryUpdated',
                roomTypeId: $roomType->id,
                checkIn: '2026-08-10',
                checkOut: '2026-08-11',
                adults: 3,
                children: 0,
            )
            ->assertSee('Extra Guest Fee')
            ->assertSee('₦5,000.00')
            ->assertSee('₦25,000.00');
    }

    public function test_invalid_dates_render_placeholder_not_crash(): void
    {
        $roomType = $this->makeRoomType();

        Livewire::test(BookingSummary::class, [
            'roomTypeId' => $roomType->id,
            'checkIn' => '',
            'checkOut' => '',
            'adults' => 1,
            'children' => 0,
        ])
            ->assertSee('Deluxe Suite')
            ->assertSee('₦0.00');
    }
}
