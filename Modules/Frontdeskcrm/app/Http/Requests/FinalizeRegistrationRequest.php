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
        return true;
    }

    /**
     * Prepare the data for validation.
     * Strips commas from numeric fields before validation.
     */
    protected function prepareForValidation(): void
    {
        // Strip commas from room_rate
        if ($this->has('room_rate')) {
            $this->merge([
                'room_rate' => str_replace(',', '', $this->room_rate),
            ]);
        }

        // Strip commas from group member rates
        if ($this->has('group_members')) {
            $members = $this->group_members;
            foreach ($members as $id => $member) {
                if (isset($member['room_rate'])) {
                    $members[$id]['room_rate'] = str_replace(',', '', $member['room_rate']);
                }
            }
            $this->merge(['group_members' => $members]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // --- Main Guest/Booking Details ---
            'guest_type_id' => 'required|exists:guest_types,id',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'payment_method' => 'required|string',

            // Room assignment (new structure: room_unit_id + room_type_id)
            'room_unit_id' => 'required|exists:room_units,id',
            'room_type_id' => 'nullable',
            'room_allocation' => 'nullable|string|max:255',

            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'nullable',

            // --- Billing ---
            'billing_type' => 'nullable|string|in:consolidate,individual',
            'billing_policy' => 'nullable|string|in:strict,flexible',

            // --- Group Members Array ---
            'group_members' => 'nullable|array',

            // --- Group Member's Rules ---
            'group_members.*.status' => 'sometimes|string|in:checked_in,no_show',
            'group_members.*.room_unit_id' => 'nullable',
            'group_members.*.room_type_id' => 'nullable',
            'group_members.*.room_allocation' => 'nullable|string|max:255',
            'group_members.*.room_rate' => 'nullable|numeric|min:0',
            'group_members.*.bed_breakfast' => 'nullable',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'room_unit_id.required' => 'Please select a room for the guest.',
            'room_unit_id.exists' => 'The selected room is invalid.',
            'room_rate.required' => 'Please enter the room rate.',
            'room_rate.numeric' => 'Room rate must be a valid number.',
        ];
    }
}
