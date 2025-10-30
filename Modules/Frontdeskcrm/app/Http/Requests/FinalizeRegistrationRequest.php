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
            'room_allocation' => 'required|string|max:255',
            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'nullable|boolean',

            // --- Billing (This was missing from the original DTO) ---
            'billing_type' => 'nullable|string|in:consolidate,individual',

            // --- Group Members Array ---
            'group_members' => 'nullable|array',

            // --- Group Member's Room/Status Details (as seen in the form) ---
            'group_members.*.room_allocation' => 'required|string|max:255',
            'group_members.*.room_rate' => 'required|numeric|min:0',
            'group_members.*.bed_breakfast' => 'nullable|boolean',

            // --- Status (This was the key field missing from the original DTO) ---
            'group_members.*.status' => 'required|string|in:checked_in,no_show',
        ];
    }
}
