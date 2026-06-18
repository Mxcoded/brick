<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
// IMPORT CUSTOM RULES
use Modules\Frontdeskcrm\Rules\ValidEmail;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 1. Check if this is an Agent Walk-in (Internal)
        $isAgentWalkin = $this->routeIs('frontdesk.registrations.storeWalkin');

        $isGuestDraft = $this->boolean('is_guest_draft', false);

        // 1. BASE RULES (Required for everyone)
        $rules = [
            'full_name' => 'required|string|max:255',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'no_of_guests' => 'required|integer|min:1|max:22',

            // Walk-in specific optional fields
            'title' => 'nullable|string|max:10',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_unit_id' => 'nullable|exists:room_units,id',
            'room_rate' => 'nullable|numeric|min:0',
            'billing_type' => 'nullable|string|in:consolidate,split',
            'payment_method' => 'nullable|string|max:50',
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:20',

            // ✅ CONDITIONAL: Only require specific fields if NOT an agent walk-in
            'is_group_lead' => 'boolean',
            'agreed_to_policies' => $isAgentWalkin ? 'nullable' : 'required|accepted',
            'opt_in_data_save' => 'boolean',

            // Signature is optional for agent walk-ins (guest signs later via kiosk)
            'guest_signature' => $isAgentWalkin ? 'nullable' : [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg);base64,[A-Za-z0-9+\/=]+$/i',
            ],

            // Group Members (Apply API Validation here too)
            'group_members' => 'nullable|array|max:10',
            'group_members.*.full_name' => 'required|string|max:255',
            'group_members.*.contact_number' => [
                'nullable',
                'string',
                'max:20',
                new ValidPhoneNumber,
            ],
            'group_members.*.email' => [
                'nullable',
                'email',
                'max:255',
                new ValidEmail,
            ],
        ];

        // 2. CONDITIONAL RULES FOR PERSONAL DATA
        // If "Returning Guest" (Secure Mode), fields are hidden/nullable.
        if (session()->has('returning_guest')) {
            $rules['title'] = 'nullable|string|max:10';
            $rules['full_name'] = 'nullable|string|max:255';
            $rules['gender'] = 'nullable|in:male,female,other';
            $rules['nationality'] = 'nullable|string|max:100';
            $rules['contact_number'] = 'nullable|string|max:20';
            $rules['email'] = 'nullable|email|max:255';
            $rules['occupation'] = 'nullable|string|max:255';
            $rules['company_name'] = 'nullable|string|max:255';
            $rules['home_address'] = 'nullable|string';
            $rules['emergency_name'] = 'nullable|string|max:255';
            $rules['emergency_contact'] = 'nullable|string|max:20';
        } else {
            // New Guest: Enforce strict requirements + API Validation
            $rules['title'] = 'nullable|string|max:10';
            $rules['full_name'] = 'required|string|max:255';
            $rules['gender'] = 'nullable|in:male,female,other';
            $rules['nationality'] = 'nullable|string|max:100';

            $rules['contact_number'] = [
                'required',
                'string',
                'max:20',
                new ValidPhoneNumber, // <-- RESTORED
            ];

            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                new ValidEmail, // <-- RESTORED
            ];

            $rules['occupation'] = 'nullable|string|max:255';
            $rules['company_name'] = 'nullable|string|max:255';
            $rules['home_address'] = 'nullable|string';
            $rules['emergency_name'] = 'nullable|string|max:255';
            $rules['emergency_contact'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'agreed_to_policies.accepted' => 'You must agree to the policies to proceed.',
            'guest_signature.required' => 'Please provide your signature.',
            'check_in.after_or_equal' => 'Check-in date must be today or in the future.',
            'contact_number.required' => 'A valid contact number is required.',
            'group_members.*.contact_number' => 'Please provide a valid phone number for group members.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_guest_draft' => $this->boolean('is_guest_draft'),
            'bed_breakfast' => $this->boolean('bed_breakfast'),
            'is_group_lead' => $this->boolean('is_group_lead'),
            'opt_in_data_save' => $this->boolean('opt_in_data_save'),
            'agreed_to_policies' => $this->boolean('agreed_to_policies'),
        ]);
    }
}
