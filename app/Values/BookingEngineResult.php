<?php

namespace App\Values;

use Illuminate\Support\Collection;

class BookingEngineResult
{
    public bool $success;
    public Collection $bookings;
    public ?string $bookingGroupId;
    public float $totalAmount;
    public ?string $error;

    public function __construct(
        bool $success,
        Collection $bookings,
        ?string $bookingGroupId = null,
        float $totalAmount = 0,
        ?string $error = null
    ) {
        $this->success = $success;
        $this->bookings = $bookings;
        $this->bookingGroupId = $bookingGroupId;
        $this->totalAmount = $totalAmount;
        $this->error = $error;
    }

    public static function ok(Collection $bookings, ?string $bookingGroupId = null, float $totalAmount = 0): self
    {
        return new self(true, $bookings, $bookingGroupId, $totalAmount);
    }

    public static function fail(string $error): self
    {
        return new self(false, collect(), null, 0, $error);
    }

    public function primaryBooking()
    {
        return $this->bookings->first();
    }
}
