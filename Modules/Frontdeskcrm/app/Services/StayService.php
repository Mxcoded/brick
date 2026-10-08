<?php

namespace Modules\Frontdeskcrm\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\RoomAvailabilityService;

/**
 * Changes to a stay: moving the check-out date and, for groups, a selection of
 * named members' stays at once. An extension may also relocate the guest to a
 * different room in the same move when their current room is not available:
 *
 *  - Extending into a period where the same room is held by another active
 *    registration (checked-in, reserved or draft) or a live website booking is
 *    refused unless the guest is moved to a free room.
 *  - Shortening the stay never conflicts.
 *
 * The blade "Adjust stay" modal and the Filament "Extend Stay" action both go
 * through this service so recalculation, the availability guard and room moves
 * cannot drift apart between the two UIs.
 */
class StayService
{
    public function __construct(
        protected RoomAvailabilityService $availability,
    ) {}

    /**
     * Move a stay's check-out date (or a selection of a group's members' stays),
     * recalculating nights and the bills - a member's own bill from their rate
     * and nights, and the lead's bill as their personal room charge plus every
     * checked-in member's bill.
     *
     * When $roomUnitId is given and differs from the guest's current room, the
     * guest is relocated there as part of the extension (the target must be
     * free for the whole stay) and the source/target unit statuses are updated.
     *
     * @param  array<int>  $memberIds
     *
     * @throws InvalidArgumentException when any extension would double-book a room
     */
    public function adjustStay(Registration $registration, Carbon $newCheckOut, array $memberIds = [], ?int $roomUnitId = null): void
    {
        $targetUnitId = $roomUnitId ?? $registration->room_unit_id;

        if ($reason = $this->roomUnavailableReason($registration, $newCheckOut, $targetUnitId)) {
            throw new InvalidArgumentException(
                'Cannot extend stay. '.$reason.' Refusing to continue.'
            );
        }

        $membersToUpdate = collect();

        if ($registration->is_group_lead && ! empty($memberIds)) {
            $membersToUpdate = $registration->children()
                ->whereIn('id', $memberIds)
                ->get();

            foreach ($membersToUpdate as $member) {
                if ($reason = $this->roomUnavailableReason($member, $newCheckOut)) {
                    throw new InvalidArgumentException(
                        "Cannot extend stay for {$member->full_name}. {$reason} Refusing to continue."
                    );
                }
            }
        }

        if ($roomUnitId && $roomUnitId !== $registration->room_unit_id) {
            $this->applyMove($registration, $roomUnitId);
        }

        $this->apply($registration, $newCheckOut);

        foreach ($membersToUpdate as $member) {
            $this->apply($member, $newCheckOut);
        }

        $this->recalculateGroupTotal($registration);
    }

