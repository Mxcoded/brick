{{-- File: _guest_details_emergency.blade.php (Partial) --}}
<div x-show="currentStep === 1" x-transition:enter.duration.500ms class="space-y-6">
    <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
        <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Personal Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input type="text" name="title" x-model="formData.title" class="form-input" placeholder="Mr./Mrs.">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Full Name <span class="text-red-400">*</span></label>
                <input type="text" name="full_name" x-model="formData.full_name" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" x-model="formData.email" class="form-input" placeholder="guest@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contact <span class="text-red-400">*</span></label>
                <input type="tel" name="contact_number" x-model="formData.contact_number" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nationality</label>
                <input type="text" name="nationality" x-model="formData.nationality" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Birthday</label>
                <input type="date" name="birthday" x-model="formData.birthday" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Occupation</label>
                <input type="text" name="occupation" x-model="formData.occupation" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Company/Group</label>
                <input type="text" name="company_name" x-model="formData.company_name" class="form-input">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Home Address</label>
                <textarea name="home_address" x-model="formData.home_address" rows="2" class="form-input"></textarea>
            </div>
        </div>
    </div>
    
    <div class="bg-white/10 rounded-lg p-4 border border-glass-border">
        <h2 class="text-xl font-semibold mb-4 flex items-center border-b border-glass-border pb-2">
            <svg class="w-5 h-5 mr-2 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Emergency Contact
        </h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="emergency_name" x-model="formData.emergency_name" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Relationship</label>
                <input type="text" name="emergency_relationship" x-model="formData.emergency_relationship" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contact</label>
                <input type="tel" name="emergency_contact" x-model="formData.emergency_contact" class="form-input">
            </div>
        </div>
    </div>
</div>