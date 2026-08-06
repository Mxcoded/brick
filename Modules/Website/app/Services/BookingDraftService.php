<?php

namespace Modules\Website\Services;

/**
 * Session-backed booking state store.
 *
 * Persists the in-progress booking form so a guest can refresh or return
 * without losing their details (stepper auto-save). Complements the room
 * selection already held in the session cart by BookingCartService; this
 * store keeps the remaining form fields (guest details, requests, payment
 * preference) plus the single-room dates/room selection.
 */
class BookingDraftService
{
    protected string $sessionKey = 'booking_draft';

    /**
     * Fields that may be persisted as part of a booking draft.
     * Never stores passwords or the honeypot field.
     */
    public const ALLOWED_FIELDS = [
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_gender',
        'guest_address',
        'guest_nationality',
        'guest_dob',
        'guest_id_type',
        'guest_id_number',
        'adults',
        'children',
        'special_requests',
        'payment_method',
        'room_type_id',
        'room_unit_id',
        'check_in_date',
        'check_out_date',
        'create_account',
        'addons',
    ];

    public function get(): array
    {
        return (array) session($this->sessionKey, []);
    }

    public function getValue(string $key, $default = null)
    {
        return data_get($this->get(), $key, $default);
    }

    public function update(array $fields): void
    {
        session([$this->sessionKey => array_merge($this->get(), $fields)]);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }
}
