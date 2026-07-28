<?php

namespace Modules\Website\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Services\RateCalculator;
use Modules\Website\Models\RoomType;

class WebsiteRateService
{
    protected RateCalculator $calculator;

    public function __construct(RateCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Calculate the total rate for a stay at a given room type.
     *
     * Falls back to RoomType->price * nights when no rate code is assigned.
     *
     * @return array{price_per_night: float, total: float, average_rate: float, rate_code_id: int|null, nights: array}
     */
    public function calculate(RoomType $roomType, string $checkIn, string $checkOut, ?GuestType $guestType = null): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = (int) $checkInDate->diffInDays($checkOutDate);

        if ($nights < 1) {
            $nights = 1;
        }

        if ($roomType->rate_code_id) {
            return $this->calculateWithRateCode($roomType, $checkInDate, $checkOutDate, $nights, $guestType);
        }

        if ($guestType) {
            $negotiated = $guestType->getNegotiatedRate($roomType->id);
            if ($negotiated['has_negotiated_rate']) {
                $nightly = $negotiated['rate'];

                return [
                    'price_per_night' => $nightly,
                    'total' => $nightly * $nights,
                    'average_rate' => $nightly,
                    'rate_code_id' => null,
                    'nights' => [],
                ];
            }
        }

        return $this->calculateFlatRate($roomType, $nights);
    }

    /**
     * Calculate rate with extra guest fees included.
     *
     * @return array{price_per_night: float, total: float, average_rate: float, rate_code_id: int|null, nights: array, guest_fee_per_night: float, guest_fee_total: float, base_total: float, breakdown: string}
     */
    public function calculateWithGuests(RoomType $roomType, string $checkIn, string $checkOut, int $adults, int $children, ?GuestType $guestType = null): array
    {
        $baseRate = $this->calculate($roomType, $checkIn, $checkOut, $guestType);
        $guestFee = $roomType->calculateGuestFee($adults, $children);

        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = max(1, (int) $checkInDate->diffInDays($checkOutDate));

        $guestFeeTotal = $guestFee['extra_fee_per_night'] * $nights;

        return array_merge($baseRate, [
            'base_total' => $baseRate['total'],
            'guest_fee_per_night' => $guestFee['extra_fee_per_night'],
            'guest_fee_total' => $guestFeeTotal,
            'total' => $baseRate['total'] + $guestFeeTotal,
            'guest_fee_breakdown' => $guestFee['breakdown'],
        ]);
    }

    /**
     * Calculate using the RateCalendar + Season + GuestType engine.
     */
    protected function calculateWithRateCode(
        RoomType $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        int $nights,
        ?GuestType $guestType = null
    ): array {
        $rateCode = RateCode::find($roomType->rate_code_id);

        if (! $rateCode || ! $rateCode->is_active) {
            Log::warning("RoomType [{$roomType->id}] has inactive/missing rate code, falling back to flat rate.");

            return $this->calculateFlatRate($roomType, $nights);
        }

        $result = $this->calculator->calculateForStay($rateCode, $checkIn, $checkOut, $guestType, $roomType->id);

        return [
            'price_per_night' => $result['average_rate'],
            'total' => $result['total'],
            'average_rate' => $result['average_rate'],
            'rate_code_id' => $rateCode->id,
            'nights' => $result['nights'],
        ];
    }

    /**
     * Fallback: flat RoomType->price multiplied by nights.
     */
    protected function calculateFlatRate(RoomType $roomType, int $nights): array
    {
        $nightlyRate = (float) $roomType->price;
        $total = $nightlyRate * $nights;

        return [
            'price_per_night' => $nightlyRate,
            'total' => $total,
            'average_rate' => $nightlyRate,
            'rate_code_id' => null,
            'nights' => [],
        ];
    }
}
