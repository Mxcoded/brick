<?php

namespace Modules\Website\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Modules\Website\Models\RoomType;

class BookingCartService
{
    const SESSION_KEY = 'booking_cart';

    /**
     * Add a room type to the cart.
     */
    public function add(int $roomTypeId, int $quantity, string $checkIn, string $checkOut, int $adults = 1, int $children = 0): array
    {
        $roomType = RoomType::findOrFail($roomTypeId);
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        $capacityError = $this->validateCapacity($roomType, $adults, $children);
        if ($capacityError) {
            return [
                'success' => false,
                'message' => $capacityError,
            ];
        }

        $cart = $this->getCart();

        // Check if cart has different dates - if so, clear it first
        if (! empty($cart['items']) && ($cart['check_in'] !== $checkIn || $cart['check_out'] !== $checkOut)) {
            $this->clear();
            $cart = $this->getCart();
        }

        // Set dates if not set
        if (empty($cart['check_in'])) {
            $cart['check_in'] = $checkIn;
            $cart['check_out'] = $checkOut;
            $cart['nights'] = $nights;
        }

        // Check availability using unified service (includes stop sell, CTA, CTD, min/max stay)
        $availabilityService = app(RoomAvailabilityService::class);
        $result = $availabilityService->checkRoomTypeAvailability($roomTypeId, $checkIn, $checkOut, $quantity);

        if (! $result['available']) {
            return [
                'success' => false,
                'message' => $result['message'],
                'available' => 0,
            ];
        }

        $rateService = app(WebsiteRateService::class);
        $rate = $rateService->calculateWithGuests($roomType, $checkIn, $checkOut, $adults, $children);

        $cart['items'][$roomTypeId] = [
            'room_type_id' => $roomTypeId,
            'room_type_name' => $roomType->name,
            'quantity' => $quantity,
            'price_per_night' => $rate['price_per_night'],
            'base_total' => $rate['base_total'],
            'guest_fee_per_night' => $rate['guest_fee_per_night'],
            'guest_fee_total' => $rate['guest_fee_total'],
            'total_rate' => $rate['total'],
            'rate_code_id' => $rate['rate_code_id'],
            'capacity' => $roomType->capacity,
            'adults' => $adults,
            'children' => $children,
            'image_url' => $roomType->image_url,
            'nights' => $nights,
            'subtotal' => $rate['total'] * $quantity,
        ];

        $this->saveCart($cart);

        return [
            'success' => true,
            'message' => "{$quantity} x {$roomType->name} added to cart.",
            'cart' => $this->getCartSummary(),
        ];
    }

    /**
     * Update quantity for a room type in cart.
     */
    public function update(int $roomTypeId, int $quantity): array
    {
        $cart = $this->getCart();

        if (! isset($cart['items'][$roomTypeId])) {
            return [
                'success' => false,
                'message' => 'Room not found in cart.',
            ];
        }

        if ($quantity <= 0) {
            return $this->remove($roomTypeId);
        }

        // Check availability using unified service
        $roomType = RoomType::findOrFail($roomTypeId);
        $availabilityService = app(RoomAvailabilityService::class);
        $result = $availabilityService->checkRoomTypeAvailability($roomTypeId, $cart['check_in'], $cart['check_out'], $quantity);

        if (! $result['available']) {
            return [
                'success' => false,
                'message' => $result['message'],
                'available' => 0,
            ];
        }

        $nights = $cart['nights'];
        $cart['items'][$roomTypeId]['quantity'] = $quantity;
        $this->recalculateItem($cart, $roomTypeId);

        $this->saveCart($cart);

        return [
            'success' => true,
            'message' => 'Cart updated.',
            'cart' => $this->getCartSummary(),
        ];
    }

    /**
     * Update guest count for a room type in cart.
     */
    public function updateGuests(int $roomTypeId, int $adults, int $children): array
    {
        $cart = $this->getCart();

        if (! isset($cart['items'][$roomTypeId])) {
            return [
                'success' => false,
                'message' => 'Room not found in cart.',
            ];
        }

        $roomType = RoomType::findOrFail($roomTypeId);
        $capacityError = $this->validateCapacity($roomType, $adults, $children);
        if ($capacityError) {
            return [
                'success' => false,
                'message' => $capacityError,
            ];
        }

        $cart['items'][$roomTypeId]['adults'] = $adults;
        $cart['items'][$roomTypeId]['children'] = $children;
        $this->recalculateItem($cart, $roomTypeId);

        $this->saveCart($cart);

        return [
            'success' => true,
            'message' => 'Guests updated.',
            'cart' => $this->getCartSummary(),
            'item' => $cart['items'][$roomTypeId],
        ];
    }

    /**
     * Validate guest count against room type capacity.
     */
    protected function validateCapacity(RoomType $roomType, int $adults, int $children): ?string
    {
        $totalGuests = $adults + $children;
        if ($totalGuests > $roomType->capacity) {
            return "Total guests ({$totalGuests}) exceed room capacity ({$roomType->capacity}).";
        }

        if ($adults < 1) {
            return 'At least 1 adult is required.';
        }

        return null;
    }

