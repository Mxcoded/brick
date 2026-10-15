<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Frontdeskcrm\Emails\RegistrationStatusMail;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;

/**
 * The two side effects every stay transition shares.
 *
 * Checking in, checking out and marking a no-show all have to keep the linked
 * Website booking in step and, if the guest left an address, send them a status
 * mail. Those two jobs were copied into the controller, into CheckoutService and
 * (would be) into CheckInService, which is three places for the booking status
 * map to drift apart. They live here instead.
 */
class BookingStatusService
{
    /**
     * Mirror the registration's stay status onto its Website booking, and onto
     * every booking in the group when the registration is a group lead.
     */
    public function sync(Registration $registration): void
    {
        if (! $registration->booking_id) {
            return;
        }

        $booking = $registration->booking;

        if (! $booking) {
            return;
        }

        $statusMap = [
            'checked_in' => 'checked_in',
            'checked_out' => 'completed',
            'no_show' => 'no_show',
            'reserved' => 'confirmed',
        ];

        $newBookingStatus = $statusMap[$registration->stay_status] ?? null;

        if ($newBookingStatus && $booking->status !== $newBookingStatus) {
            $booking->update(['status' => $newBookingStatus]);

            Log::info('Booking status synced', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'registration_id' => $registration->id,
                'new_status' => $newBookingStatus,
            ]);
        }

        if ($registration->booking_group_id) {
            $this->syncGroup($registration);
        }
    }

    /**
     * Send the guest a status mail. A mail failure is logged, never thrown: a
     * dead SMTP server must not undo or hide a completed stay transition.
     */
    public function notify(Registration $registration): void
    {
        $email = $registration->email ?? $registration->guest?->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new RegistrationStatusMail($registration));
        } catch (\Throwable $e) {
            Log::error('Failed to send registration email: '.$e->getMessage());
        }
    }

    private function syncGroup(Registration $registration): void
    {
        if (! $registration->booking_group_id || ! $registration->is_group_lead) {
            return;
        }

        $statusMap = [
            'checked_in' => 'checked_in',
            'checked_out' => 'completed',
            'no_show' => 'no_show',
        ];

        $newStatus = $statusMap[$registration->stay_status] ?? null;

        if (! $newStatus) {
            return;
        }

        Booking::where('booking_group_id', $registration->booking_group_id)
            ->update(['status' => $newStatus]);

        Log::info('Group booking status synced', [
            'booking_group_id' => $registration->booking_group_id,
            'new_status' => $newStatus,
        ]);
    }
}
