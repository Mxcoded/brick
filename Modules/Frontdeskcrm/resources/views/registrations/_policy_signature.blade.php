{{-- File: _policy_signature.blade.php (Partial) --}}
<div x-show="currentStep === 3" x-transition:enter.duration.500ms>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Policy --}}
        <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Policy Consent
            </h2>
            <div class="bg-primary-light/10 border border-primary-light/50 rounded-lg p-3 text-base-white text-sm mb-3">
                <h3 class="font-semibold text-base mb-1">Key Policies:</h3>
                <p class="text-xs">No smoking in rooms. Checkout strictly 11AM. Staff will confirm final rates.</p>
            </div>
            <label class="flex items-center text-sm font-medium">
                <input type="checkbox" name="agreed_to_policies" required x-model="formData.agreed_to_policies" class="mr-2 rounded text-red-400 focus:ring-red-400">
                <span>I agree to all policies <span class="text-red-400 font-bold">*</span></span>
            </label>
            <div class="mt-3">
                <label class="flex items-center text-sm font-medium">
                    <input type="checkbox" name="opt_in_data_save" id="opt_in" value="1" x-model="formData.opt_in_data_save" class="mr-2 rounded text-primary-light focus:ring-primary-light">
                    <span>Opt-in: Save my details for faster future check-ins.</span>
                </label>
            </div>
        </div>

        {{-- Signature --}}
        <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Guest Signature <span class="text-red-400">*</span>
            </h2>
            <canvas 
                id="signature-pad" 
                width="400" 
                height="150" 
                class="w-full bg-base-white rounded-lg border-2 border-primary-medium shadow-inner"
            ></canvas>
            <input type="hidden" name="guest_signature" id="signature-input" x-ref="signatureInput" x-bind:required="currentStep === 3">
            <button type="button" @click.prevent="clearSignature()" class="mt-3 px-4 py-2 bg-primary-dark/80 text-base-white rounded-lg hover:bg-primary-dark transition-colors text-sm font-medium">
                Clear Signature
            </button>
        </div>
    </div>
</div>