<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Website\Services\BookingCartService;

class CartSummary extends Component
{
    /**
     * Re-render whenever the cart session changes (add/remove room or add-on).
     */
    #[On('cart-updated')]
    public function refresh(): void
    {
        // Re-render pulls fresh data from the session cart.
    }

    public function render(): View
    {
        $cart = app(BookingCartService::class)->getCartSummary();

        // Push authoritative totals back to the browser so the review panel
        // and CTA totals stay in sync with the server-side cart (cart flow).
        // Named params so the browser event's `detail` carries the keys.
        $this->dispatch(
            'booking-summary-updated',
            total: (float) $cart['total'],
            baseTotal: (float) $cart['base_total'],
            guestFeeTotal: (float) $cart['guest_fee_total'],
            addonTotal: (float) ($cart['addon_total'] ?? 0),
            hasRate: true,
        );

        return view('website::livewire.cart-summary', compact('cart'));
    }
}
