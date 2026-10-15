<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\RoomUnit;

/**
 * Closing a stay.
 *
 * Checkout used to live entirely inside RegistrationController, which meant the
 * Blade registration screen was the only place a stay could be closed. The
 * Filament registration screen now offers the same action, and the two must not
 * drift: the early/late date trimming, the room release, the group sync, the
 * booking hand-back and - the part that matters most here - the automatic move
 * of an unpaid balance onto the guest's city ledger account all have to behave
 * identically no matter which UI triggered the checkout.
 *
 * A stay is "flagged" for city ledger billing simply by having a
 * `city_ledger_account_id` assigned (a reservation can be created against a
 * company account). At checkout, a flagged stay with a positive balance has its
 * outstanding amount posted to that account exactly once - `CityLedgerService`
 * refuses a second transfer - and the guest's folio is settled. A stay without
 * an account keeps its balance as an ordinary guest debt, and checkout reports a
 * warning rather than silently writing the debt off.
 */
class CheckoutService
{
    public function __construct(
        protected FolioService $folioService,
        protected CityLedgerService $cityLedgerService,
        protected BookingStatusService $bookingStatus,
    ) {}

    /**
     * Close one registration (and its group members).
     *
     * @return array{
     *     registration: Registration,
     *     message: string,
     *     warnings: array<int, string>,
     *     city_ledger_posted: bool,
     *     outstanding: float
     * }
     *
     * @throws InvalidArgumentException when the stay is not currently checked in
     */
    public function checkout(Registration $registration, ?int $agentId = null): array
    {
        if ($registration->stay_status !== 'checked_in') {
            throw new InvalidArgumentException('This guest is not currently checked-in.');
        }

        $now = now();

        // 1. The stay's own status change, possibly trimming/extending the dates.
        $updates = [
            'stay_status' => 'checked_out',
            'actual_checkout_at' => $now,
            'checked_out_by_agent_id' => $agentId,
        ];

        $early = $now->startOfDay()->lt($registration->check_out->startOfDay());
        $late = $now->startOfDay()->gt($registration->check_out->startOfDay());

        if ($early) {
            $nights = max(1, (int) $registration->check_in->diffInDays($now));

            $updates['check_out'] = $now;
            $updates['no_of_nights'] = $nights;
            $updates['total_amount'] = $registration->room_rate * $nights;
        } elseif ($late) {
            $nights = (int) $registration->check_in->diffInDays($now);

            $updates['check_out'] = $now;
            $updates['no_of_nights'] = $nights;
            $updates['total_amount'] = $registration->room_rate * $nights;
        }

        $checkedOut = collect();

        DB::transaction(function () use ($registration, $updates, $now, &$checkedOut): void {
            $registration->update($updates);
            $checkedOut->push($registration);

            $this->releaseRoom($registration);

            $guest = $registration->guest;
            if ($guest) {
                $guest->increment('visit_count');
                $guest->last_visit_at = $now;
                $guest->save();
            }

            // Group members follow the lead: same final dates, same release.
            if ($registration->is_group_lead) {
                foreach ($registration->children as $child) {
                    if ($child->stay_status !== 'checked_in') {
                        continue;
                    }

                    $child->update([
                        'stay_status' => 'checked_out',
                        'actual_checkout_at' => $now,
                        'checked_out_by_agent_id' => $updates['checked_out_by_agent_id'],
                        'check_out' => $updates['check_out'] ?? $child->check_out,
                        'no_of_nights' => $updates['no_of_nights'] ?? $child->no_of_nights,
                        'total_amount' => isset($updates['no_of_nights'])
                            ? ($child->room_rate * $updates['no_of_nights'])
                            : $child->total_amount,
                    ]);

                    $this->releaseRoom($child);
                    $checkedOut->push($child);
                }
            }
        });

        // 2. Move any flagged balance onto its city ledger account. Done outside
        //    the checkout transaction so a ledger failure cannot roll the stay
        //    back to checked-in - the guest has physically left.
        $warnings = [];
        $cityLedgerPosted = false;
        $outstanding = 0.0;

        foreach ($checkedOut as $stay) {
            $stayOutstanding = $this->folioService->getOutstandingBalance($stay);
            $outstanding += $stayOutstanding;

            if ($stayOutstanding <= 0) {
                continue;
            }

            $account = $stay->cityLedgerAccount;

            if (! $account) {
                $label = $checkedOut->count() > 1 ? "{$stay->full_name}: " : '';

                $warnings[] = $label.'Outstanding '.number_format($stayOutstanding, 2)
                    .' remains on the folio. Settle it with a payment or assign a city ledger account.';

                continue;
            }

            if ($stay->city_ledger_transferred_at !== null) {
                continue;
            }

            try {
                $this->cityLedgerService->postOutstandingToLedger($account, $stay, $agentId);
                $cityLedgerPosted = true;
            } catch (\Throwable $e) {
                Log::error('City ledger auto-transfer failed at checkout', [
                    'registration_id' => $stay->id,
                    'city_ledger_account_id' => $account->id,
                    'message' => $e->getMessage(),
                ]);

                $warnings[] = 'Could not post '.number_format($stayOutstanding, 2)
                    ." to {$account->name}: ".$e->getMessage();
            }
        }

        // 3. Hand the booking status back to the Website module.
        foreach ($checkedOut as $stay) {
            $this->bookingStatus->sync($stay);
        }

        // 4. Tell the guest, if we have an address. A mail failure must never
        //    undo or hide a completed checkout.
        $this->bookingStatus->notify($registration);

        $message = "Guest {$registration->full_name} checked out successfully.";

        if ($early) {
            $message .= ' Bill adjusted for early departure.';
        } elseif ($late) {
            $message .= ' Stay extended and bill updated for overstay.';
        }

        if ($cityLedgerPosted) {
            $message .= ' Outstanding balance posted to the city ledger.';
        }

        return [
            'registration' => $registration->fresh(),
            'message' => $message,
            'warnings' => $warnings,
            'city_ledger_posted' => $cityLedgerPosted,
            'outstanding' => $outstanding,
        ];
    }

    private function releaseRoom(Registration $registration): void
    {
        if ($registration->room_unit_id) {
            RoomUnit::where('id', $registration->room_unit_id)->update(['status' => 'available']);
        }
    }
}
