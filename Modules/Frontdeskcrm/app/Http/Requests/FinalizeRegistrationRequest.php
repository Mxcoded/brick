<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Or add agent permission check
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // --- Main Guest/Booking Details ---
            'guest_type_id' => 'required|exists:guest_types,id',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'payment_method' => 'required|string',

            // ✅ FIX: Require 'room_id' instead of 'room_allocation'
            'room_id' => 'required|exists:rooms,id',
            // 'room_allocation' => 'required|string|max:255', <--- REMOVE THIS OLD RULE

            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'nullable|boolean',

            // --- Billing ---
            'billing_type' => 'nullable|string|in:consolidate,Split',

            // --- Group Members Array ---
            'group_members' => 'nullable|array',

            // --- Group Member's Rules ---
            // ✅ FIX: Require 'room_id' ONLY if they are checking in
            'group_members.*.status' => 'required|string|in:checked_in,no_show',
            'group_members.*.room_id' => 'required_if:group_members.*.status,checked_in|nullable|exists:rooms,id',
            // 'group_members.*.room_allocation' => 'required|string|max:255', <--- REMOVE THIS OLD RULE

            'group_members.*.room_rate' => 'required|numeric|min:0',
            'group_members.*.bed_breakfast' => 'nullable|boolean',
        ];
    }
}
