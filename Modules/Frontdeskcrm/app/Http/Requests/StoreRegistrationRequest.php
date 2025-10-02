<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() || $this->input('is_guest_draft'); // Allow unauthenticated submission if it's a draft
    }

    public function rules(): array
    {
        $isGuestDraft = $this->input('is_guest_draft', false);

        // Rules for the initial guest draft (minimal required fields)
        $rules = [
            'title' => 'nullable|string|max:10',
            'full_name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'contact_number' => 'required|string|max:20',
            'birthday' => 'nullable|date',
            // Allow duplicate email if it's a guest draft since staff will finalize the booking later
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'home_address' => 'nullable|string',

            // Core agreement/signature
            'agreed_to_policies' => 'required|accepted',
            'guest_signature' => 'required|string',
            'opt_in_data_save' => 'boolean',

            // Check-in details are still required
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'no_of_guests' => 'required|integer|min:1|max:10',

            // Emergency contact
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:20',

            // Hidden draft field
            'is_guest_draft' => 'boolean',
        ];

        // Rules required only when the staff is submitting the final check-in
        if (!$isGuestDraft) {
            $rules = array_merge($rules, [
                'room_type' => 'required|string|max:100',
                'room_rate' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,pos,transfer',
                'booking_source_id' => 'required|exists:booking_sources,id',
                'guest_type_id' => 'required|exists:guest_types,id',
                'bed_breakfast' => 'boolean',
                'is_group_lead' => 'boolean',
                'group_members' => 'nullable|array',
                'group_members.*.full_name' => 'required_if:is_group_lead,true|string|max:255',
                'group_members.*.contact_number' => 'required_if:is_group_lead,true|string|max:20',
                'group_members.*.room_assignment' => 'nullable|string|max:50',
            ]);
        } else {
            // For guest draft, we ignore fields the guest shouldn't know/set
            $rules = array_merge($rules, [
                'room_type' => 'nullable|string|max:100',
                'room_rate' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|in:cash,pos,transfer',
                'booking_source_id' => 'nullable|exists:booking_sources,id',
                'guest_type_id' => 'nullable|exists:guest_types,id',
            ]);
        }

        return $rules;
    }
}
