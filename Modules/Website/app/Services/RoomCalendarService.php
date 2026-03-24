<?php

namespace Modules\Website\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Models\Booking;
use Modules\Frontdeskcrm\Models\Registration;

class RoomCalendarService
{
    /**
     * Status color mapping for consistency across all views.
     */
    const STATUS_COLORS = [
        'checked_in'    => '#32CD32', // Light Green
        'checked_out'   => '#006400', // Dark Green
        'reserved'      => '#00CED1', // Cyan
        'draft_by_guest'=> '#FFC107', // Yellow
        'online_booking'=> '#0d6efd', // Blue
        'maintenance'   => '#FF00FF', // Magenta
        'available'     => null,
    ];

    /**
     * Status labels for display.
     */
    const STATUS_LABELS = [
        'checked_in'    => 'In-House',
        'checked_out'   => 'Checked Out',
        'reserved'      => 'Reserved',
        'draft_by_guest'=> 'Pending',
        'online_booking'=> 'Online',
        'maintenance'   => 'Maintenance',
        'available'     => 'Available',
    ];

    /**
     * Get all room units (with fallback to legacy rooms).
     */
    public function getRoomUnits(): Collection
    {
        $roomUnits = RoomUnit::with('roomType')
            ->orderBy('room_number')
            ->get();

        // Fallback to legacy Rooms if no RoomUnits exist
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

    /**
     * Get bookings for a date range.
     */
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

    /**
     * Get registrations for a date range.
     */
    public function getRegistrations(Carbon $start, Carbon $end): Collection
    {
        if (!class_exists(Registration::class)) {
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

    /**
     * Get active registrations for today (for room rack).
     */
    public function getActiveRegistrations(): Collection
    {
        if (!class_exists(Registration::class)) {
            return collect();
        }

        $today = now()->format('Y-m-d');

        return Registration::whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->get();
    }

    /**
     * Get active bookings for today (for room rack).
     */
    public function getActiveBookings(): Collection
    {
        $today = now()->format('Y-m-d');

        return Booking::where('status', '!=', 'cancelled')
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->get();
    }

    /**
     * Generate date headers for a month.
     */
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

    /**
     * Check if a registration matches a room unit.
     */
    public function registrationMatchesRoom($registration, $unitId, string $roomNumber, bool $isLegacy): bool
    {
        if (!$isLegacy && $registration->room_unit_id == $unitId) {
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

    /**
     * Check if a booking matches a room unit.
     */
    public function bookingMatchesRoom($booking, $unitId, bool $isLegacy): bool
    {
        if (!$isLegacy && $booking->room_unit_id == $unitId) {
            return true;
        }
        if ($booking->room_id == $unitId) {
            return true;
        }
        return false;
    }

    /**
     * Build display name for a room unit.
     */
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

    /**
     * Parse room name to extract type and number.
     */
    public function parseRoomName(string $name): array
    {
        // Try to extract "Room 101 (Deluxe Suite)" -> {type: "Deluxe Suite", number: "101"}
        if (preg_match('/Room\s+(\w+)\s*\(([^)]+)\)/i', $name, $match)) {
            return ['number' => $match[1], 'type' => $match[2]];
        }
        return ['type' => $name, 'number' => ''];
    }

    /**
     * Format date for display.
     */
    public function formatDate($date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }
        return substr($date, 0, 10);
    }

    /**
     * Get color for a status.
     */
    public function getStatusColor(string $status): ?string
    {
        return self::STATUS_COLORS[$status] ?? '#6c757d';
    }

    /**
     * Get label for a status.
     */
    public function getStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? 'Unknown';
    }

    /**
     * Build calendar data for API response.
     */
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

    /**
     * Build calendar data for a single room.
     */
    protected function buildRoomCalendarData($unit, Collection $bookings, Collection $registrations, Carbon $start, Carbon $end): array
    {
        $events = [];
        $isLegacy = $unit->is_legacy ?? false;
        $unitId = $unit->id;
        $roomNumber = $unit->room_number;

        // 1. Maintenance Status
        if ($unit->status === 'maintenance') {
            $events[] = [
                'id' => 'maint-' . $unitId,
                'title' => 'Maintenance',
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'color' => self::STATUS_COLORS['maintenance'],
                'status' => 'maintenance'
            ];
        }

        // 2. Frontdesk Registrations (prioritized)
        foreach ($registrations as $reg) {
            if ($this->registrationMatchesRoom($reg, $unitId, $roomNumber, $isLegacy)) {
                $events[] = [
                    'id' => 'reg-' . $reg->id,
                    'title' => "{$reg->full_name} ({$this->getStatusLabel($reg->stay_status)})",
                    'start' => $this->formatDate($reg->check_in),
                    'end' => $this->formatDate($reg->check_out),
                    'color' => $this->getStatusColor($reg->stay_status),
                    'status' => $reg->stay_status,
                ];
            }
        }

        // 3. Website Bookings (if not already converted)
        foreach ($bookings as $booking) {
            if ($this->bookingMatchesRoom($booking, $unitId, $isLegacy)) {
                $hasRegistration = $registrations->where('booking_id', $booking->id)->isNotEmpty();
                
                if (!$hasRegistration) {
                    $events[] = [
                        'id' => 'bk-' . $booking->id,
                        'title' => "{$booking->guest_name} (Online)",
                        'start' => $this->formatDate($booking->check_in_date),
                        'end' => $this->formatDate($booking->check_out_date),
                        'color' => self::STATUS_COLORS['online_booking'],
                        'status' => 'online_booking',
                    ];
                }
            }
        }

        // Parse room name for display
        $displayName = $this->buildRoomDisplayName($unit);
        $parsed = $this->parseRoomName($displayName);

        return [
            'id' => $unitId,
            'name' => $displayName,
            'room_type' => $parsed['type'],
            'room_number' => $parsed['number'] ?: $roomNumber,
            'capacity' => $unit->roomType->capacity ?? 2,
            'events' => $events
        ];
    }

    /**
     * Build room rack/status data for API response.
     */
    public function getRoomStatusData(): Collection
    {
        $roomUnits = $this->getRoomUnits();
        $activeRegistrations = $this->getActiveRegistrations();
        $activeBookings = $this->getActiveBookings();

        return $roomUnits->map(function ($unit) use ($activeRegistrations, $activeBookings) {
            return $this->buildRoomStatusData($unit, $activeRegistrations, $activeBookings);
        });
    }

    /**
     * Build status data for a single room.
     */
    protected function buildRoomStatusData($unit, Collection $activeRegistrations, Collection $activeBookings): array
    {
        $unitId = $unit->id;
        $roomNumber = $unit->room_number;
        $isLegacy = $unit->is_legacy ?? false;
        $displayName = $this->buildRoomDisplayName($unit);

        // A. Maintenance Check
        if ($unit->status === 'maintenance') {
            return $this->formatRoomStatus($unitId, $displayName, 'maintenance', 'secondary', 'Maintenance');
        }

        // B. Frontdesk Priority Check
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
                $registration->check_out
            );
        }

        // C. Website Booking Check
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
                $booking->check_out_date
            );
        }

        // D. Available
        return $this->formatRoomStatus($unitId, $displayName, 'available', 'success', 'Vacant');
    }

    /**
     * Format room status for API response.
     */
    protected function formatRoomStatus($id, string $name, string $status, string $color, string $guest, $checkout = null): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'status' => $status,
            'color' => $color,
            'guest' => $guest,
            'checkout' => $checkout ? Carbon::parse($checkout)->format('M d') : null,
        ];
    }

    /**
     * Get occupancy stats for a specific date.
     */
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
            
            // Check registrations
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
            
            // Check bookings
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
}
