<?php

namespace Modules\Website\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomInventoryBlock;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;

/**
 * Unified Room Availability Service
 *
 * Provides comprehensive room availability checking across the entire ERP system.
 * Consolidates all sources of unavailability:
 * - Website Bookings
 * - Frontdesk Registrations
 * - Inventory Blocks (Stop Sell, Maintenance, Manual)
 * - Room Unit Status (maintenance, blocked)
 * - Stay Restrictions (min/max stay, CTA, CTD)
 */
class RoomAvailabilityService
{
    /**
     * Room unit statuses that prevent booking.
     */
    const UNAVAILABLE_UNIT_STATUSES = ['maintenance', 'blocked', 'out_of_order'];

    /**
     * Booking/registration statuses that count as "occupied".
     */
    const ACTIVE_BOOKING_STATUSES = ['pending', 'confirmed', 'checked_in'];

    const ACTIVE_REGISTRATION_STATUSES = ['checked_in', 'draft_by_guest', 'reserved'];

    /**
     * Check if a room type is available for booking on given dates.
     * Returns detailed availability info.
     */
    public function checkRoomTypeAvailability(
        int $roomTypeId,
        string|Carbon $checkIn,
        string|Carbon $checkOut,
        int $requiredRooms = 1
    ): array {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        $roomType = RoomType::with('units')->find($roomTypeId);
        if (! $roomType) {
            return $this->unavailableResponse('Room type not found.');
        }

        // 1. Check for Stop Sell on any date in range
        $stopSellBlock = $this->getStopSellBlock($roomTypeId, $checkIn, $checkOut);
        if ($stopSellBlock) {
            return $this->unavailableResponse(
                'This room type is not available for sale during your selected dates.',
                ['reason' => 'stop_sell', 'block' => $stopSellBlock]
            );
        }

        // 2. Check for Closed to Arrival on check-in date
        $ctaBlock = $this->getClosedToArrivalBlock($roomTypeId, $checkIn);
        if ($ctaBlock) {
            return $this->unavailableResponse(
                'Check-in is not available on '.$checkIn->format('M j, Y').'. Please try a different arrival date.',
                ['reason' => 'closed_to_arrival', 'block' => $ctaBlock]
            );
        }

        // 3. Check for Closed to Departure on check-out date
        $ctdBlock = $this->getClosedToDepartureBlock($roomTypeId, $checkOut);
        if ($ctdBlock) {
            return $this->unavailableResponse(
                'Check-out is not available on '.$checkOut->format('M j, Y').'. Please try a different departure date.',
                ['reason' => 'closed_to_departure', 'block' => $ctdBlock]
            );
        }

        // 4. Check minimum stay requirements
        $minStayViolation = $this->checkMinimumStay($roomTypeId, $checkIn, $checkOut);
        if ($minStayViolation) {
            return $this->unavailableResponse(
                "Minimum stay of {$minStayViolation['min_stay']} nights required. Your stay is {$minStayViolation['nights']} nights.",
                ['reason' => 'min_stay', 'required' => $minStayViolation['min_stay'], 'requested' => $minStayViolation['nights']]
            );
        }

        // 5. Check maximum stay requirements
        $maxStayViolation = $this->checkMaximumStay($roomTypeId, $checkIn, $checkOut);
        if ($maxStayViolation) {
            return $this->unavailableResponse(
                "Maximum stay of {$maxStayViolation['max_stay']} nights allowed. Your stay is {$maxStayViolation['nights']} nights.",
                ['reason' => 'max_stay', 'allowed' => $maxStayViolation['max_stay'], 'requested' => $maxStayViolation['nights']]
            );
        }

        // 6. Get available units (accounting for all blocks, bookings, registrations)
        $availableUnits = $this->getAvailableUnits($roomTypeId, $checkIn, $checkOut);
        $availableCount = $availableUnits->count();
        $totalUnits = $roomType->units->count();

        if ($availableCount < $requiredRooms) {
            $message = $availableCount === 0
                ? 'Sorry, no rooms are available for your selected dates.'
                : "Only {$availableCount} room(s) available, but you requested {$requiredRooms}.";

            return $this->unavailableResponse($message, [
                'reason' => 'insufficient_inventory',
                'available' => $availableCount,
                'requested' => $requiredRooms,
                'total' => $totalUnits,
            ]);
        }

        return [
            'available' => true,
            'message' => "{$availableCount} room(s) available",
            'available_count' => $availableCount,
            'total_units' => $totalUnits,
            'units' => $availableUnits,
        ];
    }

