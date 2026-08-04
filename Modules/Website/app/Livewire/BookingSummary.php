<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\WebsiteRateService;

class BookingSummary extends Component
{
    public ?int $roomTypeId = null;

    public ?string $checkIn = '';

    public ?string $checkOut = '';

    public int $adults = 1;

    public int $children = 0;

    public function mount(?int $roomTypeId = null, ?string $checkIn = '', ?string $checkOut = '', int $adults = 1, int $children = 0): void
    {
        $this->roomTypeId = $roomTypeId;
        $this->checkIn = (string) $checkIn;
        $this->checkOut = (string) $checkOut;
        $this->adults = max(1, $adults);
        $this->children = max(0, $children);
    }

    /**
     * Called by the booking form's JS (booking-form.js) whenever a
     * price-relevant field changes — no hand-rolled AJAX pricing endpoints.
     */
    #[On('summaryUpdated')]
    public function handleSummaryUpdated($roomTypeId = null, $checkIn = '', $checkOut = '', $adults = 1, $children = 0): void
    {
        $this->roomTypeId = $roomTypeId ? (int) $roomTypeId : null;
        $this->checkIn = (string) $checkIn;
        $this->checkOut = (string) $checkOut;
        $this->adults = max(1, (int) $adults);
        $this->children = max(0, (int) $children);
    }

    public function render(): View
    {
        $roomType = $this->roomTypeId ? RoomType::find($this->roomTypeId) : null;
        $rate = null;

        if ($roomType && $this->checkIn && $this->checkOut) {
            $rate = app(WebsiteRateService::class)->calculateWithGuests(
                $roomType,
                $this->checkIn,
                $this->checkOut,
                $this->adults,
                $this->children,
            );
        }

        // Push the authoritative totals back to the browser so non-Livewire
        // elements (the review strip's #reviewTotal) stay in sync. This fires
        // on every render — initial mount included — replacing the old
        // /api/room-rate AJAX in booking-form.js.
        $this->dispatch('booking-summary-updated', [
            'total' => $rate['total'] ?? 0,
            'baseTotal' => $rate['base_total'] ?? 0,
            'guestFeeTotal' => $rate['guest_fee_total'] ?? 0,
            'pricePerNight' => $rate['price_per_night'] ?? 0,
            'hasRate' => $rate !== null,
        ]);

        return view('website::livewire.booking-summary', compact('roomType', 'rate'));
    }
}
