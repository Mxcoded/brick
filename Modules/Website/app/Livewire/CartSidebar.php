<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Modules\Website\Services\BookingCartService;

class CartSidebar extends Component
{
    public function add(
        int $roomTypeId,
        int $quantity = 1,
        ?string $checkIn = null,
        ?string $checkOut = null,
        int $adults = 1,
        int $children = 0
    ): void {
        $validator = Validator::make([
            'room_type_id' => $roomTypeId,
            'quantity' => $quantity,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => $adults,
            'children' => $children,
        ], [
            'room_type_id' => 'required|integer|exists:room_types,id',
            'quantity' => 'required|integer|min:1|max:10',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            $this->dispatch('cart-error', message: $validator->errors()->first());

            return;
        }

        $result = app(BookingCartService::class)->add(
            $roomTypeId,
            $quantity,
            $validator->validated()['check_in'],
            $validator->validated()['check_out'],
            $validator->validated()['adults'] ?? 1,
            $validator->validated()['children'] ?? 0
        );

        if ($result['success']) {
            $this->dispatch('cart-updated', cart: $result['cart']);
        } else {
            $this->dispatch('cart-error', message: $result['message'] ?? 'Could not add room to cart.');
        }
    }

    public function remove(int $roomTypeId): void
    {
        $result = app(BookingCartService::class)->remove($roomTypeId);

        if ($result['success']) {
            $this->dispatch('cart-updated', cart: $result['cart']);
        }
    }

    public function clear(): void
    {
        $result = app(BookingCartService::class)->clear();

        if ($result['success']) {
            $this->dispatch('cart-updated', cart: $result['cart']);
        }
    }

    public function render(): View
    {
        $cart = app(BookingCartService::class)->getCartSummary();

        return view('website::livewire.cart-sidebar', compact('cart'));
    }
}