    /**
     * Get available room units for a room type on given dates.
     * This is the core availability calculation that accounts for ALL sources.
     */
    public function getAvailableUnits(
        int $roomTypeId,
        string|Carbon $checkIn,
        string|Carbon $checkOut,
        ?int $ignoreBookingId = null
    ): Collection {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        $roomType = RoomType::with('units')->find($roomTypeId);
        if (! $roomType) {
            return collect();
        }

        // Get the maximum blocked count for any date in the range
        $maxBlockedCount = $this->getMaxBlockedCount($roomTypeId, $checkIn, $checkOut);

        // Get units that pass all availability checks
        $availableUnits = $roomType->units()
            // 1. Exclude units in unavailable statuses
            ->whereNotIn('status', self::UNAVAILABLE_UNIT_STATUSES)
            // 2. Exclude units with conflicting website bookings
            ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut, $ignoreBookingId) {
                $q->whereNotIn('status', ['cancelled', 'no_show'])
                    ->where(function ($sub) use ($checkIn, $checkOut) {
                        $sub->where('check_in_date', '<', $checkOut)
                            ->where('check_out_date', '>', $checkIn);
                    })
                    ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId));
            })
            // 3. Exclude units with conflicting frontdesk registrations
            ->when(class_exists(Registration::class), function ($q) use ($checkIn, $checkOut) {
                $q->whereDoesntHave('registrations', function ($sub) use ($checkIn, $checkOut) {
                    $sub->whereIn('stay_status', self::ACTIVE_REGISTRATION_STATUSES)
                        ->where(function ($inner) use ($checkIn, $checkOut) {
                            $inner->where('check_in', '<', $checkOut)
                                ->where('check_out', '>', $checkIn);
                        });
                });
            })
            ->get();

        // 4. Subtract room-type level unassigned bookings
        $unassignedBookingsCount = Booking::where('room_type_id', $roomTypeId)
            ->whereNull('room_unit_id')
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->count();

        // 5. Apply inventory blocks (reduce available count)
        $effectiveAvailable = max(0, $availableUnits->count() - $unassignedBookingsCount - $maxBlockedCount);

        // Return the effective available units
        // Existing unassigned bookings get priority on the first units;
        // new bookings get whatever remains.
        return $availableUnits->values()->skip($unassignedBookingsCount + $maxBlockedCount)->values();
    }

    /**
     * Check availability for multiple room types at once.
     * Useful for booking pages that show all room types.
     */
    public function checkMultipleRoomTypes(
        string|Carbon $checkIn,
        string|Carbon $checkOut
    ): array {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        $roomTypes = RoomType::with('units')->active()->ordered()->get();
        $results = [];

        foreach ($roomTypes as $roomType) {
            $availability = $this->checkRoomTypeAvailability($roomType->id, $checkIn, $checkOut);
            $results[$roomType->id] = [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'price' => $roomType->price,
                'total_units' => $roomType->units->count(),
                'available' => $availability['available'],
                'available_count' => $availability['available_count'] ?? 0,
                'message' => $availability['message'] ?? null,
                'restrictions' => $this->getRestrictionsForDateRange($roomType->id, $checkIn, $checkOut),
            ];
        }

        return $results;
    }

    /**
     * Get detailed availability breakdown for a date range (for calendar views).
     */
    public function getAvailabilityBreakdown(
        int $roomTypeId,
        string|Carbon $start,
        string|Carbon $end
    ): array {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $roomType = RoomType::with('units')->find($roomTypeId);

        if (! $roomType) {
            return [];
        }

        $breakdown = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $nextDay = $current->copy()->addDay();

            // Get availability for this single day
            $availableUnits = $this->getAvailableUnits($roomTypeId, $current, $nextDay);
            $blockedCount = $this->getBlockedCountForDate($roomTypeId, $current);
            $restrictions = $this->getRestrictionsForDate($roomTypeId, $current);

            $totalUnits = $roomType->units->count();
            $availableCount = $availableUnits->count();
            $bookedCount = $totalUnits - $availableCount - $blockedCount;

            $breakdown[$dateStr] = [
                'date' => $dateStr,
                'total' => $totalUnits,
                'available' => $availableCount,
                'booked' => max(0, $bookedCount),
                'blocked' => $blockedCount,
                'percent' => $totalUnits > 0 ? round(($availableCount / $totalUnits) * 100) : 0,
                'status' => $this->getStatus($availableCount, $totalUnits, $restrictions),
                'stop_sell' => $restrictions['stop_sell'] ?? false,
                'closed_to_arrival' => $restrictions['closed_to_arrival'] ?? false,
                'closed_to_departure' => $restrictions['closed_to_departure'] ?? false,
                'min_stay' => $restrictions['min_stay'] ?? null,
                'max_stay' => $restrictions['max_stay'] ?? null,
            ];

            $current->addDay();
        }

        return $breakdown;
    }

    // ==========================================
    // INVENTORY BLOCK HELPERS
    // ==========================================

    /**
     * Check for stop sell blocks in date range.
     */
    protected function getStopSellBlock(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): ?RoomInventoryBlock
    {
        return RoomInventoryBlock::forRoomType($roomTypeId)
            ->overlapping($checkIn, $checkOut)
            ->stopSell()
            ->first();
    }

    /**
     * Check for closed to arrival on check-in date.
     */
    protected function getClosedToArrivalBlock(int $roomTypeId, Carbon $checkIn): ?RoomInventoryBlock
    {
        return RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($checkIn)
            ->where('closed_to_arrival', true)
            ->first();
    }

    /**
     * Check for closed to departure on check-out date.
     */
    protected function getClosedToDepartureBlock(int $roomTypeId, Carbon $checkOut): ?RoomInventoryBlock
    {
        return RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($checkOut)
            ->where('closed_to_departure', true)
            ->first();
    }

    /**
     * Check minimum stay requirements.
     */
    protected function checkMinimumStay(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): ?array
    {
        $nights = $checkIn->diffInDays($checkOut);

        $minStayBlock = RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($checkIn)
            ->whereNotNull('min_stay')
            ->where('min_stay', '>', $nights)
            ->orderBy('min_stay', 'desc')
            ->first();

        if ($minStayBlock) {
            return ['min_stay' => $minStayBlock->min_stay, 'nights' => $nights];
        }

        return null;
    }

    /**
     * Check maximum stay requirements.
     */
    protected function checkMaximumStay(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): ?array
    {
        $nights = $checkIn->diffInDays($checkOut);

        $maxStayBlock = RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($checkIn)
            ->whereNotNull('max_stay')
            ->where('max_stay', '<', $nights)
            ->orderBy('max_stay', 'asc')
            ->first();

        if ($maxStayBlock) {
            return ['max_stay' => $maxStayBlock->max_stay, 'nights' => $nights];
        }

        return null;
    }

    /**
     * Get blocked room count for a specific date.
     */
    protected function getBlockedCountForDate(int $roomTypeId, Carbon $date): int
    {
        return RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($date)
            ->sum('blocked_count');
    }

    /**
     * Get maximum blocked count for any date in range.
     */
    protected function getMaxBlockedCount(int $roomTypeId, Carbon $start, Carbon $end): int
    {
        $maxBlocked = 0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $blocked = $this->getBlockedCountForDate($roomTypeId, $current);
            $maxBlocked = max($maxBlocked, $blocked);
            $current->addDay();
        }

        return $maxBlocked;
    }

    /**
     * Get restrictions for a specific date.
     */
    protected function getRestrictionsForDate(int $roomTypeId, Carbon $date): array
    {
        $blocks = RoomInventoryBlock::forRoomType($roomTypeId)
            ->forDate($date)
            ->get();

        return [
            'stop_sell' => $blocks->where('stop_sell', true)->isNotEmpty(),
            'closed_to_arrival' => $blocks->where('closed_to_arrival', true)->isNotEmpty(),
            'closed_to_departure' => $blocks->where('closed_to_departure', true)->isNotEmpty(),
            'min_stay' => $blocks->whereNotNull('min_stay')->max('min_stay'),
            'max_stay' => $blocks->whereNotNull('max_stay')->min('max_stay'),
            'blocked_count' => $blocks->sum('blocked_count'),
        ];
    }

    /**
     * Get restrictions for a date range (check-in specific).
     */
    protected function getRestrictionsForDateRange(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): array
    {
        $restrictions = $this->getRestrictionsForDate($roomTypeId, $checkIn);

        // Check for stop sell on any date in range
        $hasStopSell = RoomInventoryBlock::forRoomType($roomTypeId)
            ->overlapping($checkIn, $checkOut)
            ->stopSell()
            ->exists();

        $restrictions['stop_sell'] = $hasStopSell;

        return $restrictions;
    }

    /**
     * Get availability status string.
     */
    protected function getStatus(int $available, int $total, array $restrictions): string
    {
        if ($restrictions['stop_sell'] ?? false) {
            return 'stop_sell';
        }
        if ($available === 0) {
            return 'full';
        }
        $percent = $total > 0 ? ($available / $total) * 100 : 0;
        if ($percent <= 30) {
            return 'limited';
        }

        return 'available';
    }

    /**
     * Build unavailable response array.
     */
    protected function unavailableResponse(string $message, array $extra = []): array
    {
        return array_merge([
            'available' => false,
            'message' => $message,
            'available_count' => 0,
        ], $extra);
    }

    // ==========================================
    // ROOM UNIT SPECIFIC CHECKS
    // ==========================================

    /**
     * Check if a specific room unit is available for dates.
     *
     * This is a per-unit occupancy check: it verifies the unit itself is not
     * in a hard-blocked status, is not stopped/closed at the room-type level,
     * and has no conflicting booking or active registration for the dates.
     *
     * It intentionally does NOT apply the aggregate "pool" logic used by
     * getAvailableUnits() (which reserves the first N units for unassigned
     * bookings / type-level blocks). That pool logic is only relevant when
     * deciding how many rooms of a type are still sellable — not when checking
     * whether one specific physical unit is free. Otherwise a perfectly free
     * unit could be wrongly reported unavailable simply because other
     * unassigned bookings consumed pool "slots" ahead of it.
     */
    public function isUnitAvailable(
        int $unitId,
        string|Carbon $checkIn,
        string|Carbon $checkOut,
        ?int $ignoreBookingId = null
    ): bool {
        $unit = RoomUnit::find($unitId);
        if (! $unit) {
            return false;
        }

        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        // A unit in a hard-blocked status is never assignable.
        if (in_array($unit->status, self::UNAVAILABLE_UNIT_STATUSES)) {
            return false;
        }

        // Respect room-type level hard restrictions (stop sell, closed to
        // arrival/departure, min/max stay). Capacity-only blocks (blocked_count)
        // reduce overall sellable inventory but do NOT make a specific physical
        // unit unavailable, so they are intentionally ignored here.
        $typeAvailability = $this->checkRoomTypeAvailability($unit->room_type_id, $checkIn, $checkOut);
        if (! $typeAvailability['available'] && ($typeAvailability['reason'] ?? null) !== 'insufficient_inventory') {
            return false;
        }

        // The unit must not have a conflicting booking for the dates
        // (ignoring the booking being (re)assigned).
        $hasConflictingBooking = Booking::where('room_unit_id', $unitId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->exists();

        if ($hasConflictingBooking) {
            return false;
        }

        // The unit must not have an active registration for the dates.
        if (class_exists(Registration::class)) {
            $hasConflictingRegistration = Registration::where('room_unit_id', $unitId)
                ->whereIn('stay_status', self::ACTIVE_REGISTRATION_STATUSES)
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->exists();

            if ($hasConflictingRegistration) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current status of a room unit.
     */
    public function getUnitCurrentStatus(int $unitId): array
    {
        $unit = RoomUnit::with(['roomType', 'currentOccupant'])->find($unitId);
        if (! $unit) {
            return ['status' => 'not_found', 'message' => 'Unit not found'];
        }

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Check unit base status
        if (in_array($unit->status, self::UNAVAILABLE_UNIT_STATUSES)) {
            return [
                'status' => $unit->status,
                'available' => false,
                'message' => ucfirst($unit->status),
            ];
        }

        // Check if currently occupied
        if ($unit->currentOccupant) {
            return [
                'status' => 'occupied',
                'available' => false,
                'message' => 'Occupied by '.$unit->currentOccupant->full_name,
                'guest' => $unit->currentOccupant->full_name,
                'check_out' => $unit->currentOccupant->check_out,
            ];
        }

        // Check for upcoming reservations today
        $todayBooking = Booking::where('room_unit_id', $unitId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereDate('check_in_date', $today)
            ->first();

        if ($todayBooking) {
            return [
                'status' => 'arriving',
                'available' => false,
                'message' => 'Guest arriving today',
                'guest' => $todayBooking->guest_name,
                'check_in' => $todayBooking->check_in_date,
            ];
        }

        return [
            'status' => 'available',
            'available' => true,
            'message' => 'Available',
        ];
    }
}
