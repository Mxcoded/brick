@extends('frontdeskcrm::layouts.master')

@section('title', 'Guest Check-in')

@section('page-content')
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                {{-- Welcome Header --}}
                <div class="text-center mb-5">
                    <div class="hotel-brand mb-3">
                        <h1 class="text-charcoal text-5xl font-bold mb-2"
                            style="font-family: Brownsugar; font-size: 1.75rem; font-weight: 600;">
                            Brickspoint Boutique Aparthotel
                        </h1>
                        <div class="welcome-badge">
                            <span class="badge fs-6 px-4 py-2 rounded-pill shadow-sm bg-charcoal">
                                <i class="fas fa-star me-2"></i>Welcome to Your Stay
                            </span>
                        </div>
                    </div>
                    <p class="lead text-muted mt-3">Let's get you settled in quickly and comfortably</p>
                </div>
                {{-- Display Success Messages --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
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
                {{-- UPDATED: This condition now checks for errors, so the progress bar shows on a validation fail --}}
                @if (session()->has('guest_data') || session()->has('search_query') || $errors->any())
                    <div class="progress-tracker mb-5">
                        <div class="d-flex justify-content-between position-relative">
                            <div class="progress-bar position-absolute top-0 start-0 end-0"
                                style="height: 4px; background: #e9ecef; z-index: 1;"></div>
                            <div class="progress-bar position-absolute top-0 start-0"
                                style="height: 4px; z-index: 2; width: 0%;" id="form-progress"></div>

                            @foreach ([1 => ['Personal Info', 'user'], 2 => ['Emergency Contact', 'phone-alt'], 3 => ['Stay Details', 'calendar-day'], 4 => ['Group Info', 'users'], 5 => ['Review & Sign', 'file-signature']] as $step => $data)
                                <div class="step" data-step="{{ $step }}">
                                    <div class="step-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                        style="width: 50px; height: 50px; z-index: 3;">
                                        <i class="fas fa-{{ $data[1] }}"></i>
                                    </div>
                                    <span class="step-label small fw-medium d-none d-md-block">{{ $data[0] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Main Card --}}
                <div class="card shadow-lg border-0 overflow-hidden">
                    <div class="card-header text-white py-4 bg-charcoal">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-feather-alt fa-2x me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-0">Guest Check-in Form</h4>
                                <p class="mb-0 opacity-75">Complete your registration in just a few minutes</p>
                            </div>
                            {{-- UPDATED: This condition now checks for errors --}}
                            @if (session()->has('guest_data') || session()->has('search_query') || $errors->any())
                                <div class="flex-shrink-0">
                                    <span class="badge bg-white text-gold fs-6">Step <span id="current-step">1</span> of
                                        5</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-white">
                        {{-- STAGE 1: SEARCH FORM --}}
                        {{-- ====================================================== --}}
                        {{-- THE FIX FOR DATA LOSS IS HERE --}}
                        {{-- Added '&& !$errors->any()' to the condition --}}
                        {{-- ====================================================== --}}
                        {{-- UPDATED: Added !session()->has('returning_guest') to ensure returning guests see the form --}}
                        @if (
                            !session()->has('guest_data') &&
                                !session()->has('search_query') &&
                                !session()->has('returning_guest') &&
                                !$errors->any())
                            <div class="text-center py-4">
                                <div class="search-icon mb-4">
                                    <i class="fas fa-search fa-3x  mb-3 text-gold"></i>
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
                                    <button type="submit" class="btn btn-gold btn-lg px-5 py-3">
                                        <span class="fw-bold">Continue</span>
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            {{-- STAGE 2: MULTI-STEP REGISTRATION FORM --}}

                            {{-- === SECURITY BRIDGE START === --}}
                            @if (session()->has('returning_guest'))
                                <div class="alert alert-success border-left-success" role="alert">
                                    <h4 class="alert-heading"><i class="fas fa-user-check me-2"></i>Welcome Back,
                                        {{ session('returning_guest.name') }}!</h4>
                                    <p>We found your profile. For your security, your full details are hidden.</p>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Phone:</strong> {{ session('returning_guest.masked_phone') }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Email:</strong> {{ session('returning_guest.masked_email') }}
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <p class="mb-0 text-muted small">Not you? Or need to update your info?</p>
                                        <a href="{{ route('frontdesk.registrations.create', ['clear' => 1]) }}"
                                            class="btn btn-outline-danger btn-sm mt-1">
                                            <i class="fas fa-times me-1"></i> Clear & Start Over
                                        </a>
                                    </div>
                                </div>
                            @endif
                            {{-- === SECURITY BRIDGE END === --}}

                            <form action="{{ route('frontdesk.registrations.store') }}" method="POST" id="checkin-form">
                                @csrf
                                <input type="hidden" name="is_guest_draft" value="1">
                                {{-- IF RETURNING: SKIP STEPS 1 & 2 --}}
                                @if (!session()->has('returning_guest'))
                                    @php
                                        $guestData = session('guest_data', []);
                                        // Use old('search_query') first if it exists from a failed validation
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
                                            <h5 class="fw-bold text-gold mb-2">
                                                <i class="fas fa-user me-2"></i>Personal Details
                                                @if ($guestData || old('contact_number'))
                                                    <span class="badge bg-light text-dark border ms-2">Welcome back
                                                        {{ $guestData['title'] ?? old('title') }}
                                                        {{ $guestData['name'] ?? old('name') }}!</span>
                                                @else
                                                    <span class="badge bg-light text-dark border ms-2">New Guest</span>
                                                @endif
                                            </h5>
                                            <p class="text-muted">Tell us a bit about yourself</p>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4 mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <select class="form-select @error('title') is-invalid @enderror"
                                                    name="title" id="title">
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
                                            <div class="col-lg-8 mb-3">
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
                                            <div class="col-lg-6 mb-3">
                                                <label for="email" class="form-label">Email Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-envelope text-muted"></i></span>
                                                    <input type="email"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        id="email" {{-- We need this ID --}} name="email"
                                                        value="{{ old('email', $email) }}" placeholder="you@example.com">
                                                </div>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    {{-- NEW: Add a helper block for suggestions --}}
                                                    <div id="email-suggestion" class="form-text text-gold fw-bold"
                                                        style="cursor: pointer; display: none;"></div>
                                                @enderror
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="contact_number" class="form-label">Contact Number <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-phone text-muted"></i></span>
                                                    <input type="tel"
                                                        class="form-control @error('contact_number') is-invalid @enderror"
                                                        name="contact_number"
                                                        value="{{ old('contact_number', $contact) }}" required
                                                        placeholder="+1 234 567 8900">
                                                </div>
                                                {{-- ====================================================== --}}
                                                {{-- NEW: Added helper text for phone number format --}}
                                                {{-- ====================================================== --}}
                                                @error('contact_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <small class="form-text text-muted">Please provide a valid number. (e.g.,
                                                        0809... or +234 809...)</small>
                                                @enderror
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="nationality" class="form-label">Nationality</label>
                                                <select class="form-select @error('nationality') is-invalid @enderror"
                                                    name="nationality" id="nationality">
                                                    <option value="">Select a Country...</option>
                                                    @include('frontdeskcrm::registrations.partials._countries_options')
                                                </select>
                                                @error('nationality')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="birthday" class="form-label">Birthday</label>
                                                <input type="date"
                                                    class="form-control @error('birthday') is-invalid @enderror"
                                                    name="birthday"
                                                    value="{{ old('birthday', $guestData['birthday'] ?? '') }}">
                                                @error('birthday')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-lg-6 mb-3">
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
                                            <div class="col-lg-6 mb-3">
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
                                            <div class="col-lg-6 mb-3">
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
                                            <button type="button" class="btn btn-gold next-step" data-next="2">
                                                Continue to Emergency Contact <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Step 2: Emergency Contact --}}
                                    <div class="form-step d-none" id="step-2">
                                        <div class="step-header mb-4">
                                            <h5 class="fw-bold text-gold mb-2">
                                                <i class="fas fa-phone-alt me-2"></i>Emergency Contact
                                            </h5>
                                            <p class="text-muted">Who should we contact in case of emergency?</p>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
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
                                            <div class="col-lg-6 mb-3">
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
                                            <button type="button" class="btn btn-gold next-step" data-next="3">
                                                Continue to Stay Details <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                {{-- Step 3: Stay Details (ALWAYS SHOW) --}}
                                {{-- IMPORTANT: If returning, make this ID "step-1" so JS shows it first --}}
                                <div class="form-step {{ session()->has('returning_guest') ? '' : 'd-none' }}"
                                    id="{{ session()->has('returning_guest') ? 'step-1' : 'step-3' }}">

                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-gold mb-2">
                                            <i class="fas fa-calendar-day me-2"></i>Stay Details
                                        </h5>
                                        <p class="text-muted">Tell us about your stay with us</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 mb-3">
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
                                        <div class="col-lg-4 mb-3">
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
                                        <div class="col-lg-4 mb-3">
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
                                        @if (!session()->has('returning_guest'))
                                            <button type="button" class="btn btn-outline-secondary prev-step"
                                                data-prev="2">
                                                <i class="fas fa-arrow-left me-2"></i>Back
                                            </button>
                                        @else
                                            <div></div> {{-- Spacer for returning guest --}}
                                        @endif

                                        {{-- If returning, next step is 2 (Group), otherwise 4 --}}
                                        {{-- FIXED: Always go to Step 4 next, since Step 2 is skipped for returning guests --}}
                                        <button type="button" class="btn btn-gold next-step" data-next="4">
                                            Continue <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 4: Group Booking (Reworded) --}}
                                <div class="form-step d-none" id="step-4">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-gold mb-2">
                                            <i class="fas fa-users me-2"></i>Additional Rooms & Guests
                                        </h5>
                                        <p class="text-muted">Let us know if you need more than one room or are booking
                                            for
                                            others.</p>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input @error('is_group_lead') is-invalid @enderror"
                                            type="checkbox" id="is_group_lead" name="is_group_lead" value="1"
                                            @checked(old('is_group_lead')) onchange="toggleGroupMembers(this.checked)">
                                        <label class="form-check-label fw-medium" for="is_group_lead">
                                            I am booking for more than one room OR for other people.
                                        </label>
                                        @error('is_group_lead')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="group-members-section"
                                        style="display: {{ old('is_group_lead') ? 'block' : 'none' }};">

                                        <p class="text-muted small mb-3">
                                            Please add the primary guest for each additional room.
                                            <br>
                                            <strong>If you need a second room for yourself, please click "Add Room" and
                                                enter your own name and contact number again.</strong>
                                        </p>

                                        <div id="group-members-container">
                                            {{-- Repopulate group members from old input --}}
                                            @if (old('group_members'))
                                                @foreach (old('group_members') as $index => $member)
                                                    <div class="row mb-2 gx-2 align-items-end">
                                                        <div class="col-lg-4"> {{-- Changed to 4 --}}
                                                            <label class="form-label small">Full Name (for this
                                                                room)</label>
                                                            <input type="text"
                                                                name="group_members[{{ $index }}][full_name]"
                                                                class="form-control @error('group_members.' . $index . '.full_name') is-invalid @enderror"
                                                                value="{{ $member['full_name'] ?? '' }}"
                                                                placeholder="Guest's Full Name" required>
                                                            @error('group_members.' . $index . '.full_name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-lg-4"> {{-- Changed to 4 --}}
                                                            <label class="form-label small">Contact Number
                                                                (optional)
                                                            </label>
                                                            <input type="text"
                                                                name="group_members[{{ $index }}][contact_number]"
                                                                class="form-control @error('group_members.' . $index . '.contact_number') is-invalid @enderror"
                                                                value="{{ $member['contact_number'] ?? '' }}"
                                                                placeholder="Guest's Contact">
                                                            @error('group_members.' . $index . '.contact_number')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        {{-- === NEW EMAIL FIELD === --}}
                                                        <div class="col-lg-3"> {{-- Changed to 3 --}}
                                                            <label class="form-label small">Email (optional)</label>
                                                            <input type="email"
                                                                name="group_members[{{ $index }}][email]"
                                                                class="form-control @error('group_members.' . $index . '.email') is-invalid @enderror"
                                                                value="{{ $member['email'] ?? '' }}"
                                                                placeholder="Guest's Email">
                                                            @error('group_members.' . $index . '.email')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        {{-- === END NEW FIELD === --}}

                                                        <div class="col-lg-1"> {{-- Changed to 1 --}}
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
                                        <button type="button" class="btn btn-outline-gold btn-sm mt-2"
                                            onclick="addGroupMember()">
                                            <i class="fas fa-plus me-1"></i> Add Room / Guest
                                        </button>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary prev-step"
                                            data-prev="3">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Stay Details
                                        </button>
                                        <button type="button" class="btn btn-gold next-step" data-next="5">
                                            Continue to Review & Sign <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 5: Policy and Signature --}}
                                <div class="form-step d-none" id="step-5">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold text-gold mb-2">
                                            <i class="fas fa-file-signature me-2"></i>Review & Signature
                                        </h5>
                                        <p class="text-muted">Almost done! Please review and sign</p>
                                    </div>

                                    <div class="policy-agreement mb-4">
                                        <h6 class="fw-bold mb-3 border-bottom pb-2">Policy Agreement</h6>
                                        <div class="border rounded p-3 bg-light"
                                            style="max-height: 200px; overflow-y: auto;">
                                            <ul class="mb-0">
                                                <li class="mb-2">The agreed rate is valid for this stay only. For
                                                    long
                                                    stays, Bricks Point reserves the right to revert to the RACK RATE if
                                                    checkout occurs before the agreed duration.</li>
                                                <li class="mb-2">Check-in is at <span class="fw-bold text-dark">3:00
                                                        PM</span> and check-out is at
                                                    <span class="fw-bold text-dark">12:00 noon</span>. Early check-in
                                                    and late check-out are subject to availability and may incur
                                                    additional
                                                    fees. After 5:00 PM, a full rate applies. No-shows will be charged
                                                    for a
                                                    full day.
                                                </li>
                                                <li class="mb-2">Lost room keys will incur a fine.</li>
                                                <li class="mb-2">Personal safes are available in each apartment.
                                                    Please
                                                    use them to secure your valuables. Bricks Point is not liable for
                                                    any
                                                    loss.</li>
                                                <li class="mb-2">If you sustain an injury or experience loss/damage
                                                    to
                                                    property during your stay, please notify hotel management before
                                                    departure. Any related claims will be governed by the laws of the
                                                    country where the hotel is located, and its courts will have
                                                    exclusive
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
                                            @checked(old('agreed_to_policies')) required>
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
                                            id="opt_in_data_save" value="1" @checked(old('opt_in_data_save', true))>
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
                                        <button type="submit" class="btn btn-gold btn-lg px-4" id="submit-btn">
                                            <i class="fas fa-check-circle me-2"></i>Submit for Final Review
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <a href="{{ route('frontdesk.registrations.create', ['clear' => 1]) }}"
                                    class="btn btn-link">
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

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--brand-gold);
            border-color: var(--brand-gold);
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

                    // Skip validation for fields in a hidden group section
                    if (field.closest('#group-members-section') && field.closest('#group-members-section').style
                        .display === 'none') {
                        continue;
                    }

                    let fieldValid = true;

                    if (field.type === 'checkbox') {
                        fieldValid = field.checked;
                    } else {
                        fieldValid = field.value.trim() !== '';
                    }

                    if (!fieldValid) {
                        isValid = false;
                        field.classList.add('is-invalid');

                        // Find or create the invalid-feedback message
                        let feedback = field.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            field.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = 'This field is required.';

                    } else {
                        field.classList.remove('is-invalid');
                    }
                }

                // Special validation for signature
                if (step === 5) {
                    if (signaturePad && signaturePad.isEmpty() && !document.getElementById('signature-data')
                        .value) {
                        document.querySelector('.signature-pad-container').classList.add('is-invalid');
                        document.querySelector('.signature-pad-container ~ .invalid-feedback').style.display =
                            'block';
                        isValid = false;
                    } else {
                        document.querySelector('.signature-pad-container').classList.remove('is-invalid');
                        document.querySelector('.signature-pad-container ~ .invalid-feedback').style.display =
                            'none';
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
                    // alert('Please fill in all required fields before continuing.');
                }

                return isValid;
            }

            function updateProgress() {
                const totalSteps = 5;
                const progress = (currentStep / totalSteps) * 100;
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

                // Ensure canvas is visible before getting offsetWidth
                if (container.offsetWidth === 0) {
                    setTimeout(initSignaturePad, 100); // Try again
                    return;
                }

                canvas.width = container.offsetWidth;
                canvas.height = container.offsetHeight;

                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(51, 51, 51)',
                    minWidth: 1,
                    maxWidth: 3,
                });

                // Restore signature from old input if exists
                const oldSignature = "{{ old('guest_signature', '') }}";
                if (oldSignature) {
                    signaturePad.fromDataURL(oldSignature);
                    placeholder.style.display = 'none';
                    document.getElementById('signature-data').value = oldSignature;
                }

                signaturePad.addEventListener('beginStroke', () => {
                    placeholder.style.display = 'none';
                });

                signaturePad.addEventListener('endStroke', () => {
                    document.getElementById('signature-data').value = signaturePad.toDataURL();
                    document.querySelector('.signature-pad-container').classList.remove('is-invalid');
                    document.querySelector('.signature-pad-container ~ .invalid-feedback').style.display =
                        'none';
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
                    <div class="col-lg-4">
                        <label class="form-label small">Full Name (for this room)</label>
                        <input type="text" name="group_members[${index}][full_name]" class="form-control" placeholder="Guest's Full Name" required>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label small">Contact Number (optional)</label>
                        <input type="text" name="group_members[${index}][contact_number]" class="form-control" placeholder="Guest's Contact">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small">Email (optional)</label>
                        <input type="email" name="group_members[${index}][email]" class="form-control" placeholder="Guest's Email">
                    </div>
                    <div class="col-lg-1">
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
                    let firstInvalidStep = 0;

                    for (let step = 1; step <= 5; step++) {
                        if (!validateStep(step)) {
                            allValid = false;
                            if (firstInvalidStep === 0) {
                                firstInvalidStep = step;
                            }
                        }
                    }

                    if (!allValid) {
                        e.preventDefault();
                        showStep(firstInvalidStep); // Jump to the first step with an error
                        alert('Please fix all validation errors before submitting.');
                        return;
                    }

                    // Set signature data one last time
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
        // (Inside the <script> tag at the bottom)

        // A simple 'Did you mean?' function for common email typos
        function checkEmailTypos() {
            const emailField = document.getElementById('email');
            const suggestionEl = document.getElementById('email-suggestion');
            if (!emailField || !suggestionEl) return;

            // List of common domains
            const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];

            emailField.addEventListener('blur', function() {
                const email = this.value;
                if (!email.includes('@')) {
                    suggestionEl.style.display = 'none';
                    return;
                }

                const [local, domain] = email.split('@');

                // Simple check (a real library is more advanced)
                if (domain === 'gmial.com' || domain === 'gamil.com' || domain === 'gmail.con' || domain ===
                    'gmaill.com' || domain === 'gmail.cm') {
                    showSuggestion('gmail.com');
                } else if (domain === 'yaho.com' || domain === 'yhoo.com') {
                    showSuggestion('yahoo.com');
                } else {
                    suggestionEl.style.display = 'none';
                }
            });

            function showSuggestion(correctedDomain) {
                const emailField = document.getElementById('email');
                const suggestionEl = document.getElementById('email-suggestion');
                const correctedEmail = emailField.value.split('@')[0] + '@' + correctedDomain;

                suggestionEl.innerHTML = `Did you mean: ${correctedEmail}?`;
                suggestionEl.style.display = 'block';

                // If they click the suggestion, fix the email for them
                suggestionEl.onclick = function() {
                    emailField.value = correctedEmail;
                    suggestionEl.style.display = 'none';
                };
            }
        }

        // Run our new function
        checkEmailTypos();
    </script>
@endpush
