<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomInventoryBlock;
use App\Models\RoomType;
use App\Models\RoomUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;

class RoomCalendarService
{
    const STATUS_COLORS = [
        'checked_in' => '#32CD32',
        'checked_out' => '#006400',
        'reserved' => '#00CED1',
        'draft_by_guest' => '#FFC107',
        'online_booking' => '#0d6efd',
        'maintenance' => '#FF00FF',
        'available' => null,
    ];

    const STATUS_LABELS = [
        'checked_in' => 'In-House',
        'checked_out' => 'Checked Out',
        'reserved' => 'Reserved',
        'draft_by_guest' => 'Pending',
        'online_booking' => 'Online',
        'maintenance' => 'Maintenance',
        'available' => 'Available',
    ];

    public function getRoomUnits(): Collection
    {
        $roomUnits = RoomUnit::with('roomType')
            ->orderBy('room_number')
            ->get();

        if ($roomUnits->isEmpty()) {
            return Room::orderBy('name')->get()->map(function ($room) {
                return (object) [
                    'id' => $room->id,
                    'room_number' => $room->name,
                    'status' => $room->status,
                    'roomType' => (object) [
                        'name' => $room->name,
                        'capacity' => $room->capacity,
                    ],
                    'is_legacy' => true,
                ];
            });
        }

        return $roomUnits;
    }