    /**
     * Recalculate an item's pricing with current guest counts.
     */
    protected function recalculateItem(array &$cart, int $roomTypeId): void
    {
        $item = &$cart['items'][$roomTypeId];
        $roomType = RoomType::findOrFail($roomTypeId);
        $nights = $cart['nights'];
        $adults = $item['adults'] ?? 1;
        $children = $item['children'] ?? 0;

        $rateService = app(WebsiteRateService::class);
        $rate = $rateService->calculateWithGuests($roomType, $cart['check_in'], $cart['check_out'], $adults, $children);

        $item['price_per_night'] = $rate['price_per_night'];
        $item['base_total'] = $rate['base_total'];
        $item['guest_fee_per_night'] = $rate['guest_fee_per_night'];
        $item['guest_fee_total'] = $rate['guest_fee_total'];
        $item['total_rate'] = $rate['total'];
        $item['subtotal'] = $rate['total'] * $item['quantity'];
    }

    /**
     * Remove a room type from cart.
     */
    public function remove(int $roomTypeId): array
    {
        $cart = $this->getCart();

        if (isset($cart['items'][$roomTypeId])) {
            unset($cart['items'][$roomTypeId]);
        }

        // If cart is empty, clear dates too
        if (empty($cart['items'])) {
            $this->clear();

            return [
                'success' => true,
                'message' => 'Item removed. Cart is now empty.',
                'cart' => $this->getCartSummary(),
            ];
        }

        $this->saveCart($cart);

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart' => $this->getCartSummary(),
        ];
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): array
    {
        Session::forget(self::SESSION_KEY);

        return [
            'success' => true,
            'message' => 'Cart cleared.',
            'cart' => $this->getCartSummary(),
        ];
    }

    /**
     * Get all cart items.
     */
    public function getItems(): array
    {
        $cart = $this->getCart();

        return array_values($cart['items'] ?? []);
    }

    /**
     * Get cart dates.
     */
    public function getDates(): array
    {
        $cart = $this->getCart();

        return [
            'check_in' => $cart['check_in'] ?? null,
            'check_out' => $cart['check_out'] ?? null,
            'nights' => $cart['nights'] ?? 0,
        ];
    }

    /**
     * Get total base room rate of cart (before guest fees).
     */
    public function getBaseTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart['items'] ?? [] as $item) {
            $total += ($item['base_total'] ?? $item['subtotal'] ?? 0);
        }

        return $total;
    }

    /**
     * Get total guest fees in cart.
     */
    public function getGuestFeeTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart['items'] ?? [] as $item) {
            $total += ($item['guest_fee_total'] ?? 0) * ($item['quantity'] ?? 1);
        }

        return $total;
    }

    /**
     * Get total price of cart.
     */
    public function getTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart['items'] ?? [] as $item) {
            $total += $item['subtotal'];
        }

        return $total;
    }

    /**
     * Get total room count in cart.
     */
    public function getTotalRooms(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart['items'] ?? [] as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Get total guest capacity.
     */
    public function getTotalGuests(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart['items'] ?? [] as $item) {
            $count += ($item['capacity'] ?? 2) * $item['quantity'];
        }

        return $count;
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        $cart = $this->getCart();

        return empty($cart['items']);
    }

    /**
     * Get cart summary for API/view.
     */
    public function getCartSummary(): array
    {
        $cart = $this->getCart();
        $total = $this->getTotal();
        $baseTotal = $this->getBaseTotal();
        $guestFeeTotal = $this->getGuestFeeTotal();

        return [
            'items' => array_values($cart['items'] ?? []),
            'check_in' => $cart['check_in'] ?? null,
            'check_out' => $cart['check_out'] ?? null,
            'nights' => $cart['nights'] ?? 0,
            'total_rooms' => $this->getTotalRooms(),
            'total_guests' => $this->getTotalGuests(),
            'base_total' => $baseTotal,
            'guest_fee_total' => $guestFeeTotal,
            'total' => $total,
            'formatted_total' => '₦'.number_format($total, 2),
        ];
    }

    /**
     * Validate all cart items are still available.
     * Uses unified RoomAvailabilityService for comprehensive checking.
     * Returns array of unavailable items or empty if all OK.
     */
    public function validateAvailability(): array
    {
        $cart = $this->getCart();
        $unavailable = [];

        if (empty($cart['items']) || empty($cart['check_in'])) {
            return $unavailable;
        }

        $availabilityService = app(RoomAvailabilityService::class);

        foreach ($cart['items'] as $roomTypeId => $item) {
            $roomType = RoomType::find($roomTypeId);

            if (! $roomType) {
                $unavailable[] = [
                    'room_type_id' => $roomTypeId,
                    'name' => $item['room_type_name'],
                    'requested' => $item['quantity'],
                    'available' => 0,
                    'message' => 'Room type no longer exists.',
                ];

                continue;
            }

            // Use unified service for comprehensive availability check
            $result = $availabilityService->checkRoomTypeAvailability(
                $roomTypeId,
                $cart['check_in'],
                $cart['check_out'],
                $item['quantity']
            );

            if (! $result['available']) {
                $unavailable[] = [
                    'room_type_id' => $roomTypeId,
                    'name' => $item['room_type_name'],
                    'requested' => $item['quantity'],
                    'available' => $result['available_count'] ?? 0,
                    'message' => $result['message'],
                    'reason' => $result['reason'] ?? 'unavailable',
                ];
            }
        }

        return $unavailable;
    }

    /**
     * Get raw cart data from session.
     */
    protected function getCart(): array
    {
        return Session::get(self::SESSION_KEY, [
            'items' => [],
            'check_in' => null,
            'check_out' => null,
            'nights' => 0,
        ]);
    }

    /**
     * Save cart to session.
     */
    protected function saveCart(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
