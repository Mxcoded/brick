<?php

namespace Modules\Website\Services;

use Illuminate\Support\Collection;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomUnit;

/**
 * Automatic Room Assignment Service
 *
 * Selects the best available room unit for a booking using a priority-based
 * scoring algorithm. Factors considered (in order of priority):
 *
 * 1. Availability — unit must be free for the booking dates
 * 2. Housekeeping — clean > inspected > dirty (assignable, but lower priority)
 * 3. Floor — lower floors preferred (operational convenience)
 * 4. Room number — lower numbers preferred (tiebreaker)
 *
 * The service never assigns a unit in a hard-blocked status
 * (maintenance, blocked, out_of_order).
 */
class RoomAssignmentService
{
    public function __construct(
        protected RoomAvailabilityService $availabilityService,
    ) {}

    /**
     * Auto-assign the best available room unit to a booking.
     *
     * Returns the assigned RoomUnit model on success, or null if no
     * suitable unit is available.
     */
    public function autoAssign(Booking $booking): ?RoomUnit
    {
        $unit = $this->findBestUnit($booking);

        if (! $unit) {
            return null;
        }

        $booking->update(['room_unit_id' => $unit->id]);

        return $unit;
    }

    /**
     * Find the best available room unit for a booking without assigning it.
     *
     * Useful for previewing the recommendation before confirming.
     */
    public function findBestUnit(Booking $booking): ?RoomUnit
    {
        $availableUnits = $this->getAvailableUnits($booking);

        if ($availableUnits->isEmpty()) {
            return null;
        }

        return $this->rankUnits($availableUnits)->first();
    }

    /**
     * Get all available units for a booking, ranked by priority.
     */
    public function getRankedAvailableUnits(Booking $booking): Collection
    {
        $availableUnits = $this->getAvailableUnits($booking);

        if ($availableUnits->isEmpty()) {
            return collect();
        }

        return $this->rankUnits($availableUnits);
    }

    /**
     * Fetch available units from the availability service.
     */
    protected function getAvailableUnits(Booking $booking): Collection
    {
        return $this->availabilityService->getAvailableUnits(
            $booking->room_type_id,
            $booking->check_in_date,
            $booking->check_out_date,
            $booking->id,
        );
    }

    /**
     * Rank units by operational priority.
     *
     * Scoring (lower is better):
     * - housekeeping_score:  0 = clean, 1 = inspected, 2 = dirty
     * - floor_score:         actual floor number
     * - room_number_score:   numeric portion of room number
     */
    protected function rankUnits(Collection $units): Collection
    {
        return $units
            ->map(fn (RoomUnit $unit) => [
                'unit' => $unit,
                'score' => $this->calculateScore($unit),
            ])
            ->sortBy('score')
            ->pluck('unit')
            ->values();
    }

    /**
     * Calculate a composite priority score for a unit.
     */
    protected function calculateScore(RoomUnit $unit): array
    {
        return [
            $this->housekeepingScore($unit),
            $unit->floor ?? 0,
            $this->roomNumberScore($unit),
        ];
    }

    /**
     * Housekeeping priority: clean (0) > inspected (1) > dirty (2) > other (3).
     */
    protected function housekeepingScore(RoomUnit $unit): int
    {
        return match ($unit->housekeeping_status ?? 'clean') {
            'clean' => 0,
            'inspected' => 1,
            'dirty' => 2,
            default => 3,
        };
    }

    /**
     * Extract the leading numeric portion of a room number for sorting.
     * "101A" -> 101, "2B" -> 2, "VIP" -> 9999.
     */
    protected function roomNumberScore(RoomUnit $unit): int
    {
        if (preg_match('/^\d+/', $unit->room_number, $matches)) {
            return (int) $matches[0];
        }

        return 9999;
    }
}
