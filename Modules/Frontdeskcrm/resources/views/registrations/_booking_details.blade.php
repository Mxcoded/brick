{{-- File: _booking_details.blade.php (Partial - Now handles Stay & Group/Room Allocation) --}}
<div x-show="currentStep === 2" x-transition:enter.duration.500ms>
    <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
        <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Stay & Group Information
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

        {{-- Group Details - Agent Only --}}
        <div x-show="!isGuestDraft" class="mt-6 pt-4 border-t border-glass-border">
            <h3 class="text-lg font-semibold mb-3">Group Allocation</h3>
            <div class="md:col-span-3 flex items-center mt-2 mb-4">
                <input type="checkbox" name="is_group_lead" id="is_group_lead" x-model="formData.is_group_lead" class="rounded text-primary-medium focus:ring-primary-medium">
                <label for="is_group_lead" class="ml-2 text-sm font-medium">This guest is the **Group Lead**?</label>
            </div>
            
            <div x-show="formData.is_group_lead" x-transition class="p-4 bg-white/20 rounded-lg border border-glass-border">
                <h4 class="text-base font-medium mb-3 border-b border-glass-border pb-2">Group Members & Room Assignment</h4>
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
        
        {{-- GUEST NOTE: Let the guest know booking is done later --}}
        <p x-show="isGuestDraft" class="text-sm font-medium mt-4 text-primary-light">Booking Source, Rate, and Room Allocation will be handled by the front desk agent.</p>
    </div>
</div>
