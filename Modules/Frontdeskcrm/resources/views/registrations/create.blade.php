@extends('frontdeskcrm::layouts.master')

@section('title', 'Guest Check-In Draft')

@section('page-content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-glass-bg backdrop-blur-md rounded-xl border border-glass-border shadow-2xl overflow-hidden text-base-white">
        
        <div class="bg-gradient-to-r from-primary-dark to-primary-medium px-6 py-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-base-white">Guest Check-In Draft Form</h1>
            <p class="text-primary-light text-sm">Please submit your details for a faster check-in at the front desk. Booking and Rate information will be finalized by our agent.</p>
        </div>
        
        <form 
            {{-- CRITICAL: Initialize as TRUE for isGuestDraft --}}
            x-data="formHandler({{ json_encode($oldData ?? []) }}, true)" 
            method="POST" 
            action="{{ route('frontdesk.registrations.store') }}" 
            class="p-6 space-y-6 bg-black/50" 
        >
            @csrf
            
            <input type="hidden" name="guest_id" x-model="selectedGuestId">
            <input type="hidden" name="is_guest_draft" value="1"> 
            
            {{-- SEARCH BAR REMOVED --}}

            {{-- STEPPER NAVIGATION (Unchanged) --}}
            <div class="flex justify-between items-center text-sm font-medium mb-6">
                <span :class="{'text-primary-light': currentStep >= 1, 'text-base-white/50': currentStep < 1}">1. Guest & Contact</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 2, 'bg-base-white/30': currentStep < 2}"></div>
                <span :class="{'text-primary-light': currentStep >= 2, 'text-base-white/50': currentStep < 2}">2. Stay Details</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 3, 'bg-base-white/30': currentStep < 3}"></div>
                <span :class="{'text-primary-light': currentStep >= 3, 'text-base-white/50': currentStep < 3}">3. Policies & Sign</span>
            </div>

            <div x-show="!isSubmitting">
                
                @include('frontdeskcrm::registrations._guest_details_emergency')
                @include('frontdeskcrm::registrations._booking_details') {{-- Agent section hidden by conditional in partial --}}
                @include('frontdeskcrm::registrations._policy_signature')

                {{-- NAVIGATION BUTTONS --}}
                <div class="flex justify-between mt-6 pt-4 border-t border-glass-border">
                    <button 
                        type="button" 
                        x-show="currentStep > 1" 
                        @click.prevent="prevStep()" 
                        class="px-6 py-3 bg-primary-dark/50 text-base-white rounded-lg hover:bg-primary-dark transition-colors font-semibold shadow-lg"
                    >
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Previous
                    </button>

                    <div x-show="currentStep < 3" class="ml-auto">
                        <button 
                            type="button" 
                            @click.prevent="nextStep($event)" 
                            class="px-6 py-3 bg-primary-medium text-base-white rounded-lg hover:bg-primary-dark disabled:opacity-50 transition-colors font-semibold shadow-lg"
                        >
                            Next Step
                            <svg class="w-4 h-4 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>

                    {{-- SUBMIT BUTTON: GUEST DRAFT --}}
                    <div x-show="currentStep === 3" class="ml-auto">
                        <button 
                            type="submit" 
                            @click="$refs.signatureInput.value = signaturePad.toDataURL(); isSubmitting = true;"
                            x-bind:disabled="!formData.agreed_to_policies || (signaturePad && signaturePad.isEmpty()) || isSubmitting"
                            class="px-6 py-3 text-base-white rounded-lg disabled:opacity-50 transition-colors font-semibold shadow-lg flex items-center justify-center bg-primary-dark hover:bg-primary-medium"
                        >
                            <span x-show="!isSubmitting">Submit Draft to Front Desk</span>
                            <span x-show="isSubmitting">Submitting...</span>
                            <div x-show="isSubmitting" class="ml-2 animate-spin rounded-full h-4 w-4 border-b-2 border-base-white"></div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    .form-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid rgba(154, 166, 178, 0.5); 
        border-radius: 0.375rem;
        background-color: rgba(248, 250, 252, 0.9); /* Base White with transparency */
        color: #050506; 
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
</style>
@endsection
