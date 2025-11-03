<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() || $this->input('is_guest_draft'); // Allow unauthenticated submission if it's a draft
    }

    public function rules(): array
    {
        $isGuestDraft = $this->boolean('is_guest_draft', false);

        // Common rules for all submissions (guest details, agreements, stay info)
        $rules = [
            'title' => ['nullable', 'string', 'max:10'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],  // Added gender validation
            'nationality' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'birthday' => ['nullable', 'date'],
            // Allow duplicate email if it's a guest draft; otherwise, unique on guests
            'email' => [
                'nullable',
                'email',
                'max:255',
                $isGuestDraft ? '' : 'unique:guests,email'  // Assume unique on guests table
            ],
            'occupation' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'home_address' => ['nullable', 'string'],

            // Core agreement/signature
            'agreed_to_policies' => ['required', 'accepted'],
            'guest_signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg);base64,[A-Za-z0-9+\/=]+$/i'
            ],
            // Fixed: Added full base64 matcher and /i flag
            'opt_in_data_save' => ['nullable', 'boolean'],

            // Check-in details (required for all)
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'no_of_guests' => ['required', 'integer', 'min:1', 'max:10'],

            // Emergency contact
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact' => ['nullable', 'string', 'max:20', new ValidPhoneNumber],

            // Hidden draft field
            'is_guest_draft' => 'boolean',

            // Registration metadata (auto-set in model, but validate if provided)
            'registration_date' => ['nullable', 'date'],
            'front_desk_agent' => $isGuestDraft ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'],
        ];

        // Rules for agent/staff submissions (full check-in)
        if (!$isGuestDraft) {
            $rules = array_merge($rules, [
                'room_type' => ['required', 'string', 'max:100'],
                'room_rate' => ['required', 'numeric', 'min:0'],
                'payment_method' => ['required', 'in:cash,pos,transfer'],
                'booking_source_id' => ['required', 'exists:booking_sources,id'],
                'guest_type_id' => ['required', 'exists:guest_types,id'],
                'bed_breakfast' => ['nullable', 'boolean'],
                'is_group_lead' => ['nullable', 'boolean'],
                'group_members' => ['nullable', 'array', 'max:10'],
                'group_members.*.full_name' => ['required_if:is_group_lead,true', 'string', 'max:255'],
                'group_members.*.contact_number' => ['required_if:is_group_lead,true', 'string', 'max:20', new ValidPhoneNumber],
                'group_members.*.room_assignment' => ['nullable', 'string', 'max:50'],
                'stay_status' => Rule::in(['checked_in']),  // Enforce for full submissions
            ]);
        } else {
            // For guest drafts: Nullable for staff-finalized fields
            $rules = array_merge($rules, [
                'room_type' => ['nullable', 'string', 'max:100'],
                'room_rate' => ['nullable', 'numeric', 'min:0'],
                'payment_method' => ['nullable', 'in:cash,pos,transfer'],
                'booking_source_id' => ['nullable', 'exists:booking_sources,id'],
                'guest_type_id' => ['nullable', 'exists:guest_types,id'],
                'bed_breakfast' => ['nullable', 'boolean'],
                'is_group_lead' => ['nullable', 'boolean'],
                'group_members' => ['nullable', 'array', 'max:10'],  // Rare for drafts, but allow
                'group_members.*.full_name' => ['nullable', 'string', 'max:255'],
                'group_members.*.contact_number' => ['nullable', 'string', 'max:20', new ValidPhoneNumber],
                'group_members.*.room_assignment' => ['nullable', 'string', 'max:50'],
                'stay_status' => Rule::in(['draft_by_guest']),  // Enforce for drafts
            ]);
        }

        return $rules;
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'agreed_to_policies.accepted' => 'You must agree to the policies to proceed.',
            'guest_signature.required' => 'Please provide your signature.',
            'guest_signature.regex' => 'Signature must be a valid image (PNG/JPG).',
            'check_in.after_or_equal' => 'Check-in date must be today or in the future.',
            'check_out.after' => 'Check-out date must be after check-in.',
            'email.unique' => 'This email is already registered.',
            'booking_source_id.exists' => 'Invalid booking source selected.',
            'guest_type_id.exists' => 'Invalid guest type selected.',
            'gender.in' => 'Please select a valid gender.',
        ];
    }

    /**
     * Prepare data for validation (e.g., cast booleans).
     */
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
