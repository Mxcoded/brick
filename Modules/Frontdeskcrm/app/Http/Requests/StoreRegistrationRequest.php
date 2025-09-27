<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:10',
            'full_name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'contact_number' => 'required|string|max:20',
            'birthday' => 'nullable|date',
            'email' => 'nullable|email|unique:guests,email', // Uniqueness on guests
            'occupation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'home_address' => 'nullable|string',
            'room_type' => 'required|string|max:100',
            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'boolean',
            'check_in' => 'required|date|after_or_equal:today',
            'no_of_guests' => 'required|integer|min:1|max:10',
            'check_out' => 'required|date|after:check_in',
            'no_of_nights' => 'integer|min:1',
            'payment_method' => 'required|in:cash,pos,transfer',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:20',
            'agreed_to_policies' => 'required|accepted',
            'guest_signature' => 'required|string',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'guest_type_id' => 'required|exists:guest_types,id', // Dynamic CRUD via ID
            'opt_in_data_save' => 'boolean', // For prefs/history consent
            'is_group_lead' => 'boolean',
            'group_members.*.full_name' => 'required_with:is_group_lead|string|max:255',
            'group_members.*.contact_number' => 'required_with:is_group_lead|string|max:20',
            'group_members.*.room_assignment' => 'nullable|string|max:50',
        ];
    }
}
