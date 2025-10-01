@extends('frontdeskcrm::layouts.master')

@section('title', 'New Guest Registration')

@section('page-content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-glass-bg backdrop-blur-md rounded-xl border border-glass-border shadow-2xl overflow-hidden text-base-white">
        
        <div class="bg-gradient-to-r from-primary-dark to-primary-medium px-6 py-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-base-white" x-text="isGuestDraft ? 'Guest Check-In Draft' : 'New Front Desk Registration'"></h1>
            <p class="text-primary-light text-sm">Fill in the details to {{ request()->routeIs('frontdesk.registrations.create') ? 'create a new registration' : 'finalize a draft' }}.</p>
        </div>
        
        <form 
            x-data="formHandler({{ json_encode($oldData ?? []) }}, {{ Auth::check() ? 'false' : 'true' }})" 
            method="POST" 
            :action="isGuestDraft ? '{{ route('frontdesk.registrations.store') }}' : '{{ route('frontdesk.registrations.store') }}'" {{-- Logic to be refined if you use the same form for edit/finalize --}}
            class="p-6 space-y-6 bg-black/50" 
        >
            @csrf
            
            <input type="hidden" name="guest_id" x-model="selectedGuestId">
            <input type="hidden" name="is_guest_draft" :value="isGuestDraft ? '1' : '0'"> 
            
            {{-- Search Bar (Front Desk Only) --}}
            <div x-show="!isGuestDraft && currentStep === 1" class="bg-white/10 rounded-lg p-4 border border-glass-border mb-6">
                <h3 class="text-xl font-semibold mb-3">Returning Guest Search</h3>
                <div class="flex">
                    <input type="text" x-model="searchQuery" @keyup.enter.prevent="performSearch" class="flex-1 form-input" placeholder="Search by Email, Contact, or Name">
                    <button @click.prevent="performSearch" type="button" x-bind:disabled="loading" class="bg-primary-dark hover:bg-primary-medium px-4 py-2 ml-2 rounded text-base-white font-semibold disabled:opacity-50">
                        Search
                    </button>
                </div>
                <div x-show="loading" class="mt-2 text-sm text-primary-light">Searching...</div>
                <div x-show="searchResults.length" class="mt-3 space-y-2 max-h-40 overflow-y-auto">
                    <template x-for="guest in searchResults" :key="guest.id">
                        <div class="bg-white/20 rounded p-3 flex justify-between items-center border border-glass-border">
                            <div>
                                <p class="font-semibold text-base-white" x-text="guest.full_name"></p>
                                <p class="text-sm text-primary-light" x-text="guest.email || guest.contact_number"></p>
                            </div>
                            <button @click.prevent="selectGuest(guest); $nextTick(() => nextStep($event))" class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition-colors text-white font-medium">
                                Select & Pre-Fill
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- STEPPER NAVIGATION --}}
            <div class="flex justify-between items-center text-sm font-medium mb-6">
                <span :class="{'text-primary-light': currentStep >= 1, 'text-base-white/50': currentStep < 1}">1. Guest & Contact</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 2, 'bg-base-white/30': currentStep < 2}"></div>
                <span :class="{'text-primary-light': currentStep >= 2, 'text-base-white/50': currentStep < 2}">2. Stay Details</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 3, 'bg-base-white/30': currentStep < 3}"></div>
                <span :class="{'text-primary-light': currentStep >= 3, 'text-base-white/50': currentStep < 3}">3. Policies & Sign</span>
            </div>

            <div x-show="!isSubmitting">
                
                {{-- STEP 1: GUEST DETAILS --}}
                @include('frontdeskcrm::registrations._guest_details_emergency')

                {{-- STEP 2: BOOKING INFO & GROUP MEMBERS --}}
                @include('frontdeskcrm::registrations._booking_details')

                {{-- STEP 3: POLICY & SIGNATURE --}}
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

                    <div x-show="currentStep < 3" :class="{'ml-auto': currentStep === 1}">
                        <button 
                            type="button" 
                            @click.prevent="nextStep($event)" 
                            class="px-6 py-3 bg-primary-medium text-base-white rounded-lg hover:bg-primary-dark disabled:opacity-50 transition-colors font-semibold shadow-lg"
                        >
                            Next Step
                            <svg class="w-4 h-4 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>

                    {{-- SUBMIT BUTTON (Guest Draft or Final Front Desk Submit) --}}
                    <div x-show="currentStep === 3" class="ml-auto">
                        <button 
                            type="submit" 
                            @click="$refs.signatureInput.value = signaturePad.toDataURL(); isSubmitting = true;"
                            x-bind:disabled="!formData.agreed_to_policies || (signaturePad && signaturePad.isEmpty()) || isSubmitting"
                            class="px-6 py-3 text-base-white rounded-lg disabled:opacity-50 transition-colors font-semibold shadow-lg flex items-center justify-center"
                            :class="isGuestDraft ? 'bg-primary-dark hover:bg-primary-medium' : 'bg-green-600 hover:bg-green-700'"
                        >
                            <span x-show="!isSubmitting" x-text="isGuestDraft ? 'Submit Draft to Front Desk' : 'Finalize Check-In'"></span>
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