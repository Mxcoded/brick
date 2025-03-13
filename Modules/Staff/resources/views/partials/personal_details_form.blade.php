<!-- Personal Details Form Fields -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $employee->name ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="place_of_birth">Place of Birth</label>
            <input type="text" name="place_of_birth" id="place_of_birth" class="form-control" value="{{ old('place_of_birth', $employee->place_of_birth ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="state_of_origin">State of Origin</label>
            <input type="text" name="state_of_origin" id="state_of_origin" class="form-control" value="{{ old('state_of_origin', $employee->state_of_origin ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="lga">Local Government Area (LGA)</label>
            <input type="text" name="lga" id="lga" class="form-control" value="{{ old('lga', $employee->lga ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="nationality">Nationality</label>
            <input type="text" name="nationality" id="nationality" class="form-control" value="{{ old('nationality', $employee->nationality ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select name="gender" id="gender" class="form-control" required>
                <option value="Male" {{ (old('gender', $employee->gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ (old('gender', $employee->gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ (old('gender', $employee->gender ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="marital_status">Marital Status</label>
            <select name="marital_status" id="marital_status" class="form-control" required>
                <option value="Single" {{ (old('marital_status', $employee->marital_status ?? '') == 'Single') ? 'selected' : '' }}>Single</option>
                <option value="Married" {{ (old('marital_status', $employee->marital_status ?? '') == 'Married') ? 'selected' : '' }}>Married</option>
                <option value="Divorced" {{ (old('marital_status', $employee->marital_status ?? '') == 'Divorced') ? 'selected' : '' }}>Divorced</option>
                <option value="Widowed" {{ (old('marital_status', $employee->marital_status ?? '') == 'Widowed') ? 'selected' : '' }}>Widowed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="blood_group">Blood Group</label>
            <input type="text" name="blood_group" id="blood_group" class="form-control" value="{{ old('blood_group', $employee->blood_group ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="genotype">Genotype</label>
            <input type="text" name="genotype" id="genotype" class="form-control" value="{{ old('genotype', $employee->genotype ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $employee->phone_number ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="residential_address">Residential Address</label>
            <textarea name="residential_address" id="residential_address" class="form-control" required>{{ old('residential_address', $employee->residential_address ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Next of Kin and ICE Contact -->
<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-group">
            <label for="next_of_kin_name">Next of Kin Name</label>
            <input type="text" name="next_of_kin_name" id="next_of_kin_name" class="form-control" value="{{ old('next_of_kin_name', $employee->next_of_kin_name ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="next_of_kin_phone">Next of Kin Phone</label>
            <input type="text" name="next_of_kin_phone" id="next_of_kin_phone" class="form-control" value="{{ old('next_of_kin_phone', $employee->next_of_kin_phone ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="ice_contact_name">ICE Contact Name</label>
            <input type="text" name="ice_contact_name" id="ice_contact_name" class="form-control" value="{{ old('ice_contact_name', $employee->ice_contact_name ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="ice_contact_phone">ICE Contact Phone</label>
            <input type="text" name="ice_contact_phone" id="ice_contact_phone" class="form-control" value="{{ old('ice_contact_phone', $employee->ice_contact_phone ?? '') }}" required>
        </div>
    </div>
</div>

<!-- File Uploads -->
<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-group">
            <label for="profile_image">Profile Image</label>
            <input type="file" name="profile_image" id="profile_image" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="cv_path">Upload CV</label>
            <input type="file" name="cv_path" id="cv_path" class="form-control">
        </div>
    </div>
</div>