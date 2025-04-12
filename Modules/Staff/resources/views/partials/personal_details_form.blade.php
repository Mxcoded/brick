<!-- Assuming Bootstrap 5 is used -->
<div class="container">

    <!-- Personal Details Section -->
    <h4 class="mb-3">Personal Details</h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="name" class="form-label">Name <small class="text-muted"><i>(Surname
                            first)</i></small></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Doe John"
                    value="{{ old('name', $employee->name ?? '') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control"
                    placeholder="e.g. john.doe@example.com" value="{{ old('email', $employee->email ?? '') }}" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="place_of_birth" class="form-label">Place of Birth</label>
                <input type="text" name="place_of_birth" id="place_of_birth" class="form-control"
                    placeholder="e.g. Lagos" value="{{ old('place_of_birth', $employee->place_of_birth ?? '') }}"
                    required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="state_of_origin" class="form-label">State of Origin</label>
                <input type="text" name="state_of_origin" id="state_of_origin" class="form-control"
                    placeholder="e.g. Lagos State"
                    value="{{ old('state_of_origin', $employee->state_of_origin ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="lga" class="form-label">Local Government Area (LGA)</label>
                <input type="text" name="lga" id="lga" class="form-control" placeholder="e.g. Ikeja"
                    value="{{ old('lga', $employee->lga ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="nationality" class="form-label">Nationality</label>
                <input type="text" name="nationality" id="nationality" class="form-control"
                    placeholder="e.g. Nigerian" value="{{ old('nationality', $employee->nationality ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="gender" class="form-label">Gender</label>
                <select name="gender" id="gender" class="form-select" required>
                    <option value="" disabled {{ !old('gender', $employee->gender ?? '') ? 'selected' : '' }}>
                        Select Gender</option>
                    <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male
                    </option>
                    <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>
                        Female</option>
                    <option value="Other" {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>
                        Other</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                    value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="marital_status" class="form-label">Marital Status</label>
                <select name="marital_status" id="marital_status" class="form-select" required>
                    <option value="" disabled
                        {{ !old('marital_status', $employee->marital_status ?? '') ? 'selected' : '' }}>Select Status
                    </option>
                    <option value="Single"
                        {{ old('marital_status', $employee->marital_status ?? '') == 'Single' ? 'selected' : '' }}>
                        Single</option>
                    <option value="Married"
                        {{ old('marital_status', $employee->marital_status ?? '') == 'Married' ? 'selected' : '' }}>
                        Married</option>
                    <option value="Divorced"
                        {{ old('marital_status', $employee->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>
                        Divorced</option>
                    <option value="Widowed"
                        {{ old('marital_status', $employee->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>
                        Widowed</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="blood_group" class="form-label">Blood Group</label>
                <input type="text" name="blood_group" id="blood_group" class="form-control"
                    placeholder="e.g. O+" value="{{ old('blood_group', $employee->blood_group ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="genotype" class="form-label">Genotype</label>
                <input type="text" name="genotype" id="genotype" class="form-control" placeholder="e.g. AA"
                    value="{{ old('genotype', $employee->genotype ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control"
                    placeholder="e.g. +2348012345678"
                    value="{{ old('phone_number', $employee->phone_number ?? '') }}" required>
                @error('phone_number')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="nin" class="form-label">NIN</label>
                <input type="text" name="nin" id="nin" class="form-control"
                    placeholder="e.g. 12345678901" value="{{ old('nin', $employee->nin ?? '') }}" required>
                @error('nin')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="bvn" class="form-label">BVN</label>
                <input type="text" name="bvn" id="bvn" class="form-control"
                    placeholder="e.g. 22334455667" value="{{ old('bvn', $employee->bvn ?? '') }}" required>
                @error('bvn')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="position" class="form-label">Position</label>
                <input type="text" name="position" id="position" class="form-control"
                    placeholder="e.g. Software Engineer" value="{{ old('position', $employee->position ?? '') }}"
                    required>
                @error('position')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label for="residential_address" class="form-label">Residential Address</label>
                <textarea name="residential_address" id="residential_address" class="form-control" rows="3"
                    placeholder="Enter full address" required>{{ old('residential_address', $employee->residential_address ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Next of Kin and ICE Contact Section -->
    <h4 class="mt-4 mb-3">Emergency Contacts</h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="next_of_kin_name" class="form-label">Next of Kin Name</label>
                <input type="text" name="next_of_kin_name" id="next_of_kin_name" class="form-control"
                    placeholder="e.g. Jane Doe"
                    value="{{ old('next_of_kin_name', $employee->next_of_kin_name ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="next_of_kin_phone" class="form-label">Next of Kin Phone</label>
                <input type="text" name="next_of_kin_phone" id="next_of_kin_phone" class="form-control"
                    placeholder="e.g. +2348012345678"
                    value="{{ old('next_of_kin_phone', $employee->next_of_kin_phone ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="ice_contact_name" class="form-label">ICE Contact Name</label>
                <input type="text" name="ice_contact_name" id="ice_contact_name" class="form-control"
                    placeholder="e.g. John Smith"
                    value="{{ old('ice_contact_name', $employee->ice_contact_name ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="ice_contact_phone" class="form-label">ICE Contact Phone</label>
                <input type="text" name="ice_contact_phone" id="ice_contact_phone" class="form-control"
                    placeholder="e.g. +2348012345678"
                    value="{{ old('ice_contact_phone', $employee->ice_contact_phone ?? '') }}" required>
            </div>
        </div>
    </div>

    <!-- Employment Details Section -->
    <h4 class="mt-4 mb-3">Employment Details</h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                    value="{{ old('start_date', $employee->start_date ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="end_date" class="form-label">End Date <small class="text-muted">(Leave blank if
                        active)</small></label>
                <input type="date" name="end_date" id="end_date" class="form-control"
                    value="{{ old('end_date', $employee->end_date ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="branch_name" class="form-label">Branch Name</label>
                <input type="text" name="branch_name" id="branch_name" class="form-control"
                    placeholder="e.g. Lagos Branch" value="{{ old('branch_name', $employee->branch_name ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="leaving_reason" class="form-label">Reason for Leaving</label>
                <select name="leaving_reason" id="leaving_reason" class="form-select">
                    <option value=""
                        {{ !old('leaving_reason', $employee->leaving_reason ?? '') ? 'selected' : '' }}>None</option>
                    <option value="resignation"
                        {{ old('leaving_reason', $employee->leaving_reason ?? '') == 'resignation' ? 'selected' : '' }}>
                        Resignation</option>
                    <option value="sack"
                        {{ old('leaving_reason', $employee->leaving_reason ?? '') == 'sack' ? 'selected' : '' }}>Sack
                    </option>
                    <option value="transfer"
                        {{ old('leaving_reason', $employee->leaving_reason ?? '') == 'transfer' ? 'selected' : '' }}>
                        Transfer</option>
                </select>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label for="note_for_leaving" class="form-label">Note for Leaving</label>
                <textarea name="note_for_leaving" id="note_for_leaving" class="form-control" rows="3"
                    placeholder="e.g. Reason for resignation or transfer details">{{ old('note_for_leaving', $employee->note_for_leaving ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- File Uploads Section -->
    <h4 class="mt-4 mb-3">File Uploads</h4>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="profile_image" class="form-label">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" class="form-control"
                    accept="image/*">
                @if ($employee->profile_image ?? false)
                    <small class="text-muted">Current: <a href="{{ Storage::url($employee->profile_image) }}"
                            target="_blank">View</a></small>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="cv_path" class="form-label">Upload CV</label>
                <input type="file" name="cv_path" id="cv_path" class="form-control" accept=".pdf,.doc,.docx">
                @if ($employee->cv_path ?? false)
                    <small class="text-muted">Current: <a href="{{ Storage::url($employee->cv_path) }}"
                            target="_blank">View</a></small>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="resignation_letter" class="form-label">Resignation Letter</label>
                <input type="file" name="resignation_letter" id="resignation_letter" class="form-control"
                    accept=".pdf,.doc,.docx">
                @if ($employee->resignation_letter ?? false)
                    <small class="text-muted">Current: <a href="{{ Storage::url($employee->resignation_letter) }}"
                            target="_blank">View</a></small>
                @endif
            </div>
        </div>
    </div>


</div>

<!-- Optional CSS for further customization -->
<style>
    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 500;
    }

    .text-danger {
        font-size: 0.875rem;
    }

    h4 {
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 0.5rem;
    }
</style>
