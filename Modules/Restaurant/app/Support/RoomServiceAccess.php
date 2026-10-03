<?php

namespace Modules\Restaurant\Support;

use App\Models\User;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Room;

/**
 * Who may order room service, and to which room.
 *
 * Room service used to be an anonymous choice on the public landing page: the
 * visitor picked any room from a dropdown and ordered against it. That let
 * anyone bill a stranger's room. The room is now derived from the guest's own
 * checked-in stay, so the only room a guest can order to is the one they are
 * occupying.
 *
 * The stay lives in Frontdeskcrm's Registration, which is the record the front
 * desk actually flips on check-in. The room it points at is the Website Room
 * the restaurant module already serves, so no new mapping is introduced.
 */
class RoomServiceAccess
{
    /**
     * The room the given user is currently checked into, if any.
     *
     * Null means they are not entitled to room service: no account, no
     * checked-in stay, or a stay with no room attached to it.
     */
    public function roomFor(?User $user): ?Room
    {
        if (! $user instanceof User
            || ! class_exists(Registration::class)
            || ! class_exists(Room::class)) {
            return null;
        }

        $registration = Registration::query()
            ->where('stay_status', 'checked_in')
            ->whereNotNull('room_id')
            // Prefer the stay linked to this account's own booking. A front-desk
            // walk-in has no booking, so the same email is accepted as the link
            // of last resort -- it is still the guest's own stay.
            ->where(function ($query) use ($user) {
                $query->whereHas('booking', fn ($booking) => $booking->where('user_id', $user->id))
                    ->orWhere('email', $user->email);
            })
            ->latest('check_in')
            ->first();

        if (! $registration instanceof Registration) {
            return null;
        }

        return Room::find($registration->room_id);
    }

    /**
     * Whether the given user is entitled to order to the given room.
     *
     * This is the check the room-service routes run, so a guest cannot reach
     * another room by editing the id in the URL.
     */
    public function allows(?User $user, mixed $roomId): bool
    {
        $room = $this->roomFor($user);

        return $room !== null && (int) $room->id === (int) $roomId;
    }
}
