<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or add agent permission check
    }

    public function rules(): array
    {
        return [
            'guest_type_id' => 'required|exists:guest_types,id',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'payment_method' => 'required|string|in:cash,pos,transfer',
            'room_allocation' => 'required|string|max:255',
            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'nullable|boolean',
            'stay_status' => 'required|in:checked_in,no_show',
            'group_members' => 'nullable|array',
            'group_members.*.stay_status' => 'required|in:checked_in,no_show',
            'group_members.*.full_name' => 'required|string|max:255',
            'group_members.*.contact_number' => 'required|string|max:20',
            'group_members.*.room_allocation' => 'required|string|max:255',
            'group_members.*.room_rate' => 'required|numeric|min:0',
            'group_members.*.bed_breakfast' => 'nullable|boolean',
        ];
    }
}
