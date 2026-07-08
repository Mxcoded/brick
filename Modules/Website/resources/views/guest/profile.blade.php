@extends('website::layouts.guest')

@section('title', 'My Profile')

@section('content')
<div class="container py-5">
    <div class="row g-4">
        
        @include('website::guest.partials.sidebar', ['active' => 'profile'])

        {{-- FORM --}}
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                
                {{-- HEADER WITH TOGGLE --}}
                <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user-circle me-2"></i>My Profile</h5>
                    
                    {{-- EDIT TOGGLE SWITCH --}}
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="editModeToggle" style="cursor: pointer;">
                        <label class="form-check-label fw-bold text-muted small ms-1" for="editModeToggle">Edit Profile</label>
                    </div>
                </div>
                
                <div class="card-body p-4 view-mode" id="profileCardBody">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('guest.profile.update') }}" method="POST" id="profileForm">
                        @csrf
                        @method('PUT')

                        {{-- 1. PERSONAL DETAILS --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">Personal Details</h6>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">Title</label>
                                    <select name="title" class="form-select profile-input" disabled>
                                        <option value="">--</option>
                                        @foreach(['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.'] as $t)
                                            <option value="{{ $t }}" {{ (old('title', $profile->title ?? '') == $t) ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">Full Name <span class="text-danger edit-indicator d-none">*</span></label>
                                    <input type="text" name="full_name" class="form-control profile-input" value="{{ old('full_name', $user->name) }}" required disabled>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">Email <span class="text-muted fst-italic ms-1">(Cannot change)</span></label>
                                    {{-- Email is always readonly/disabled --}}
                                    <input type="email" name="email" class="form-control bg-light border-0" value="{{ old('email', $user->email) }}" readonly>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Gender</label>
                                    <select name="gender" class="form-select profile-input" disabled>
                                        <option value="">Select</option>
                                        <option value="Male" {{ (old('gender', $profile->gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ (old('gender', $profile->gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Date of Birth</label>
                                    <input type="date" name="birthday" class="form-control profile-input" value="{{ old('birthday', optional($profile->birthday ?? null)->format('Y-m-d')) }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Nationality</label>
                                    <select name="nationality" class="form-select profile-input" disabled>
                                        <option value="">Select</option>
                                        <option value="Nigeria" {{ (old('nationality', $profile->nationality ?? '') == 'Nigeria') ? 'selected' : '' }}>Nigeria</option>
                                        <option value="Ghana" {{ (old('nationality', $profile->nationality ?? '') == 'Ghana') ? 'selected' : '' }}>Ghana</option>
                                        <option value="USA" {{ (old('nationality', $profile->nationality ?? '') == 'USA') ? 'selected' : '' }}>USA</option>
                                        <option value="Other" {{ (old('nationality', $profile->nationality ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 2. WORK & OCCUPATION --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">Employment</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Occupation</label>
                                    <input type="text" name="occupation" class="form-control profile-input" value="{{ old('occupation', $profile->occupation ?? '') }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Company Name</label>
                                    <input type="text" name="company_name" class="form-control profile-input" value="{{ old('company_name', $profile->company_name ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- 3. CONTACT & ADDRESS --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">Contact Info</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Phone Number</label>
                                    <input type="tel" name="contact_number" class="form-control profile-input" value="{{ old('contact_number', $profile->contact_number ?? '') }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Home Address</label>
                                    <input type="text" name="home_address" class="form-control profile-input" value="{{ old('home_address', $profile->home_address ?? '') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">City</label>
                                    <input type="text" name="city" class="form-control profile-input" value="{{ old('city', $profile->city ?? '') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">State</label>
                                    <input type="text" name="state" class="form-control profile-input" value="{{ old('state', $profile->state ?? '') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Zip Code</label>
                                    <input type="text" name="zip_code" class="form-control profile-input" value="{{ old('zip_code', $profile->zip_code ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- 4. EMERGENCY CONTACT --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">Emergency Contact</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Contact Name</label>
                                    <input type="text" name="emergency_name" class="form-control profile-input" value="{{ old('emergency_name', $profile->emergency_name ?? '') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Relationship</label>
                                    <input type="text" name="emergency_relationship" class="form-control profile-input" placeholder="e.g. Spouse" value="{{ old('emergency_relationship', $profile->emergency_relationship ?? '') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Emergency Phone</label>
                                    <input type="tel" name="emergency_contact" class="form-control profile-input" value="{{ old('emergency_contact', $profile->emergency_contact ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- 5. IDENTIFICATION --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">Identification</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">ID Type</label>
                                    <select name="identification_type" class="form-select profile-input" disabled>
                                        <option value="">Select ID</option>
                                        <option value="Passport" {{ (old('identification_type', $profile->identification_type ?? '') == 'Passport') ? 'selected' : '' }}>International Passport</option>
                                        <option value="NIN" {{ (old('identification_type', $profile->identification_type ?? '') == 'NIN') ? 'selected' : '' }}>National ID (NIN)</option>
                                        <option value="Drivers License" {{ (old('identification_type', $profile->identification_type ?? '') == 'Drivers License') ? 'selected' : '' }}>Drivers License</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">ID Number</label>
                                    <input type="text" name="identification_number" class="form-control profile-input" value="{{ old('identification_number', $profile->identification_number ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3">
                            <button type="submit" id="saveBtn" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-pill d-none animate__animated animate__fadeIn">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* "Illusion" of Read-Only Text */
    .view-mode .profile-input:disabled {
        background-color: transparent !important;
        border: none !important;
        padding-left: 0 !important;
        padding-top: 0 !important;
        color: #333 !important;
        font-weight: 500;
        box-shadow: none !important;
        cursor: text;
    }

    /* Hide select arrow in view mode */
    .view-mode select.profile-input:disabled {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
        padding-right: 0;
    }
    
    /* Slight animation for transition */
    .form-control, .form-select {
        transition: all 0.3s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('editModeToggle');
        const saveBtn = document.getElementById('saveBtn');
        const inputs = document.querySelectorAll('.profile-input');
        const cardBody = document.getElementById('profileCardBody');
        const indicators = document.querySelectorAll('.edit-indicator');

        toggle.addEventListener('change', function() {
            const isEditing = this.checked;

            if (isEditing) {
                // SWITCH TO EDIT MODE
                cardBody.classList.remove('view-mode');
                saveBtn.classList.remove('d-none');
                
                inputs.forEach(input => {
                    input.removeAttribute('disabled');
                    input.classList.add('bg-white'); // Add explicit background
                });
                
                indicators.forEach(el => el.classList.remove('d-none'));
                
            } else {
                // SWITCH TO VIEW MODE (READ ONLY)
                cardBody.classList.add('view-mode');
                saveBtn.classList.add('d-none');
                
                inputs.forEach(input => {
                    input.setAttribute('disabled', 'disabled');
                    input.classList.remove('bg-white');
                });
                
                indicators.forEach(el => el.classList.add('d-none'));
            }
        });
    });
</script>
@endpush
@endsection