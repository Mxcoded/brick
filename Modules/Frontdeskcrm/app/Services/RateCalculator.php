<?php

namespace Modules\Frontdeskcrm\Services;

use Carbon\Carbon;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Season;

class RateCalculator
{
    public function calculate(
        RateCode $rateCode,
        Carbon $date,
        ?GuestType $guestType = null,
        ?int $roomTypeId = null,
    ): float {
        if ($guestType && $roomTypeId) {
            $negotiated = $guestType->getNegotiatedRate($roomTypeId, $date);
            if ($negotiated['has_negotiated_rate']) {
                return round($negotiated['rate'], 2);
            }
        }

        $rate = $this->getBaseRate($rateCode, $date);

        $rate = $this->applySeasonMultiplier($rate, $date);

        $rate = $this->applyGuestTypeDiscount($rate, $guestType);

        return round($rate, 2);
    }

    public function calculateForStay(
        RateCode $rateCode,
        Carbon $checkIn,
        Carbon $checkOut,
        ?GuestType $guestType = null,
        ?int $roomTypeId = null,
    ): array {
        $nights = [];
        $total = 0;
        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $nightly = $this->calculate($rateCode, $current, $guestType, $roomTypeId);
            $nights[$current->format('Y-m-d')] = $nightly;
            $total += $nightly;
            $current->addDay();
        }

        return [
            'nights' => $nights,
            'total' => round($total, 2),
            'average_rate' => count($nights) > 0 ? round($total / count($nights), 2) : 0,
        ];
    }

    public function getBaseRate(RateCode $rateCode, Carbon $date): float
    {
        $calendar = $rateCode->calendar()
            ->whereDate('date', $date)
            ->first();

        if ($calendar && $calendar->rate !== null) {
            return (float) $calendar->rate;
        }

        return (float) $rateCode->default_rate;
    }

    public function applySeasonMultiplier(float $rate, Carbon $date): float
    {
        $season = Season::where('is_active', true)
            ->whereDate('valid_from', '<=', $date)
            ->whereDate('valid_to', '>=', $date)
            ->orderBy('rate_multiplier', 'desc')
            ->first();

        if ($season) {
            $rate *= (float) $season->rate_multiplier;
        }

        return $rate;
    }

    public function applyGuestTypeDiscount(float $rate, ?GuestType $guestType): float
    {
        if ($guestType && $guestType->discount_rate > 0) {
            $rate *= (1 - ((float) $guestType->discount_rate / 100));
        }

        return $rate;
    }

    public function validateRestrictions(RateCode $rateCode, Carbon $checkIn, Carbon $checkOut, int $los): array
    {
        $errors = [];

        if ($rateCode->min_los && $los < $rateCode->min_los) {
            $errors[] = "Minimum length of stay is {$rateCode->min_los} nights.";
        }

        if ($rateCode->max_los && $los > $rateCode->max_los) {
            $errors[] = "Maximum length of stay is {$rateCode->max_los} nights.";
        }

        if ($rateCode->closed_to_arrival) {
            $errors[] = 'This rate is closed to arrival.';
        }

        if ($rateCode->closed_to_departure) {
            $errors[] = 'This rate is closed to departure.';
        }

        $dow = $checkIn->dayOfWeek;
        $isWeekend = in_array($dow, [Carbon::FRIDAY, Carbon::SATURDAY], true);
        $isWeekday = ! $isWeekend;

        if ($isWeekend && ! $rateCode->apply_weekends) {
            $errors[] = 'This rate does not apply on weekends.';
        }

        if ($isWeekday && ! $rateCode->apply_weekdays) {
            $errors[] = 'This rate does not apply on weekdays.';
        }

        if ($rateCode->valid_from && $checkIn->lt($rateCode->valid_from)) {
            $errors[] = "This rate is not valid before {$rateCode->valid_from->format('M d, Y')}.";
        }

        if ($rateCode->valid_to && $checkOut->gt($rateCode->valid_to)) {
            $errors[] = "This rate is not valid after {$rateCode->valid_to->format('M d, Y')}.";
        }

        return $errors;
    }
}
