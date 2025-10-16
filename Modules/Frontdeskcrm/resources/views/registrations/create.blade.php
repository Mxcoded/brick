@extends('frontdeskcrm::layouts.master')

@section('title', 'Guest Check-in')

@section('page-content')
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                {{-- Welcome Header --}}
                <div class="text-center mb-5">
                    <div class="hotel-brand mb-3">
                        <h1 class="text-gray-400 text-5xl font-bold mb-2" style="font-family: 'BrownSugar', sans-serif;">
                            Brickspoint Aparthotel
                        </h1>
                        <div class="welcome-badge">
                            <span class="badge fs-6 px-4 py-2 rounded-pill shadow-sm" style="background-color: #2b2225;">
                                <i class="fas fa-star me-2"></i>Welcome to Your Stay
                            </span>
                        </div>
                    </div>
                    <p class="lead text-muted mt-3">Let's get you settled in quickly and comfortably</p>
                </div>

                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following
                            errors:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Progress Tracker --}}
                @if (session()->has('guest_data') || session()->has('search_query'))
                    <div class="progress-tracker mb-5">
                        <div class="d-flex justify-content-between position-relative">
                            <div class="progress-bar position-absolute top-0 start-0 end-0"
                                style="height: 4px; background: #e9ecef; z-index: 1;"></div>
                            <div class="progress-bar position-absolute top-0 start-0"
                                style="height: 4px; background: var(--bs-success); z-index: 2; width: 0%;"
                                id="form-progress"></div>

                            @foreach ([1 => ['Personal Info', 'user'], 2 => ['Emergency Contact', 'phone-alt'], 3 => ['Stay Details', 'calendar-day'], 4 => ['Group Info', 'users'], 5 => ['Review & Sign', 'file-signature']] as $step => $data)
                                <div class="step" data-step="{{ $step }}">
                                    <div class="step-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                        style="width: 50px; height: 50px; z-index: 3;">
                                        <i class="fas fa-{{ $data[1] }}"></i>
                                    </div>
                                    <span class="step-label small fw-medium">{{ $data[0] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Main Card --}}
                <div class="card shadow-lg border-0 overflow-hidden">
                    <div class="card-header text-white py-4" style="background: linear-gradient(90deg, #2b2225, #2b2225);">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-feather-alt fa-2x me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-0">Guest Check-in Form</h4>
                                <p class="mb-0 opacity-75">Complete your registration in just a few minutes</p>
                            </div>
                            @if (session()->has('guest_data') || session()->has('search_query'))
                                <div class="flex-shrink-0">
                                    <span class="badge bg-white text-success fs-6">Step <span id="current-step">1</span> of
                                        5</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        {{-- STAGE 1: SEARCH FORM --}}
                        @if (!session()->has('guest_data') && !session()->has('search_query'))
                            <div class="text-center py-4">
                                <div class="search-icon mb-4">
                                    <i class="fas fa-search fa-3x  mb-3" style="color: #2b2225;"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Find Your Profile</h3>
                                <p class="text-muted mb-4">Enter your email or contact number to quickly retrieve your
                                    information</p>

                                <form action="{{ route('frontdesk.registrations.handle-search') }}" method="POST"
                                    class="mx-auto" style="max-width: 500px;">
                                    @csrf
                                    <div class="mb-4">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-envelope text-muted"></i>
                                            </span>
                                            <input type="text"
                                                class="form-control border-start-0 @error('search_query') is-invalid @enderror"
                                                id="search_query" name="search_query"
                                                placeholder="email@example.com or +1234567890" required autofocus>
                                            @error('search_query')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-lg px-5 py-3"
                                        style="background-color: #2b2225; color: white; transition: background-color 0.4s;">
                                        <span class="fw-bold">Continue</span>
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            {{-- STAGE 2: MULTI-STEP REGISTRATION FORM --}}
                            @if (session('status'))
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form action="{{ route('frontdesk.registrations.store') }}" method="POST" id="checkin-form">
                                @csrf
                                <input type="hidden" name="is_guest_draft" value="1">

                                @php
                                    $guestData = session('guest_data', []);
                                    $searchQuery = old('search_query', session('search_query', ''));
                                    $email = Str::contains($searchQuery, '@')
                                        ? $searchQuery
                                        : $guestData['email'] ?? '';
                                    $contact = !Str::contains($searchQuery, '@')
                                        ? $searchQuery
                                        : $guestData['contact_number'] ?? '';
                                @endphp

                                {{-- Step 1: Personal Details --}}
                                <div class="form-step" id="step-1">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-user me-2"></i>Personal Details
                                            @if ($guestData)
                                                <span class="badge bg-success ms-2">Welcome back!</span>
                                            @else
                                                <span class="badge bg-warning ms-2">New Guest</span>
                                            @endif
                                        </h5>
                                        <p class="text-muted">Tell us a bit about yourself</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <select class="form-select @error('title') is-invalid @enderror" name="title"
                                                id="title">
                                                <option value="">Select...</option>
                                                <option value="Mr."
                                                    {{ old('title', $guestData['title'] ?? '') == 'Mr.' ? 'selected' : '' }}>
                                                    Mr.</option>
                                                <option value="Ms."
                                                    {{ old('title', $guestData['title'] ?? '') == 'Ms.' ? 'selected' : '' }}>
                                                    Ms.</option>
                                                <option value="Mrs."
                                                    {{ old('title', $guestData['title'] ?? '') == 'Mrs.' ? 'selected' : '' }}>
                                                    Mrs.</option>
                                                <option value="Dr."
                                                    {{ old('title', $guestData['title'] ?? '') == 'Dr.' ? 'selected' : '' }}>
                                                    Dr.</option>
                                            </select>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label for="full_name" class="form-label">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('full_name') is-invalid @enderror"
                                                name="full_name"
                                                value="{{ old('full_name', $guestData['full_name'] ?? '') }}" required
                                                placeholder="Your full name as per ID">
                                            @error('full_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-envelope text-muted"></i></span>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    name="email" value="{{ old('email', $email) }}"
                                                    placeholder="you@example.com">
                                            </div>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_number" class="form-label">Contact Number <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-phone text-muted"></i></span>
                                                <input type="tel"
                                                    class="form-control @error('contact_number') is-invalid @enderror"
                                                    name="contact_number" value="{{ old('contact_number', $contact) }}"
                                                    required placeholder="+1 234 567 8900">
                                            </div>
                                            @error('contact_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nationality" class="form-label">Nationality</label>
                                            <select class="form-select @error('nationality') is-invalid @enderror"
                                                name="nationality" id="nationality">
                                                <option value="">Select a Country...</option>
                                                @include('frontdeskcrm::registrations._countries_options')
                                            </select>
                                            @error('nationality')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="birthday" class="form-label">Birthday</label>
                                            <input type="date"
                                                class="form-control @error('birthday') is-invalid @enderror"
                                                name="birthday"
                                                value="{{ old('birthday', $guestData['birthday'] ?? '') }}">
                                            @error('birthday')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select class="form-select @error('gender') is-invalid @enderror"
                                                name="gender" id="gender">
                                                <option value="">Select Gender...</option>
                                                <option value="male"
                                                    {{ old('gender', $guestData['gender'] ?? '') == 'male' ? 'selected' : '' }}>
                                                    Male</option>
                                                <option value="female"
                                                    {{ old('gender', $guestData['gender'] ?? '') == 'female' ? 'selected' : '' }}>
                                                    Female</option>
                                                <option value="other"
                                                    {{ old('gender', $guestData['gender'] ?? '') == 'other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="occupation" class="form-label">Occupation</label>
                                            <input type="text"
                                                class="form-control @error('occupation') is-invalid @enderror"
                                                name="occupation"
                                                value="{{ old('occupation', $guestData['occupation'] ?? '') }}"
                                                placeholder="Your profession">
                                            @error('occupation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="company_name" class="form-label">Company/Group</label>
                                            <input type="text"
                                                class="form-control @error('company_name') is-invalid @enderror"
                                                name="company_name"
                                                value="{{ old('company_name', $guestData['company_name'] ?? '') }}"
                                                placeholder="Your company or organization">
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="home_address" class="form-label">Home Address</label>
                                            <textarea class="form-control @error('home_address') is-invalid @enderror" name="home_address" rows="2"
                                                placeholder="Your complete home address">{{ old('home_address', $guestData['home_address'] ?? '') }}</textarea>
                                            @error('home_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <div></div>
                                        <button type="button" class="btn btn-success next-step" data-next="2">
                                            Continue to Emergency Contact <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 2: Emergency Contact --}}
                                <div class="form-step d-none" id="step-2">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-phone-alt me-2"></i>Emergency Contact
                                        </h5>
                                        <p class="text-muted">Who should we contact in case of emergency?</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="emergency_name" class="form-label">Contact Name</label>
                                            <input type="text"
                                                class="form-control @error('emergency_name') is-invalid @enderror"
                                                name="emergency_name"
                                                value="{{ old('emergency_name', $guestData['emergency_name'] ?? '') }}"
                                                placeholder="Full name of emergency contact">
                                            @error('emergency_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="emergency_contact" class="form-label">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-phone text-muted"></i></span>
                                                <input type="tel"
                                                    class="form-control @error('emergency_contact') is-invalid @enderror"
                                                    name="emergency_contact"
                                                    value="{{ old('emergency_contact', $guestData['emergency_contact'] ?? '') }}"
                                                    placeholder="Emergency contact number">
                                            </div>
                                            @error('emergency_contact')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        This information is kept confidential and used only for emergency situations.
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary prev-step"
                                            data-prev="1">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Personal Details
                                        </button>
                                        <button type="button" class="btn btn-success next-step" data-next="3">
                                            Continue to Stay Details <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 3: Stay Details --}}
                                <div class="form-step d-none" id="step-3">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-calendar-day me-2"></i>Stay Details
                                        </h5>
                                        <p class="text-muted">Tell us about your stay with us</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="no_of_guests" class="form-label">Number of Guests <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-users text-muted"></i></span>
                                                <input type="number"
                                                    class="form-control @error('no_of_guests') is-invalid @enderror"
                                                    name="no_of_guests" value="{{ old('no_of_guests', 1) }}"
                                                    min="1" required>
                                            </div>
                                            @error('no_of_guests')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="check_in" class="form-label">Check-in Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-sign-in-alt text-muted"></i></span>
                                                <input type="date"
                                                    class="form-control @error('check_in') is-invalid @enderror"
                                                    name="check_in" value="{{ old('check_in', now()->format('Y-m-d')) }}"
                                                    required>
                                            </div>
                                            @error('check_in')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="check_out" class="form-label">Check-out Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-sign-out-alt text-muted"></i></span>
                                                <input type="date"
                                                    class="form-control @error('check_out') is-invalid @enderror"
                                                    name="check_out"
                                                    value="{{ old('check_out', now()->addDay()->format('Y-m-d')) }}"
                                                    required>
                                            </div>
                                            @error('check_out')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary prev-step"
                                            data-prev="2">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Emergency Contact
                                        </button>
                                        <button type="button" class="btn btn-success next-step" data-next="4">
                                            Continue to Group Booking <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 4: Group Booking --}}
                                <div class="form-step d-none" id="step-4">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-users me-2"></i>Group Booking
                                        </h5>
                                        <p class="text-muted">Traveling with others? Let us know</p>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input @error('is_group_lead') is-invalid @enderror"
                                            type="checkbox" id="is_group_lead" name="is_group_lead" value="1"
                                            {{ old('is_group_lead') ? 'checked' : '' }}
                                            onchange="toggleGroupMembers(this.checked)">
                                        <label class="form-check-label fw-medium" for="is_group_lead">
                                            I am checking in with a group and I am the group lead.
                                        </label>
                                        @error('is_group_lead')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="group-members-section"
                                        style="display: {{ old('is_group_lead') ? 'block' : 'none' }};">
                                        <p class="text-muted small mb-3">Please add the full name and contact number for
                                            each member in your group.</p>
                                        <div id="group-members-container">
                                            {{-- Repopulate group members from old input --}}
                                            @if (old('group_members'))
                                                @foreach (old('group_members') as $index => $member)
                                                    <div class="row mb-2 gx-2 align-items-end">
                                                        <div class="col-md-5">
                                                            <label class="form-label small">Full Name</label>
                                                            <input type="text"
                                                                name="group_members[{{ $index }}][full_name]"
                                                                class="form-control"
                                                                value="{{ $member['full_name'] ?? '' }}"
                                                                placeholder="Member's Full Name" required>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label small">Contact Number</label>
                                                            <input type="text"
                                                                name="group_members[{{ $index }}][contact_number]"
                                                                class="form-control"
                                                                value="{{ $member['contact_number'] ?? '' }}"
                                                                placeholder="Member's Contact">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm w-100"
                                                                onclick="this.parentElement.parentElement.remove()">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                                            onclick="addGroupMember()">
                                            <i class="fas fa-plus me-1"></i> Add Member
                                        </button>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary prev-step"
                                            data-prev="3">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Stay Details
                                        </button>
                                        <button type="button" class="btn btn-success next-step" data-next="5">
                                            Continue to Review & Sign <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 5: Policy and Signature --}}
                                <div class="form-step d-none" id="step-5">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-file-signature me-2"></i>Review & Signature
                                        </h5>
                                        <p class="text-muted">Almost done! Please review and sign</p>
                                    </div>

                                    <div class="policy-agreement mb-4">
                                        <h6 class="fw-bold mb-3 text-bg-danger">Policy Agreement</h6>
                                        <div class="border rounded p-3 bg-light"
                                            style="max-height: 200px; overflow-y: auto;">
                                            <ul class="mb-0">
                                                <li class="mb-2">The agreed rate is valid for this stay only. For long
                                                    stays, Bricks Point reserves the right to revert to the RACK RATE if
                                                    checkout occurs before the agreed duration.</li>
                                                <li class="mb-2">Check-in is at <span
                                                        class="fw-bold text-bg-yellow">3:00 PM</span> and check-out is at
                                                    <span class="fw-bold text-bg-yellow">12:00 noon</span>. Early check-in
                                                    and late check-out are subject to availability and may incur additional
                                                    fees. After 5:00 PM, a full rate applies. No-shows will be charged for a
                                                    full day.</li>
                                                <li class="mb-2">Lost room keys will incur a fine.</li>
                                                <li class="mb-2">Personal safes are available in each apartment. Please
                                                    use them to secure your valuables. Bricks Point is not liable for any
                                                    loss.</li>
                                                <li class="mb-2">If you sustain an injury or experience loss/damage to
                                                    property during your stay, please notify hotel management before
                                                    departure. Any related claims will be governed by the laws of the
                                                    country where the hotel is located, and its courts will have exclusive
                                                    jurisdiction.</li>
                                                <li class="mb-0">By signing this form, you agree to abide by our
                                                    policies.</li>
                                            </ul>
                                        </div>
                                        <p class="mt-3 text-center text-muted">Thank you for choosing Brickspoint
                                            Aparthotel. We look forward to redefining your hospitality experience.</p>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Signature <span
                                                class="text-danger">*</span></label>
                                        <div class="signature-pad-container border rounded bg-white position-relative @error('guest_signature') is-invalid @enderror"
                                            style="width: 100%; max-width: 500px; height: 150px; margin: 0 auto;">
                                            <canvas id="signature-pad"></canvas>
                                            <div id="signature-placeholder" class="signature-placeholder">
                                                <i class="fas fa-pencil-alt"></i>
                                                <span>Sign Here</span>
                                            </div>
                                        </div>
                                        @error('guest_signature')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                            id="clear-signature">
                                            <i class="fas fa-eraser me-1"></i> Clear Signature
                                        </button>
                                        <input type="hidden" name="guest_signature" id="signature-data" required>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox"
                                            class="form-check-input @error('agreed_to_policies') is-invalid @enderror"
                                            name="agreed_to_policies" id="agreed_to_policies" value="1"
                                            {{ old('agreed_to_policies') ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-medium" for="agreed_to_policies">
                                            I have read and agree to the hotel's policies and privacy terms. <span
                                                class="text-danger">*</span>
                                        </label>
                                        @error('agreed_to_policies')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" name="opt_in_data_save"
                                            id="opt_in_data_save" value="1"
                                            {{ old('opt_in_data_save', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="opt_in_data_save">
                                            Save my information for faster check-ins in the future.
                                        </label>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary prev-step"
                                            data-prev="4">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Group Booking
                                        </button>
                                        <button type="submit" class="btn btn-success btn-lg px-4" id="submit-btn">
                                            <i class="fas fa-check-circle me-2"></i>Submit for Final Review
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <a href="{{ route('frontdesk.registrations.create') }}" class="btn btn-link">
                                    <i class="fas fa-redo me-1"></i> Start Over
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .signature-pad-container {
            position: relative;
            border: 1px solid #ddd;
        }

        #signature-pad {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }

        .signature-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #aaa;
            pointer-events: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step.active .step-icon {
            background-color: #198754 !important;
            color: white !important;
            transform: scale(1.1);
        }

        .step.active .step-label {
            color: #198754;
            font-weight: 600;
        }
    </style>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Multi-step form functionality
            let currentStep = 1;
            let signaturePad = null;

            // Initialize form
            initForm();

            function initForm() {
                updateProgress();

                // Set nationality from old input
                const nationalitySelect = document.getElementById('nationality');
                if (nationalitySelect) {
                    const oldNationality = "{{ old('nationality', $guestData['nationality'] ?? '') }}";
                    if (oldNationality) {
                        nationalitySelect.value = oldNationality;
                    }
                }

                // Step navigation
                document.querySelectorAll('.next-step').forEach(button => {
                    button.addEventListener('click', function() {
                        const nextStep = parseInt(this.getAttribute('data-next'));
                        if (validateStep(currentStep)) {
                            showStep(nextStep);
                        }
                    });
                });

                document.querySelectorAll('.prev-step').forEach(button => {
                    button.addEventListener('click', function() {
                        const prevStep = parseInt(this.getAttribute('data-prev'));
                        showStep(prevStep);
                    });
                });

                // Initialize signature pad when step 5 is shown
                initSignaturePad();
            }

            function showStep(step) {
                // Hide all steps
                document.querySelectorAll('.form-step').forEach(stepEl => {
                    stepEl.classList.add('d-none');
                });

                // Show current step
                document.getElementById(`step-${step}`).classList.remove('d-none');

                // Update current step
                currentStep = step;
                updateProgress();

                // Reinitialize signature pad if we're on step 5
                if (step === 5) {
                    setTimeout(initSignaturePad, 100);
                }
            }

            function validateStep(step) {
                const stepEl = document.getElementById(`step-${step}`);
                const requiredFields = stepEl.querySelectorAll('[required]');
                let isValid = true;

                for (let field of requiredFields) {
                    // Skip validation for hidden fields (like signature)
                    if (field.type === 'hidden') continue;

                    let fieldValid = true;

                    if (field.type === 'checkbox') {
                        fieldValid = field.checked;
                    } else {
                        fieldValid = field.value.trim() !== '';
                    }

                    if (!fieldValid) {
                        isValid = false;
                        field.classList.add('is-invalid');

                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains(
                                'invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'This field is required.';
                            field.parentNode.appendChild(feedback);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }

                if (!isValid) {
                    // Scroll to first error
                    const firstError = stepEl.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    alert('Please fill in all required fields before continuing.');
                }

                return isValid;
            }

            function updateProgress() {
                const progress = (currentStep / 5) * 100;
                const progressBar = document.getElementById('form-progress');
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }

                // Update step indicators
                document.querySelectorAll('.step').forEach((stepEl) => {
                    const stepNumber = parseInt(stepEl.getAttribute('data-step'));
                    if (stepNumber === currentStep) {
                        stepEl.classList.add('active');
                    } else {
                        stepEl.classList.remove('active');
                    }
                });

                // Update current step display
                const currentStepElement = document.getElementById('current-step');
                if (currentStepElement) {
                    currentStepElement.textContent = currentStep;
                }
            }

            function initSignaturePad() {
                const canvas = document.getElementById('signature-pad');
                if (!canvas) return;

                // Clear existing signature pad
                if (signaturePad) {
                    signaturePad.off();
                }

                const placeholder = document.getElementById('signature-placeholder');

                // Set canvas dimensions
                const container = canvas.parentElement;
                canvas.width = container.offsetWidth;
                canvas.height = container.offsetHeight;

                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 255)',
                    minWidth: 1,
                    maxWidth: 3,
                });

                // Restore signature from old input if exists
                const oldSignature = "{{ old('guest_signature', '') }}";
                if (oldSignature) {
                    signaturePad.fromDataURL(oldSignature);
                    placeholder.style.display = 'none';
                }

                signaturePad.addEventListener('beginStroke', () => {
                    placeholder.style.display = 'none';
                });

                document.getElementById('clear-signature').addEventListener('click', () => {
                    signaturePad.clear();
                    placeholder.style.display = 'flex';
                    document.getElementById('signature-data').value = '';
                });
            }

            // Group booking functionality
            window.toggleGroupMembers = function(isChecked) {
                document.getElementById('group-members-section').style.display = isChecked ? 'block' : 'none';
            }

            window.addGroupMember = function() {
                const container = document.getElementById('group-members-container');
                const index = container.children.length;
                const memberFields = `
            <div class="row mb-2 gx-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small">Full Name</label>
                    <input type="text" name="group_members[${index}][full_name]" class="form-control" placeholder="Member's Full Name" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Contact Number</label>
                    <input type="text" name="group_members[${index}][contact_number]" class="form-control" placeholder="Member's Contact">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', memberFields);
            }

            // Form submission handling
            const form = document.getElementById('checkin-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = document.getElementById('submit-btn');

                    // Validate all steps before submission
                    let allValid = true;
                    for (let step = 1; step <= 5; step++) {
                        if (!validateStep(step)) {
                            allValid = false;
                            showStep(step);
                            break;
                        }
                    }

                    if (!allValid) {
                        e.preventDefault();
                        alert('Please fix all validation errors before submitting.');
                        return;
                    }

                    // Validate signature
                    if (signaturePad && signaturePad.isEmpty()) {
                        e.preventDefault();
                        alert('Please provide your signature before submitting.');
                        showStep(5);
                        return;
                    }

                    // Set signature data
                    if (signaturePad && !signaturePad.isEmpty()) {
                        document.getElementById('signature-data').value = signaturePad.toDataURL();
                    }

                    // Show loading state
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                    }
                });
            }

            // Auto-show step with errors if validation failed
            @if ($errors->any())
                @php
                    $errorFields = array_keys($errors->getMessages());
                    $errorSteps = [];
                    foreach ($errorFields as $field) {
                        if (str_contains($field, 'full_name') || str_contains($field, 'contact_number') || str_contains($field, 'email')) {
                            $errorSteps[] = 1;
                        } elseif (str_contains($field, 'emergency')) {
                            $errorSteps[] = 2;
                        } elseif (str_contains($field, 'check_in') || str_contains($field, 'check_out') || str_contains($field, 'no_of_guests')) {
                            $errorSteps[] = 3;
                        } elseif (str_contains($field, 'group')) {
                            $errorSteps[] = 4;
                        } elseif (str_contains($field, 'signature') || str_contains($field, 'agreed_to_policies')) {
                            $errorSteps[] = 5;
                        }
                    }
                    $firstErrorStep = count($errorSteps) > 0 ? min($errorSteps) : 1;
                @endphp
                showStep({{ $firstErrorStep }});
            @endif
        });
    </script>
@endpush