    /**
     * Why a guest cannot be kept in / moved to the given room for their stay
     * ending on $newCheckOut, or null when it is fine.
     *
     * Checks the room unit's own status, any competing registration (checked-in,
     * reserved, draft_by_guest - the same party is excluded) and any competing
     * website booking, for the whole [check-in, new check-out] window.
     */
    public function roomUnavailableReason(Registration $registration, Carbon $newCheckOut, ?int $roomUnitId = null): ?string
    {
        $unit = $roomUnitId
            ? RoomUnit::find($roomUnitId)
            : $registration->roomUnit;

        if (! $unit && ! $registration->room_id) {
            return null;
        }

        if ($newCheckOut->lte($registration->check_out)) {
            return null;
        }

        $checkIn = $registration->check_in;
        $ignoreIds = $this->sameStayIds($registration);
        $ignoreBookingId = $registration->booking_id;

        if ($unit) {
            $room = $unit->room_number;

            if (in_array($unit->status, RoomAvailabilityService::UNAVAILABLE_UNIT_STATUSES)) {
                return "{$room} is currently {$unit->status}";
            }

            $blocker = Registration::query()
                ->where('room_unit_id', $unit->id)
                ->whereIn('stay_status', RoomAvailabilityService::ACTIVE_REGISTRATION_STATUSES)
                ->whereNotIn('id', $ignoreIds)
                ->where(function ($query) use ($checkIn, $newCheckOut) {
                    $query->where('check_in', '<', $newCheckOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->orderBy('check_in')
                ->first();

            if ($blocker) {
                return "{$room} is held by {$blocker->full_name} "
                    .$blocker->check_in?->format('M j, Y').' – '.$blocker->check_out?->format('M j, Y');
            }

            $booking = Booking::query()
                ->where('room_unit_id', $unit->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
                ->where(function ($query) use ($checkIn, $newCheckOut) {
                    $query->where('check_in_date', '<', $newCheckOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                ->orderBy('check_in_date')
                ->first();

            if ($booking) {
                return "{$room} is reserved for {$booking->guest_name} "
                    .$booking->check_in_date?->format('M j, Y').' – '.$booking->check_out_date?->format('M j, Y');
            }

            return null;
        }

        // Legacy rooms (no unit): fall back to matching the legacy room id.
        $blocker = Registration::query()
            ->where('room_id', $registration->room_id)
            ->whereIn('stay_status', RoomAvailabilityService::ACTIVE_REGISTRATION_STATUSES)
            ->whereNotIn('id', $ignoreIds)
            ->where(function ($query) use ($checkIn, $newCheckOut) {
                $query->where('check_in', '<', $newCheckOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->first();

        if ($blocker) {
            return 'this room is held by '.$blocker->full_name;
        }

        return null;
    }

    /**
     * Room units of the guest's room type that are free for their stay ending
     * on $newCheckOut. The guest's current room is included first when it is
     * still available so the move control can preselect it.
     *
     * @return Collection<int, RoomUnit>
     */
    public function availableMoveUnits(Registration $registration, Carbon $newCheckOut): Collection
    {
        $roomTypeId = $registration->room_type_id ?? $registration->roomUnit?->room_type_id;

        if (! $roomTypeId) {
            return collect();
        }

        $units = RoomUnit::query()
            ->where('room_type_id', $roomTypeId)
            ->whereNotIn('status', RoomAvailabilityService::UNAVAILABLE_UNIT_STATUSES)
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get()
            ->filter(
                fn (RoomUnit $unit) => $this->roomUnavailableReason($registration, $newCheckOut, $unit->id) === null
            );

        $current = $units->firstWhere('id', $registration->room_unit_id);
        $others = $units->reject(fn (RoomUnit $unit) => $unit->id === $registration->room_unit_id)->values();

        return $current ? collect([$current])->merge($others) : $others;
    }

    /**
     * Registrations belonging to the same party (- lead, - children) are never
     * a booking conflict for each other. Called for both the lead and members.
     *
     * @return array<int>
     */
    private function sameStayIds(Registration $registration): array
    {
        $ids = [$registration->id];

        if ($registration->is_group_lead) {
            return array_merge($ids, $registration->children()->pluck('id')->all());
        }

        if ($registration->parent_registration_id) {
            $ids[] = $registration->parent_registration_id;
        }

        return $ids;
    }

    /**
     * Relocate a registration to another unit, syncing both units' status and
     * the linked web booking so availability queries stay accurate.
     */
    private function applyMove(Registration $registration, int $roomUnitId): void
    {
        $unit = RoomUnit::find($roomUnitId);

        if (! $unit) {
            throw new InvalidArgumentException('The selected room no longer exists.');
        }

        $oldUnitId = $registration->room_unit_id;

        $registration->update([
            'room_unit_id' => $unit->id,
            'room_type_id' => $registration->room_type_id ?? $unit->room_type_id,
        ]);

        if ($oldUnitId && $oldUnitId !== $unit->id) {
            RoomUnit::find($oldUnitId)?->update(['status' => 'available']);
        }

        if ($unit->status !== 'occupied') {
            $unit->update(['status' => 'occupied']);
        }

        if ($registration->booking && $registration->booking->room_unit_id === $oldUnitId) {
            $registration->booking->update(['room_unit_id' => $unit->id]);
        }
    }

    private function apply(Registration $registration, Carbon $newCheckOut): void
    {
        $nights = $registration->check_in->diffInDays($newCheckOut);

        if ($nights < 1) {
            $nights = 1;
        }

        $registration->update([
            'check_out' => $newCheckOut,
            'no_of_nights' => $nights,
            'total_amount' => $registration->room_rate * $nights,
        ]);
    }

    /**
     * The lead's bill sits at group level: their personal room charge plus the
     * total of every currently checked-in member's bill.
     */
    private function recalculateGroupTotal(Registration $registration): void
    {
        $lead = $registration->is_group_lead ? $registration : $registration->parent;

        if (! $lead) {
            return;
        }

        $leadPersonalBill = $lead->room_rate * $lead->no_of_nights;

        $membersTotalBill = $lead->children()
            ->where('stay_status', 'checked_in')
            ->sum('total_amount');

        $lead->update([
            'total_amount' => $leadPersonalBill + $membersTotalBill,
        ]);
    }
}
