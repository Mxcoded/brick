@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.show', $employee->id) }}">{{ $employee->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('page-content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: rgba(200, 161, 101, 0.15);">
                <i class="fas fa-user-edit text-gold"></i>
            </span>
            <div>
                <h1 class="h3 mb-0 fw-bold">Edit Staff Record</h1>
                <p class="text-muted mb-0 small">
                    <i class="fas fa-user me-1"></i>{{ $employee->name }}
                    <span class="mx-2">|</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $employee->staff_code }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-4 border-danger mb-4 py-2">
            <i class="fas fa-exclamation-circle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.update', $employee->id) }}" method="POST" enctype="multipart/form-data" id="staffForm">
        @csrf
        @method('PUT')

        {{-- ==================== SECTION 1: PERSONAL DETAILS ==================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" style="width: 36px; height: 36px; background: rgba(200, 161, 101, 0.15);">
                        <i class="fas fa-user text-gold" style="font-size: 0.9rem;"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Personal Details</h5>
                        <small class="text-muted">Basic information about the staff member</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Surname first e.g. Doe John" value="{{ old('name', $employee->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control" placeholder="staff@brickspoint.com" value="{{ old('email', $employee->email) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="+2348012345678" value="{{ old('phone_number', $employee->phone_number) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Gender <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-venus-mars text-muted"></i></span>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="" disabled>Select Gender</option>
                                <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $employee->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Date of Birth <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar text-muted"></i></span>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Place of Birth <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-pin text-muted"></i></span>
                            <input type="text" name="place_of_birth" id="place_of_birth" class="form-control" placeholder="e.g. Lagos" value="{{ old('place_of_birth', $employee->place_of_birth) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">State of Origin <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-marked-alt text-muted"></i></span>
                            <input type="text" name="state_of_origin" id="state_of_origin" class="form-control" placeholder="e.g. Lagos State" value="{{ old('state_of_origin', $employee->state_of_origin) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">LGA <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-location-dot text-muted"></i></span>
                            <input type="text" name="lga" id="lga" class="form-control" placeholder="e.g. Ikeja" value="{{ old('lga', $employee->lga) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Nationality <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-globe-africa text-muted"></i></span>
                            <input type="text" name="nationality" id="nationality" class="form-control" placeholder="e.g. Nigerian" value="{{ old('nationality', $employee->nationality) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Marital Status <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-ring text-muted"></i></span>
                            <select name="marital_status" id="marital_status" class="form-select" required>
                                <option value="" disabled>Select Status</option>
                                <option value="Single" {{ old('marital_status', $employee->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('marital_status', $employee->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Divorced" {{ old('marital_status', $employee->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="Widowed" {{ old('marital_status', $employee->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Blood Group <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-droplet text-muted"></i></span>
                            <input type="text" name="blood_group" id="blood_group" class="form-control" placeholder="O+" value="{{ old('blood_group', $employee->blood_group) }}" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Genotype <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-dna text-muted"></i></span>
                            <input type="text" name="genotype" id="genotype" class="form-control" placeholder="AA" value="{{ old('genotype', $employee->genotype) }}" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-muted">Residential Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-home text-muted"></i></span>
                            <textarea name="residential_address" id="residential_address" class="form-control" rows="2" placeholder="Enter full residential address" required>{{ old('residential_address', $employee->residential_address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- NIN & BVN --}}
                <hr class="my-4">
                <h6 class="fw-bold mb-3 small text-uppercase text-secondary"><i class="fas fa-id-card me-2"></i>Identification</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">NIN <span class="text-danger">*</span></label>
                        <input type="text" name="nin" id="nin" class="form-control" placeholder="11-digit NIN" value="{{ old('nin', $employee->nin) }}" maxlength="11" required>
                        <div class="form-text">National Identification Number</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">BVN <span class="text-danger">*</span></label>
                        <input type="text" name="bvn" id="bvn" class="form-control" placeholder="11-digit BVN" value="{{ old('bvn', $employee->bvn) }}" maxlength="11" required>
                        <div class="form-text">Bank Verification Number</div>
                    </div>
                </div>

                {{-- Staff Code (read-only) --}}
                <hr class="my-4">
                <h6 class="fw-bold mb-3 small text-uppercase text-secondary"><i class="fas fa-id-badge me-2"></i>Staff Identification</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Staff Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control bg-light" value="{{ $employee->staff_code }}" readonly>
                        </div>
                        <div class="form-text">Auto-generated, cannot be changed</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Status</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-circle text-muted"></i></span>
                            <input type="text" class="form-control bg-light" value="{{ ucfirst($employee->status) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== SECTION 2: EMPLOYMENT & EMERGENCY ==================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" style="width: 36px; height: 36px; background: rgba(200, 161, 101, 0.15);">
                        <i class="fas fa-briefcase text-gold" style="font-size: 0.9rem;"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Employment &amp; Emergency Contacts</h5>
                        <small class="text-muted">Job details, branch, and emergency contact information</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 small text-uppercase text-secondary"><i class="fas fa-briefcase me-2"></i>Employment Details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Position <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                            <input type="text" name="position" id="position" class="form-control" placeholder="e.g. Software Engineer" value="{{ old('position', $employee->position) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Department <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                            <input type="text" name="department" id="department" class="form-control" placeholder="e.g. Engineering" value="{{ old('department', $employee->department) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Branch</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-code-branch text-muted"></i></span>
                            <input type="text" name="branch_name" id="branch_name" class="form-control" placeholder="e.g. Lagos Branch" value="{{ old('branch_name', $employee->branch_name) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Start Date <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-play text-muted"></i></span>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $employee->start_date ? \Carbon\Carbon::parse($employee->start_date)->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-stop text-muted"></i></span>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $employee->end_date ? \Carbon\Carbon::parse($employee->end_date)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-text">Leave blank if currently employed</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Reason for Leaving</label>
                        <select name="leaving_reason" id="leaving_reason" class="form-select">
                            <option value="" {{ !old('leaving_reason', $employee->leaving_reason) ? 'selected' : '' }}>— Still Active —</option>
                            <option value="resignation" {{ old('leaving_reason', $employee->leaving_reason) == 'resignation' ? 'selected' : '' }}>Resignation</option>
                            <option value="sack" {{ old('leaving_reason', $employee->leaving_reason) == 'sack' ? 'selected' : '' }}>Sack</option>
                            <option value="transfer" {{ old('leaving_reason', $employee->leaving_reason) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="absconded" {{ old('leaving_reason', $employee->leaving_reason) == 'absconded' ? 'selected' : '' }}>Absconded</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-muted">Note for Leaving</label>
                        <textarea name="note_for_leaving" id="note_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving or transfer details">{{ old('note_for_leaving', $employee->note_for_leaving) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3 small text-uppercase text-secondary"><i class="fas fa-phone-alt me-2"></i>Emergency Contacts</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Next of Kin Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-friends text-muted"></i></span>
                            <input type="text" name="next_of_kin_name" id="next_of_kin_name" class="form-control" placeholder="e.g. Jane Doe" value="{{ old('next_of_kin_name', $employee->next_of_kin_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Next of Kin Phone <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="next_of_kin_phone" id="next_of_kin_phone" class="form-control" placeholder="+2348012345678" value="{{ old('next_of_kin_phone', $employee->next_of_kin_phone) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">ICE Contact Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-muted"></i></span>
                            <input type="text" name="ice_contact_name" id="ice_contact_name" class="form-control" placeholder="e.g. John Smith" value="{{ old('ice_contact_name', $employee->ice_contact_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">ICE Contact Phone <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="ice_contact_phone" id="ice_contact_phone" class="form-control" placeholder="+2348012345678" value="{{ old('ice_contact_phone', $employee->ice_contact_phone) }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== SECTION 3: DOCUMENTS ==================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" style="width: 36px; height: 36px; background: rgba(200, 161, 101, 0.15);">
                        <i class="fas fa-file-upload text-gold" style="font-size: 0.9rem;"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Documents &amp; File Uploads</h5>
                        <small class="text-muted">Profile image, CV, resignation letter</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Profile Image</label>
                        <div class="file-upload-box text-center p-4 border rounded-3" style="border-style: dashed !important; border-color: #ccc !important;" onclick="document.getElementById('profile_image').click();">
                            @if($employee->profile_image)
                                <img src="{{ asset('storage/'.$employee->profile_image) }}" class="rounded-circle mb-2" style="width: 64px; height: 64px; object-fit: cover;">
                                <p class="small text-muted mb-1">Click to change photo</p>
                            @else
                                <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                                <p class="small text-muted mb-0">Click to upload photo</p>
                            @endif
                            <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/*" onchange="previewFile(this, 'profilePreview')">
                            <div id="profilePreview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Upload CV</label>
                        <div class="file-upload-box text-center p-4 border rounded-3" style="border-style: dashed !important; border-color: #ccc !important;" onclick="document.getElementById('cv_path').click();">
                            <i class="fas fa-file-pdf fa-2x text-muted mb-2"></i>
                            @if($employee->cv_path)
                                <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>CV uploaded</p>
                            @endif
                            <p class="small text-muted mb-0">Click to {{ $employee->cv_path ? 'replace' : 'upload' }} CV</p>
                            <input type="file" name="cv_path" id="cv_path" class="d-none" accept=".pdf,.doc,.docx" onchange="previewFile(this, 'cvPreview')">
                            <div id="cvPreview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Resignation Letter</label>
                        <div class="file-upload-box text-center p-4 border rounded-3" style="border-style: dashed !important; border-color: #ccc !important;" onclick="document.getElementById('resignation_letter').click();">
                            <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                            @if($employee->resignation_letter)
                                <p class="small text-success mb-1"><i class="fas fa-check-circle me-1"></i>Letter uploaded</p>
                            @endif
                            <p class="small text-muted mb-0">Click to {{ $employee->resignation_letter ? 'replace' : 'upload' }} letter</p>
                            <input type="file" name="resignation_letter" id="resignation_letter" class="d-none" accept=".pdf,.doc,.docx" onchange="previewFile(this, 'letterPreview')">
                            <div id="letterPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== SECTION 4: HISTORY ==================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" style="width: 36px; height: 36px; background: rgba(200, 161, 101, 0.15);">
                        <i class="fas fa-history text-gold" style="font-size: 0.9rem;"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Employment History</h5>
                        <small class="text-muted">Previous work experience</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div id="employment-history-container">
                    @forelse($employee->employmentHistories as $index => $history)
                        @include('staff::partials.employment_history_form', ['index' => $index, 'history' => $history])
                    @empty
                        @include('staff::partials.employment_history_form', ['index' => 0])
                    @endforelse
                </div>
                <button type="button" id="add-employment-history" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fas fa-plus me-1"></i> Add Another Employment History
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" style="width: 36px; height: 36px; background: rgba(200, 161, 101, 0.15);">
                        <i class="fas fa-graduation-cap text-gold" style="font-size: 0.9rem;"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Educational Background</h5>
                        <small class="text-muted">Academic qualifications and certificates</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div id="educational-background-container">
                    @forelse($employee->educationalBackgrounds as $index => $education)
                        @include('staff::partials.educational_background_form', ['index' => $index, 'education' => $education])
                    @empty
                        @include('staff::partials.educational_background_form', ['index' => 0])
                    @endforelse
                </div>
                <button type="button" id="add-educational-background" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fas fa-plus me-1"></i> Add Another Educational Background
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-3 shadow-sm mb-4 border">
            <div>
                <p class="fw-bold mb-0"><i class="fas fa-info-circle me-2 text-gold"></i>All fields marked with <span class="text-danger">*</span> are required.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" id="submitBtn">
                    <i class="fas fa-save me-2" id="submitIcon"></i>
                    <span id="submitText">Update Staff Record</span>
                    <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@section('styles')
<style>
    .card { border-radius: 12px; }
    .card-header { padding-left: 1.5rem; padding-right: 1.5rem; }
    .card-body { padding: 1.5rem; }
    .form-control, .form-select, .input-group-text { border-color: #e0e0e0; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .input-group-text { border-right: none; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    .input-group .form-control:focus, .input-group .form-select:focus { box-shadow: none; border-color: #C8A165; }
    .input-group:focus-within .input-group-text { border-color: #C8A165; }
    .file-upload-box { cursor: pointer; transition: all 0.2s ease; }
    .file-upload-box:hover { border-color: #C8A165 !important; background: rgba(200, 161, 101, 0.03); }
    .file-upload-box:active { transform: scale(0.98); }
    hr { opacity: 0.15; }
</style>
@endsection

@section('page-scripts')
<script src="{{ asset('js/staff/dynamic-forms.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('staffForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitIcon.classList.add('d-none');
        submitText.textContent = 'Updating Staff Record...';
        submitSpinner.classList.remove('d-none');
    });
});

function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="max-height: 80px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success py-2"><i class="fas fa-check-circle me-1"></i>${file.name}</span>`;
        }
        input.closest('.file-upload-box')?.classList.add('border-success');
    }
}
</script>
@endsection
