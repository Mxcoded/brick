{{-- File: agent-checkin.blade.php (Agent Full Check-In with Search & Group) --}}
@extends('frontdeskcrm::layouts.master')

@section('title', 'New Agent-Led Registration')

@section('page-content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-glass-bg backdrop-blur-md rounded-xl border border-glass-border shadow-2xl overflow-hidden text-base-white">
        
        <div class="bg-gradient-to-r from-green-600 to-indigo-700 px-6 py-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-base-white">Front Desk: New Guest Check-In</h1>
            <p class="text-primary-light text-sm">Search for returning guests or enter new data. Complete all booking details for immediate check-in.</p>
        </div>
        
        <form 
            x-data="formHandler({{ json_encode($oldData ?? []) }}, false)" 
            method="POST" 
            action="{{ route('frontdesk.registrations.store') }}" 
            class="p-6 space-y-6 bg-black/50" 
        >
            @csrf
            
            <input type="hidden" name="guest_id" x-model="selectedGuestId">
            <input type="hidden" name="is_guest_draft" value="0"> 
            
            {{-- STEPPER NAVIGATION --}}
            <div class="flex justify-between items-center text-sm font-medium mb-6">
                <span :class="{'text-primary-light': currentStep >= 1, 'text-base-white/50': currentStep < 1}">1. Search & Guest</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 2, 'bg-base-white/30': currentStep < 2}"></div>
                <span :class="{'text-primary-light': currentStep >= 2, 'text-base-white/50': currentStep < 2}">2. Stay & Booking</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 3, 'bg-base-white/30': currentStep < 3}"></div>
                <span :class="{'text-primary-light': currentStep >= 3, 'text-base-white/50': currentStep < 3}">3. Policies & Sign</span>
            </div>

            <div x-show="!isSubmitting">
                
                {{-- Step 1: Search & Guest Details --}}
                <div x-show="currentStep === 1">
                    {{-- Search Bar --}}
                    <div class="bg-white/10 rounded-lg p-4 border border-glass-border mb-6">
                        <h3 class="text-xl font-semibold mb-3">Returning Guest Search</h3>
                        <div class="flex">
                            <input type="text" x-model="searchQuery" @keyup.enter.prevent="performSearch" class="flex-1 form-input" placeholder="Email, Phone, or Name">
                            <button @click.prevent="performSearch" type="button" x-bind:disabled="loading" class="bg-primary-dark hover:bg-primary-medium px-4 py-2 ml-2 rounded text-base-white font-semibold disabled:opacity-50">
                                Search
                            </button>
                        </div>
                        <div x-show="loading" class="mt-2 text-sm text-primary-light">Searching...</div>
                        <div x-show="searchResults.length" class="mt-3 space-y-2 max-h-40 overflow-y-auto">
                            <template x-for="guest in searchResults" :key="guest.id">
                                <div class="p-3 bg-white/20 rounded-lg cursor-pointer hover:bg-white/30 transition-colors" @click="selectGuest(guest)">
                                    <p class="font-medium" x-text="guest.full_name"></p>
                                    <p class="text-sm text-primary-light" x-text="guest.email || guest.contact_number"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    @include('frontdeskcrm::registrations.partials._guest_details_emergency')

                    <div class="flex justify-end mt-4">
                        <button 
                            type="button" 
                            @click.prevent="nextStep($event)" 
                            class="px-6 py-3 bg-primary-medium text-base-white rounded-lg hover:bg-primary-dark transition-colors font-semibold shadow-lg"
                        >
                            Next Step
                            <svg class="w-4 h-4 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- Step 2: Stay & Booking --}}
                <div x-show="currentStep === 2">
                    @include('frontdeskcrm::registrations.partials._booking_details')
                    <div class="flex justify-between mt-4">
                        <button 
                            type="button" 
                            @click.prevent="prevStep()" 
                            class="px-6 py-3 bg-gray-600 text-base-white rounded-lg hover:bg-gray-700 transition-colors font-semibold shadow-lg"
                        >
                            Previous
                        </button>
                        <button 
                            type="button" 
                            @click.prevent="nextStep($event)" 
                            class="px-6 py-3 bg-primary-medium text-base-white rounded-lg hover:bg-primary-dark transition-colors font-semibold shadow-lg"
                        >
                            Next Step
                        </button>
                    </div>
                </div>

                {{-- Step 3: Policies & Sign --}}
                <div x-show="currentStep === 3">
                    @include('frontdeskcrm::registrations.partials._policy_signature')
                    <div class="flex justify-between mt-4">
                        <button 
                            type="button" 
                            @click.prevent="prevStep()" 
                            class="px-6 py-3 bg-gray-600 text-base-white rounded-lg hover:bg-gray-700 transition-colors font-semibold shadow-lg"
                        >
                            Previous
                        </button>
                        <button 
                            type="submit" 
                            @click="$refs.signatureInput.value = signaturePad.toDataURL(); isSubmitting = true;"
                            x-bind:disabled="!formData.agreed_to_policies || (signaturePad && signaturePad.isEmpty()) || isSubmitting"
                            class="px-6 py-3 text-base-white rounded-lg disabled:opacity-50 transition-colors font-semibold shadow-lg flex items-center justify-center bg-green-600 hover:bg-green-700"
                        >
                            <span x-show="!isSubmitting">Complete Check-In & Print</span>
                            <span x-show="isSubmitting">Processing...</span>
                            <div x-show="isSubmitting" class="ml-2 animate-spin rounded-full h-4 w-4 border-b-2 border-base-white"></div>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Loading Overlay --}}
            <div x-show="isSubmitting" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-white/90 rounded-lg p-6 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-dark mx-auto mb-4"></div>
                    <p class="text-primary-dark">Processing check-in...</p>
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
        background-color: rgba(248, 250, 252, 0.9);
        color: #050506; 
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
</style>
@endsection