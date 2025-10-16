{{-- File: _financial_booking_details.blade.php (Partial - Step 3) --}}
<div x-show="currentStep === 3" x-transition:enter.duration.500ms>
    <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
        <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2h-1v2h2m-6-2a3 3 0 00-3 3v2a3 3 0 003 3h4a3 3 0 003-3v-2a3 3 0 00-3-3H6zm0 0V8m0 4h4"></path></svg>
            Booking & Financial Details
        </h2>
        
        <div x-show="!isGuestDraft" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Room Type <span class="text-red-400">*</span></label>
                <input type="text" name="room_type" x-model="formData.room_type" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Rate/Night (₦) <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="room_rate" x-model="formData.room_rate" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Payment Method <span class="text-red-400">*</span></label>
                <select name="payment_method" x-model="formData.payment_method" required class="form-input">
                    <option value="">Select</option>
                    <option value="cash">Cash</option>
                    <option value="pos">POS</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Booking Source <span class="text-red-400">*</span></label>
                <select name="booking_source_id" x-model="formData.booking_source_id" required class="form-input">
                    <option value="">Select</option>
                    @foreach($bookingSources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Guest Type <span class="text-red-400">*</span></label>
                <select name="guest_type_id" x-model="formData.guest_type_id" required class="form-input">
                    <option value="">Select</option>
                    @foreach($guestTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center pt-6">
                <input type="checkbox" name="bed_breakfast" id="bed_breakfast" x-model="formData.bed_breakfast" class="rounded text-primary-medium focus:ring-primary-medium">
                <label for="bed_breakfast" class="ml-2 text-sm">Bed & Breakfast Included</label>
            </div>
        </div>
        
        <p x-show="isGuestDraft" class="text-lg font-semibold text-red-400 text-center mt-6">
            Financial details are completed by the Front Desk Agent. Proceed to finalize.
        </p>
    </div>
</div>