    public function getBookings(Carbon $start, Carbon $end): Collection
    {
        return Booking::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in_date', [$start, $end])
                    ->orWhereBetween('check_out_date', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('check_in_date', '<=', $start)
                            ->where('check_out_date', '>=', $end);
                    });
            })->get();
    }

    public function getRegistrations(Carbon $start, Carbon $end): Collection
    {
        if (! class_exists(Registration::class)) {
            return collect();
        }

        return Registration::whereNotIn('stay_status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in', [$start, $end])
                    ->orWhereBetween('check_out', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('check_in', '<=', $start)
                            ->where('check_out', '>=', $end);
                    });
            })->get();
    }

    public function getActiveRegistrations(): Collection
    {
        if (! class_exists(Registration::class)) {
            return collect();
        }

        $today = now()->format('Y-m-d');

        return Registration::whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->get();
    }

    public function getActiveBookings(): Collection
    {
        $today = now()->format('Y-m-d');

        return Booking::where('status', '!=', 'cancelled')
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->get();
    }

    public function generateDateHeaders(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dates[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'weekday' => $current->format('D'),
                'is_weekend' => $current->isWeekend(),
                'is_today' => $current->isToday(),
            ];
            $current->addDay();
        }

        return $dates;
    }

    public function registrationMatchesRoom($registration, $unitId, string $roomNumber, bool $isLegacy): bool
    {
        if (! $isLegacy && $registration->room_unit_id == $unitId) {
            return true;
        }
        if ($isLegacy && $registration->room_id == $unitId) {
            return true;
        }
        if ($registration->room_allocation && str_contains($registration->room_allocation, $roomNumber)) {
            return true;
        }

        return false;
    }

    public function bookingMatchesRoom($booking, $unitId, bool $isLegacy): bool
    {
        if (! $isLegacy && $booking->room_unit_id == $unitId) {
            return true;
        }
        if ($booking->room_id == $unitId) {
            return true;
        }

        return false;
    }

    public function buildRoomDisplayName($unit): string
    {
        $isLegacy = $unit->is_legacy ?? false;

        if ($isLegacy) {
            return $unit->room_number;
        }

        $name = "Room {$unit->room_number}";
        if ($unit->roomType) {
            $name .= " ({$unit->roomType->name})";
        }

        return $name;
    }

    public function parseRoomName(string $name): array
    {
        if (preg_match('/Room\s+(\w+)\s*\(([^)]+)\)/i', $name, $match)) {
            return ['number' => $match[1], 'type' => $match[2]];
        }

        return ['type' => $name, 'number' => ''];
    }

    public function formatDate($date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }

        return substr($date, 0, 10);
    }

    public function getStatusColor(string $status): ?string
    {
        return self::STATUS_COLORS[$status] ?? '#6c757d';
    }

    public function getStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? 'Unknown';
    }

    public function getCalendarData(Carbon $start, Carbon $end): array
    {
        $roomUnits = $this->getRoomUnits();
        $bookings = $this->getBookings($start, $end);
        $registrations = $this->getRegistrations($start, $end);

        $roomData = $roomUnits->map(function ($unit) use ($bookings, $registrations, $start, $end) {
            return $this->buildRoomCalendarData($unit, $bookings, $registrations, $start, $end);
        });

        return [
            'rooms' => $roomData,
            'days' => $this->generateDateHeaders($start, $end),
        ];
    }

    protected function buildRoomCalendarData($unit, Collection $bookings, Collection $registrations, Carbon $start, Carbon $end): array
    {
        $events = [];
        $isLegacy = $unit->is_legacy ?? false;
        $unitId = $unit->id;
        $roomNumber = $unit->room_number;

        if ($unit->status === 'maintenance') {
            $events[] = [
                'id' => 'maint-'.$unitId,
                'title' => 'Maintenance',
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'color' => self::STATUS_COLORS['maintenance'],
                'status' => 'maintenance',
            ];
        }

        foreach ($registrations as $reg) {
            if ($this->registrationMatchesRoom($reg, $unitId, $roomNumber, $isLegacy)) {
                $events[] = [
                    'id' => 'reg-'.$reg->id,
                    'title' => "{$reg->full_name} ({$this->getStatusLabel($reg->stay_status)})",
                    'start' => $this->formatDate($reg->check_in),
                    'end' => $this->formatDate($reg->check_out),
                    'color' => $this->getStatusColor($reg->stay_status),
                    'status' => $reg->stay_status,
                ];
            }
        }

        foreach ($bookings as $booking) {
            if ($this->bookingMatchesRoom($booking, $unitId, $isLegacy)) {
                $hasRegistration = $registrations->where('booking_id', $booking->id)->isNotEmpty();

                if (! $hasRegistration) {
                    $events[] = [
                        'id' => 'bk-'.$booking->id,
                        'title' => "{$booking->guest_name} (Online)",
                        'start' => $this->formatDate($booking->check_in_date),
                        'end' => $this->formatDate($booking->check_out_date),
                        'color' => self::STATUS_COLORS['online_booking'],
                        'status' => 'online_booking',
                    ];
                }
            }
        }

        $displayName = $this->buildRoomDisplayName($unit);
        $parsed = $this->parseRoomName($displayName);

        return [
            'id' => $unitId,
            'name' => $displayName,
            'room_type' => $parsed['type'],
            'room_number' => $parsed['number'] ?: $roomNumber,
            'capacity' => $unit->roomType->capacity ?? 2,
            'events' => $events,
        ];
    }

    public function getRoomStatusData(): Collection
    {
        $roomUnits = $this->getRoomUnits();
        $activeRegistrations = $this->getActiveRegistrations();
        $activeBookings = $this->getActiveBookings();

        return $roomUnits->map(function ($unit) use ($activeRegistrations, $activeBookings) {
            return $this->buildRoomStatusData($unit, $activeRegistrations, $activeBookings);
        });
    }

    protected function buildRoomStatusData($unit, Collection $activeRegistrations, Collection $activeBookings): array
    {
        $unitId = $unit->id;
        $roomNumber = $unit->room_number;
        $isLegacy = $unit->is_legacy ?? false;
        $displayName = $this->buildRoomDisplayName($unit);

        $cleaningStatus = $unit->cleaning_status ?? 'clean';

        if (in_array($unit->status, ['maintenance', 'blocked'])) {
            return $this->formatRoomStatus($unitId, $displayName, $unit->status, $unit->status_color, ucfirst($unit->status), null, $cleaningStatus);
        }

        $registration = $activeRegistrations->first(function ($reg) use ($unitId, $roomNumber, $isLegacy) {
            return $this->registrationMatchesRoom($reg, $unitId, $roomNumber, $isLegacy);
        });

        if ($registration) {
            $statusColor = $registration->stay_status === 'checked_in' ? 'danger' : 'warning';

            return $this->formatRoomStatus(
                $unitId,
                $displayName,
                'occupied',
                $statusColor,
                $registration->full_name,
                $registration->check_out,
                $cleaningStatus
            );
        }

        $booking = $activeBookings->first(function ($booking) use ($unitId, $isLegacy) {
            return $this->bookingMatchesRoom($booking, $unitId, $isLegacy);
        });

        if ($booking) {
            $checkoutDate = $this->formatDate($booking->check_out_date);
            $isCheckingOut = $checkoutDate === now()->format('Y-m-d');

            return $this->formatRoomStatus(
                $unitId,
                $displayName,
                'occupied',
                $isCheckingOut ? 'warning' : 'primary',
                $booking->guest_name,
                $booking->check_out_date,
                $cleaningStatus
            );
        }

        return $this->formatRoomStatus($unitId, $displayName, 'available', 'success', 'Vacant', null, $cleaningStatus);
    }

    protected function formatRoomStatus($id, string $name, string $status, string $color, string $guest, $checkout = null, $cleaningStatus = null): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'status' => $status,
            'color' => $color,
            'guest' => $guest,
            'checkout' => $checkout ? Carbon::parse($checkout)->format('M d') : null,
            'cleaning_status' => $cleaningStatus ?? 'clean',
        ];
    }

    public function getOccupancyStats(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $dateStr = $date->format('Y-m-d');

        $roomUnits = $this->getRoomUnits();
        $totalRooms = $roomUnits->count();

        $activeRegistrations = $this->getActiveRegistrations();
        $activeBookings = $this->getActiveBookings();

        $occupied = 0;
        $reserved = 0;

        foreach ($roomUnits as $unit) {
            $isLegacy = $unit->is_legacy ?? false;
            $unitId = $unit->id;
            $roomNumber = $unit->room_number;

            $hasRegistration = $activeRegistrations->first(function ($reg) use ($unitId, $roomNumber, $isLegacy) {
                return $this->registrationMatchesRoom($reg, $unitId, $roomNumber, $isLegacy);
            });

            if ($hasRegistration) {
                if ($hasRegistration->stay_status === 'checked_in') {
                    $occupied++;
                } else {
                    $reserved++;
                }

                continue;
            }

            $hasBooking = $activeBookings->first(function ($booking) use ($unitId, $isLegacy) {
                return $this->bookingMatchesRoom($booking, $unitId, $isLegacy);
            });

            if ($hasBooking) {
                $reserved++;
            }
        }

        $available = $totalRooms - $occupied - $reserved;
        $occupancyRate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

        return [
            'total_rooms' => $totalRooms,
            'occupied' => $occupied,
            'reserved' => $reserved,
            'available' => $available,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    // ==========================================
    // INVENTORY CALENDAR METHODS
    // ==========================================

    const AVAILABILITY_THRESHOLDS = [
        'full' => 0,
        'limited' => 30,
        'available' => 100,
    ];

    public function getInventoryByRoomType(Carbon $start, Carbon $end): array
    {
        $roomTypes = RoomType::with('units')->active()->ordered()->get();
        $bookings = $this->getBookings($start, $end);
        $registrations = $this->getRegistrations($start, $end);
        $inventoryBlocks = RoomInventoryBlock::overlapping($start, $end)->get();

        $inventory = [];

        foreach ($roomTypes as $roomType) {
            $totalUnits = $roomType->units->count();
            $inventory[$roomType->id] = [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'total_units' => $totalUnits,
                'price' => $roomType->price,
                'dates' => [],
            ];

            $current = $start->copy();
            while ($current->lte($end)) {
                $dateStr = $current->format('Y-m-d');
                $dateData = $this->calculateDateInventory(
                    $roomType,
                    $current,
                    $bookings,
                    $registrations,
                    $inventoryBlocks
                );
                $inventory[$roomType->id]['dates'][$dateStr] = $dateData;
                $current->addDay();
            }
        }

        return $inventory;
    }

    protected function calculateDateInventory(
        RoomType $roomType,
        Carbon $date,
        Collection $bookings,
        Collection $registrations,
        Collection $inventoryBlocks
    ): array {
        $totalUnits = $roomType->units->count();
        $dateStr = $date->format('Y-m-d');

        $bookedFromRegistrations = $registrations->filter(function ($reg) use ($roomType, $date) {
            if ($reg->room_type_id != $roomType->id) {
                return false;
            }
            $checkIn = Carbon::parse($reg->check_in);
            $checkOut = Carbon::parse($reg->check_out);

            return $date->gte($checkIn) && $date->lt($checkOut);
        })->count();

        $bookedFromBookings = $bookings->filter(function ($booking) use ($roomType, $date, $registrations) {
            if ($booking->room_type_id != $roomType->id) {
                return false;
            }
            if ($registrations->where('booking_id', $booking->id)->isNotEmpty()) {
                return false;
            }
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);

            return $date->gte($checkIn) && $date->lt($checkOut);
        })->count();

        $blocks = $inventoryBlocks->filter(function ($block) use ($roomType, $date) {
            return $block->room_type_id == $roomType->id && $block->coversDate($date);
        });

        $blockedCount = $blocks->sum('blocked_count');
        $stopSell = $blocks->where('stop_sell', true)->isNotEmpty();
        $closedToArrival = $blocks->where('closed_to_arrival', true)->isNotEmpty();
        $closedToDeparture = $blocks->where('closed_to_departure', true)->isNotEmpty();
        $minStay = $blocks->whereNotNull('min_stay')->max('min_stay');
        $maxStay = $blocks->whereNotNull('max_stay')->min('max_stay');

        $totalBooked = $bookedFromRegistrations + $bookedFromBookings;
        $totalBlocked = min($blockedCount, $totalUnits - $totalBooked);
        $available = max(0, $totalUnits - $totalBooked - $totalBlocked);

        $availabilityPercent = $totalUnits > 0 ? round(($available / $totalUnits) * 100) : 0;
        $status = $this->getAvailabilityStatus($availabilityPercent, $stopSell);

        return [
            'date' => $dateStr,
            'total' => $totalUnits,
            'booked' => $totalBooked,
            'blocked' => $totalBlocked,
            'available' => $available,
            'percent' => $availabilityPercent,
            'status' => $status,
            'color' => $this->getAvailabilityColor($status),
            'stop_sell' => $stopSell,
            'closed_to_arrival' => $closedToArrival,
            'closed_to_departure' => $closedToDeparture,
            'min_stay' => $minStay,
            'max_stay' => $maxStay,
            'restrictions' => $this->formatRestrictions($minStay, $maxStay, $closedToArrival, $closedToDeparture),
        ];
    }

    protected function getAvailabilityStatus(int $percent, bool $stopSell): string
    {
        if ($stopSell) {
            return 'stop_sell';
        }
        if ($percent <= self::AVAILABILITY_THRESHOLDS['full']) {
            return 'full';
        }
        if ($percent <= self::AVAILABILITY_THRESHOLDS['limited']) {
            return 'limited';
        }

        return 'available';
    }

    protected function getAvailabilityColor(string $status): string
    {
        return match ($status) {
            'available' => '#28a745',
            'limited' => '#ffc107',
            'full' => '#dc3545',
            'stop_sell' => '#6c757d',
            default => '#6c757d',
        };
    }

    protected function formatRestrictions($minStay, $maxStay, $closedToArrival, $closedToDeparture): array
    {
        $restrictions = [];
        if ($minStay) {
            $restrictions[] = "Min {$minStay} nights";
        }
        if ($maxStay) {
            $restrictions[] = "Max {$maxStay} nights";
        }
        if ($closedToArrival) {
            $restrictions[] = 'CTA';
        }
        if ($closedToDeparture) {
            $restrictions[] = 'CTD';
        }

        return $restrictions;
    }

    public function getInventoryMatrix(Carbon $start, Carbon $end): array
    {
        $inventory = $this->getInventoryByRoomType($start, $end);
        $dates = $this->generateDateHeaders($start, $end);

        $dailyTotals = [];
        foreach ($dates as $day) {
            $dateStr = $day['date'];
            $totalAvailable = 0;
            $totalRooms = 0;
            $totalBooked = 0;

            foreach ($inventory as $roomType) {
                if (isset($roomType['dates'][$dateStr])) {
                    $dateData = $roomType['dates'][$dateStr];
                    $totalAvailable += $dateData['available'];
                    $totalRooms += $dateData['total'];
                    $totalBooked += $dateData['booked'];
                }
            }

            $dailyTotals[$dateStr] = [
                'total' => $totalRooms,
                'available' => $totalAvailable,
                'booked' => $totalBooked,
                'percent' => $totalRooms > 0 ? round(($totalAvailable / $totalRooms) * 100) : 0,
            ];
        }

        return [
            'room_types' => array_values($inventory),
            'dates' => $dates,
            'daily_totals' => $dailyTotals,
            'summary' => $this->calculateInventorySummary($inventory, $start, $end),
        ];
    }

    protected function calculateInventorySummary(array $inventory, Carbon $start, Carbon $end): array
    {
        $totalRoomNights = 0;
        $bookedRoomNights = 0;
        $blockedRoomNights = 0;
        $availableRoomNights = 0;

        foreach ($inventory as $roomType) {
            foreach ($roomType['dates'] as $dateData) {
                $totalRoomNights += $dateData['total'];
                $bookedRoomNights += $dateData['booked'];
                $blockedRoomNights += $dateData['blocked'];
                $availableRoomNights += $dateData['available'];
            }
        }

        return [
            'period' => $start->format('M d').' - '.$end->format('M d, Y'),
            'total_room_nights' => $totalRoomNights,
            'booked_room_nights' => $bookedRoomNights,
            'blocked_room_nights' => $blockedRoomNights,
            'available_room_nights' => $availableRoomNights,
            'occupancy_rate' => $totalRoomNights > 0
                ? round(($bookedRoomNights / $totalRoomNights) * 100, 1)
                : 0,
        ];
    }

    public function blockRooms(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate,
        int $blockedCount,
        string $blockType = 'manual',
        ?string $notes = null,
        ?int $userId = null
    ): RoomInventoryBlock {
        return RoomInventoryBlock::create([
            'room_type_id' => $roomTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'blocked_count' => $blockedCount,
            'block_type' => $blockType,
            'notes' => $notes,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    public function removeBlock(int $blockId): bool
    {
        $block = RoomInventoryBlock::find($blockId);

        return $block ? $block->delete() : false;
    }

    /**
     * Open (unblock) a date range for a room type.
     *
     * Unlike a plain delete, this only removes the requested range from any
     * overlapping blocks and preserves the remaining portions by splitting the
     * original block(s) around the opened range. This lets you open a sub-range
     * of a previously blocked range while keeping the rest blocked.
     *
     * If $openCount is provided, only that many rooms are freed per overlapping
     * block (the block's blocked_count is reduced by $openCount); otherwise the
     * whole range is opened. Stop-sell / restrictions on a block are preserved.
     */
    public function openRooms(int $roomTypeId, Carbon $startDate, Carbon $endDate, ?int $openCount = null): int
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->startOfDay();

        $blocks = RoomInventoryBlock::forRoomType($roomTypeId)
            ->overlapping($startDate, $endDate)
            ->get();

        $affected = 0;

        foreach ($blocks as $block) {
            $blockStart = Carbon::parse($block->start_date)->startOfDay();
            $blockEnd = Carbon::parse($block->end_date)->startOfDay();

            // Safety: skip if there is no actual overlap.
            if ($blockEnd->lt($startDate) || $blockStart->gt($endDate)) {
                continue;
            }

            // Rooms still blocked in the opened range after this open.
            $remainingBlocked = $openCount === null
                ? 0
                : max(0, $block->blocked_count - $openCount);

            // Keep a (reduced) segment if rooms remain blocked, or if the block
            // carries other restrictions we must preserve.
            $hasRestrictions = $block->stop_sell
                || $block->closed_to_arrival
                || $block->closed_to_departure
                || $block->min_stay
                || $block->max_stay;

            $keepOpenedSegment = $remainingBlocked > 0 || $hasRestrictions;

            // Portion before the opened range (keeps full blocked count).
            $before = null;
            if ($blockStart->lt($startDate)) {
                $before = [
                    'start_date' => $blockStart->copy(),
                    'end_date' => $startDate->copy()->subDay(),
                    'blocked_count' => $block->blocked_count,
                ];
            }

            // Portion after the opened range (keeps full blocked count).
            $after = null;
            if ($blockEnd->gt($endDate)) {
                $after = [
                    'start_date' => $endDate->copy()->addDay(),
                    'end_date' => $blockEnd->copy(),
                    'blocked_count' => $block->blocked_count,
                ];
            }

            // The opened range itself (reduced count, restrictions preserved).
            $opened = null;
            if ($keepOpenedSegment) {
                $opened = [
                    'start_date' => $startDate->copy(),
                    'end_date' => $endDate->copy(),
                    'blocked_count' => $remainingBlocked,
                ];
            }

            // Drop the original block (soft delete) and recreate the survivors.
            $block->delete();
            $affected++;

            foreach ([$before, $opened, $after] as $segment) {
                if (! $segment) {
                    continue;
                }
                if ($segment['start_date']->gt($segment['end_date'])) {
                    continue;
                }

                RoomInventoryBlock::create([
                    'room_type_id' => $block->room_type_id,
                    'start_date' => $segment['start_date'],
                    'end_date' => $segment['end_date'],
                    'blocked_count' => $segment['blocked_count'],
                    'block_type' => $block->block_type,
                    'min_stay' => $block->min_stay,
                    'max_stay' => $block->max_stay,
                    'stop_sell' => $block->stop_sell,
                    'closed_to_arrival' => $block->closed_to_arrival,
                    'closed_to_departure' => $block->closed_to_departure,
                    'notes' => $block->notes,
                    'created_by' => $block->created_by,
                ]);
            }
        }

        return $affected;
    }

    /**
     * Apply restrictions to a room type for a date range.
     */
    public function applyRestrictions(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate,
        array $restrictions,
        ?int $userId = null
    ): RoomInventoryBlock {
        return RoomInventoryBlock::create([
            'room_type_id' => $roomTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'blocked_count' => $restrictions['blocked_count'] ?? 0,
            'block_type' => $restrictions['block_type'] ?? 'manual',
            'min_stay' => $restrictions['min_stay'] ?? null,
            'max_stay' => $restrictions['max_stay'] ?? null,
            'stop_sell' => $restrictions['stop_sell'] ?? false,
            'closed_to_arrival' => $restrictions['closed_to_arrival'] ?? false,
            'closed_to_departure' => $restrictions['closed_to_departure'] ?? false,
            'notes' => $restrictions['notes'] ?? null,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    public function bulkUpdateInventory(array $updates, ?int $userId = null): array
    {
        $results = [];

        foreach ($updates as $update) {
            $roomTypeId = $update['room_type_id'];
            $startDate = Carbon::parse($update['start_date']);
            $endDate = Carbon::parse($update['end_date']);

            if ($update['replace_existing'] ?? false) {
                RoomInventoryBlock::forRoomType($roomTypeId)
                    ->overlapping($startDate, $endDate)
                    ->delete();
            }

            $block = $this->applyRestrictions(
                $roomTypeId,
                $startDate,
                $endDate,
                $update,
                $userId
            );

            $results[] = $block;
        }

        return $results;
    }

    public function getActiveBlocks(int $roomTypeId): Collection
    {
        return RoomInventoryBlock::forRoomType($roomTypeId)
            ->active()
            ->orderBy('start_date')
            ->get();
    }
}
