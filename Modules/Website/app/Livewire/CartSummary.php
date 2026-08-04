<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Website\Services\BookingCartService;

class CartSummary extends Component
{
    public function render(): View
    {
        $cart = app(BookingCartService::class)->getCartSummary();

        return view('website::livewire.cart-summary', compact('cart'));
    }
}
