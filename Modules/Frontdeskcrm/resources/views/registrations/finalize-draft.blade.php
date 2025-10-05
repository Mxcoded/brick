{{-- File: finalize-draft.blade.php --}}
@extends('frontdeskcrm::layouts.master')

@section('title', 'Finalize Guest Registration Draft')

@section('page-content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-glass-bg backdrop-blur-md rounded-xl border border-glass-border shadow-2xl overflow-hidden text-base-white">
        
        <div class="bg-gradient-to-r from-green-600 to-indigo-700 px-6 py-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-base-white">Finalize Guest Draft #{{ $registration->id }}</h1>
            <p class="text-primary-light text-sm">Guest **{{ $registration->full_name }}** has submitted their details. Complete the booking information below. Existing signature from draft is shown.</p>
        </div>
        
        <form 
            x-data="formHandler({{ json_encode($oldData ?? []) }}, false)" 
            method="POST" 
            action="{{ route('frontdesk.registrations.finish-draft.store', $registration) }}"
            class="p-6 space-y-6 bg-black/50" 
            x-ref="finalizeForm"
        >
            @csrf
            
            <input type="hidden" name="guest_id" x-model="selectedGuestId">
            <input type="hidden" name="is_guest_draft" value="0"> 
            <input type="hidden" name="guest_signature" value="{{ $registration->guest_signature ?? '' }}">
            
            {{-- STEPPER NAVIGATION --}}
            <div class="flex justify-between items-center text-sm font-medium mb-6">
                <span :class="{'text-primary-light': currentStep >= 1, 'text-base-white/50': currentStep < 1}">1. Guest & Contact</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 2, 'bg-base-white/30': currentStep < 2}"></div>
                <span :class="{'text-primary-light': currentStep >= 2, 'text-base-white/50': currentStep < 2}">2. Stay Details</span>
                <div class="h-0.5 flex-1 mx-2 transition-colors duration-500" :class="{'bg-primary-light': currentStep >= 3, 'bg-base-white/30': currentStep < 3}"></div>
                <span :class="{'text-primary-light': currentStep >= 3, 'text-base-white/50': currentStep < 3}">3. Review & Confirm</span>
            </div>

            {{-- Form Content --}}
            <div x-show="!isSubmitting" x-transition:leave="transition ease-in-out duration-300">
                {{-- Step 1: Guest & Contact --}}
                <div x-show="currentStep === 1">
                    @include('frontdeskcrm::registrations._guest_details_emergency')
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

                {{-- Step 2: Stay Details --}}
                <div x-show="currentStep === 2">
                    @include('frontdeskcrm::registrations._booking_details')
                    <div class="flex justify-between mt-4">
                        <button 
                            type="button" 
                            @click.prevent="prevStep()" 
                            class="px-6 py-3 bg-gray-600 text-base-white rounded-lg hover:bg-gray-700 transition-colors font-semibold shadow-lg"
                        >
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Previous
                        </button>
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

                {{-- Step 3: Review & Confirm --}}
                <div x-show="currentStep === 3">
                    <div class="space-y-6">
                        {{-- Existing Signature --}}
                        <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
                            <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Guest Signature (From Draft)
                            </h2>
                            @if($registration->guest_signature)
                                <img src="{{ Storage::url($registration->guest_signature) }}" alt="Guest Signature" class="max-w-full h-32 bg-base-white rounded-lg border-2 border-primary-medium shadow-inner mx-auto block">
                                <p class="text-sm text-primary-light text-center mt-2">Signature confirmed. <button type="button" @click="showReSign = !showReSign" class="text-yellow-300 underline">Re-sign if needed</button></p>
                                <div x-show="showReSign" class="mt-3">
                                    <canvas id="re-signature-pad" class="w-full h-32 bg-base-white rounded-lg border-2 border-primary-medium shadow-inner"></canvas>
                                    <input type="hidden" name="new_guest_signature" id="re-signature-input">
                                    <button type="button" @click="clearReSignature()" class="mt-2 px-3 py-1 bg-red-600 text-white rounded text-sm">Clear</button>
                                </div>
                            @else
                                @include('frontdeskcrm::registrations._policy_signature')
                            @endif
                        </div>

                        {{-- Policy (Pre-checked) --}}
                        <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
                            <h3 class="text-lg font-semibold mb-3">Policy Confirmation</h3>
                            <label class="flex items-center">
                                <input type="checkbox" name="agreed_to_policies" x-model="formData.agreed_to_policies" checked required class="mr-2 rounded text-red-400 focus:ring-red-400">
                                <span class="text-sm">Guest has agreed to all policies <span class="text-red-400">*</span></span>
                            </label>
                            <div class="mt-3">
                                <label class="flex items-center text-sm font-medium">
                                    <input type="checkbox" name="opt_in_data_save" id="opt_in" value="1" x-model="formData.opt_in_data_save" class="mr-2 rounded text-primary-light focus:ring-primary-light">
                                    <span>Opt-in: Save details for faster future check-ins.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button 
                            type="button" 
                            @click.prevent="prevStep()" 
                            class="px-6 py-3 bg-gray-600 text-base-white rounded-lg hover:bg-gray-700 transition-colors font-semibold shadow-lg"
                        >
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Previous
                        </button>
                        <div class="flex space-x-3">
                            <button 
                                type="button" 
                                @click.prevent="window.open('{{ route('frontdesk.registrations.print', $registration) }}', '_blank')"
                                class="px-6 py-3 bg-yellow-600 text-base-white rounded-lg hover:bg-yellow-700 transition-colors font-semibold shadow-lg"
                            >
                                Print Draft
                            </button>
                            <button 
                                type="button" 
                                @click.prevent="handleFinalizeSubmit($event)"
                                x-bind:disabled="isSubmitting"
                                class="px-6 py-3 bg-green-600 text-base-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors font-semibold shadow-lg flex items-center justify-center"
                            >
                                <span x-show="!isSubmitting">Finalize & Check-In</span>
                                <span x-show="isSubmitting">Finalizing...</span>
                                <div x-show="isSubmitting" class="ml-2 animate-spin rounded-full h-4 w-4 border-b-2 border-base-white"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loading Overlay --}}
            <div x-show="isSubmitting" x-transition:enter="transition ease-out duration-300" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-white/90 rounded-lg p-6 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-dark mx-auto mb-4"></div>
                    <p class="text-primary-dark">Finalizing check-in...</p>
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