<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Website\Models\Addon;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\WebsiteRateService;

class BookingSummary extends Component
{
    public ?int $roomTypeId = null;

    public ?string $checkIn = '';

    public ?string $checkOut = '';

    public int $adults = 1;

    public int $children = 0;

    /** Selected add-on ids for the non-cart (single-room) flow. */
    public array $addons = [];

    public function mount(?int $roomTypeId = null, ?string $checkIn = '', ?string $checkOut = '', int $adults = 1, int $children = 0, array $addons = []): void
    {
        $this->roomTypeId = $roomTypeId;
        $this->checkIn = (string) $checkIn;
        $this->checkOut = (string) $checkOut;
        $this->adults = max(1, $adults);
        $this->children = max(0, $children);
        $this->addons = array_values(array_map('intval', $addons));
    }

    /**
     * Called by the booking form's JS (booking-form.js) whenever a
     * price-relevant field changes — no hand-rolled AJAX pricing endpoints.
     */
    #[On('summaryUpdated')]
    public function handleSummaryUpdated($roomTypeId = null, $checkIn = '', $checkOut = '', $adults = 1, $children = 0, $addons = []): void
    {
        $this->roomTypeId = $roomTypeId ? (int) $roomTypeId : null;
        $this->checkIn = (string) $checkIn;
        $this->checkOut = (string) $checkOut;
        $this->adults = max(1, (int) $adults);
        $this->children = max(0, (int) $children);
        $this->addons = array_values(array_filter(array_map('intval', (array) $addons)));
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

        // Add-ons are computed server-side so the review panel + totals stay
        // authoritative (no hand-rolled client-side pricing).
        $addonTotal = 0;
        $addonItems = collect();
        if ($this->addons && $roomType && $this->checkIn && $this->checkOut) {
            $nights = Carbon::parse($this->checkIn)->diffInDays($this->checkOut) ?: 1;
            $addonItems = Addon::whereIn('id', $this->addons)->active()->get();
            foreach ($addonItems as $addon) {
                $addonTotal += $addon->totalFor($nights);
            }
        }

        // Push the authoritative totals back to the browser so non-Livewire
        // elements (the review strip's #reviewTotal) stay in sync. This fires
        // on every render — initial mount included — replacing the old
        // /api/room-rate AJAX in booking-form.js. Named params so the browser
        // event's `detail` carries the totals as keys (Livewire v4 collects
        // variadic params verbatim).
        $this->dispatch(
            'booking-summary-updated',
            total: (float) (($rate['total'] ?? 0) + $addonTotal),
            baseTotal: (float) ($rate['base_total'] ?? 0),
            guestFeeTotal: (float) ($rate['guest_fee_total'] ?? 0),
            addonTotal: (float) $addonTotal,
            pricePerNight: (float) ($rate['price_per_night'] ?? 0),
            hasRate: $rate !== null,
        );

        return view('website::livewire.booking-summary', compact('roomType', 'rate', 'addonTotal', 'addonItems'));
    }
}
