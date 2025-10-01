{{-- File: _booking_details.blade.php (Partial) --}}
<div x-show="currentStep === 2" x-transition:enter.duration.500ms>
    <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
        <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Stay Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Check-In Date <span class="text-red-400">*</span></label>
                <input type="date" name="check_in" x-model="formData.check_in" required min="{{ now()->format('Y-m-d') }}" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Check-Out Date <span class="text-red-400">*</span></label>
                <input type="date" name="check_out" x-model="formData.check_out" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Number of Guests <span class="text-red-400">*</span></label>
                <input type="number" name="no_of_guests" x-model="formData.no_of_guests" min="1" max="10" required class="form-input">
            </div>
        </div>

        {{-- FRONT DESK ONLY: Booking Details (Only visible if not a guest draft) --}}
        <div x-show="!isGuestDraft" class="mt-6 pt-4 border-t border-glass-border">
            <h3 class="text-lg font-semibold mb-3">Front Desk: Final Booking Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Room Type <span class="text-red-400">*</span></label>
                    <input type="text" name="room_type" x-model="formData.room_type" :required="!isGuestDraft" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Rate/Night (₦) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="room_rate" x-model="formData.room_rate" :required="!isGuestDraft" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method <span class="text-red-400">*</span></label>
                    <select name="payment_method" x-model="formData.payment_method" :required="!isGuestDraft" class="form-input">
                        <option value="">Select</option>
                        <option value="cash">Cash</option>
                        <option value="pos">POS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Booking Source <span class="text-red-400">*</span></label>
                    <select name="booking_source_id" x-model="formData.booking_source_id" :required="!isGuestDraft" class="form-input">
                        <option value="">Select</option>
                        @foreach($bookingSources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Guest Type <span class="text-red-400">*</span></label>
                    <select name="guest_type_id" x-model="formData.guest_type_id" :required="!isGuestDraft" class="form-input">
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

                <div class="md:col-span-3 flex items-center mt-2">
                    <input type="checkbox" name="is_group_lead" id="is_group_lead" x-model="formData.is_group_lead" class="rounded text-primary-medium focus:ring-primary-medium">
                    <label for="is_group_lead" class="ml-2 text-sm font-medium">This guest is the **Group Lead**?</label>
                </div>
                {{-- Group member logic here --}}
                <div x-show="formData.is_group_lead" x-transition class="md:col-span-3 mt-4 p-4 bg-white/20 rounded-lg border border-glass-border">
                    <h3 class="text-lg font-medium mb-3 border-b border-glass-border pb-2">Group Members</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <template x-for="(member, index) in groupMembers" :key="index">
                            <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 bg-white/50 p-3 rounded-lg">
                                <input type="text" x-bind:name="'group_members[' + index + '][full_name]'" placeholder="Name (Required)" class="flex-1 form-input" x-model="member.full_name" :required="formData.is_group_lead">
                                <input type="tel" x-bind:name="'group_members[' + index + '][contact_number]'" placeholder="Contact (Required)" class="flex-1 form-input" x-model="member.contact_number" :required="formData.is_group_lead">
                                <input type="text" x-bind:name="'group_members[' + index + '][room_assignment]'" placeholder="Room No. (Optional)" class="flex-1 form-input" x-model="member.room_assignment">
                                <button type="button" @click.prevent="removeGroupMember(index)" class="px-3 py-2 bg-red-600 text-white rounded text-sm font-medium hover:bg-red-700 transition-colors sm:w-auto">Remove</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click.prevent="addGroupMember()" class="mt-3 text-primary-light hover:text-base-white text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Member
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>