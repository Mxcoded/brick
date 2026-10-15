<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\RoomAvailabilityService;

/**
 * Turning a reservation into an in-house stay, from the panel.
 *
 * The legacy finalize screen is a wizard: it lets a group lead assign a room
 * and rate to every member at once, applies late-arrival billing policy and
 * folds members' bills into the lead under consolidated billing. That is far
 * more than an individual reservation needs, and reimplementing the whole thing
 * here would be a second copy of a financially sensitive flow.
 *
 * This service covers the common case the desk hits all day - one reservation,
 * one room, one rate - with the same availability guard the wizard uses. Group
 * leads with unassigned members are left to the finalize screen, which the
 * panel links to, so the group arithmetic stays in exactly one place.
 */
class CheckInService
{
    public function __construct(
        protected RoomAvailabilityService $availability,
        protected BookingStatusService $bookingStatus,
    ) {}

    /**
     * @throws InvalidArgumentException when the stay cannot be checked in
     */
    public function checkIn(Registration $registration, int $roomUnitId, float $rate, ?int $agentId = null): Registration
    {
        if (! in_array($registration->stay_status, ['reserved', 'draft_by_guest'], true)) {
            throw new InvalidArgumentException('Only a reserved or draft registration can be checked in.');
        }

        if ($registration->is_group_lead && $registration->children()
            ->whereIn('stay_status', ['reserved', 'draft_by_guest'])
            ->exists()) {
            throw new InvalidArgumentException(
                'This is a group booking with members still to be placed. Use the finalize screen to assign every room.'
            );
        }

        $unit = RoomUnit::with('roomType')->find($roomUnitId);

        if (! $unit) {
            throw new InvalidArgumentException('The selected room no longer exists.');
        }

        if (! $this->availability->isUnitAvailable(
            $unit->id,
            $registration->check_in,
            $registration->check_out,
            $registration->booking_id,
            $registration->id,
        )) {
            throw new InvalidArgumentException("Room {$unit->room_number} is not available for these dates.");
        }

        if ($rate < 0) {
            throw new InvalidArgumentException('The rate cannot be negative.');
        }

        $nights = max(1, (int) $registration->check_in->diffInDays($registration->check_out));

        DB::transaction(function () use ($registration, $unit, $rate, $nights, $agentId): void {
            $registration->update([
                'room_unit_id' => $unit->id,
                'room_type_id' => $unit->room_type_id,
                'room_allocation' => $unit->room_number.' ('.($unit->roomType->name ?? 'Room').')',
                'room_rate' => $rate,
                'no_of_nights' => $nights,
                'total_amount' => $rate * $nights,
                'stay_status' => 'checked_in',
                'checked_in_at' => now(),
                'finalized_by_agent_id' => $agentId,
            ]);

            RoomUnit::where('id', $unit->id)->update(['status' => 'occupied']);

            // Fold an online deposit into the stay's payments, exactly as the
            // finalize screen does. Keyed on the booking reference so a second
            // check-in attempt cannot double-count the money.
            $booking = $registration->booking;

            if ($booking && (float) $booking->amount_paid > 0) {
                $exists = $registration->payments()
                    ->where('reference', $booking->booking_reference)
                    ->exists();

                if (! $exists) {
                    $registration->payments()->create([
                        'amount' => $booking->amount_paid,
                        'payment_method' => $booking->payment_method ?? 'online',
                        'payment_date' => now()->toDateString(),
                        'reference' => $booking->booking_reference,
                        'notes' => 'Online payment synced from booking.',
                        'received_by' => $agentId,
                    ]);
                }
            }
        });

        $this->bookingStatus->sync($registration);
        $this->bookingStatus->notify($registration);

        return $registration->fresh();
    }
}
