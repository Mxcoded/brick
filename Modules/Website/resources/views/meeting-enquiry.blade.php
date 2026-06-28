@extends('website::layouts.master')

@section('title', 'Meeting & Event Enquiry - Brickspoint ApartHotel')

@section('content')

<style>
    /* ─── Hero ─── */
    .enquiry-hero {
        position: relative;
        height: 55vh;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .enquiry-hero img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .enquiry-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .enquiry-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 780px;
        padding: 0 1.5rem;
    }
    .enquiry-hero-content .overline {
        font-size: 0.8rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #C8A165;
        margin-bottom: 1.25rem;
        display: block;
        font-weight: 500;
    }
    .enquiry-hero-content h1 {
        font-size: clamp(2.2rem, 5vw, 3.8rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5), 0 1px 4px rgba(0,0,0,0.3);
    }
    .enquiry-hero-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 400;
        line-height: 1.8;
        text-shadow: 0 1px 12px rgba(0,0,0,0.3);
    }

    /* ─── Section ─── */
    .section-enquiry {
        padding: 5rem 0;
        background: #fafaf8;
    }
    .section-enquiry .container {
        max-width: 1140px;
    }

    /* ─── Side Heading ─── */
    .form-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(200,161,101,0.25);
    }
    .form-heading .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(200,161,101,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .form-heading .icon-circle i {
        color: #C8A165;
        font-size: 1rem;
    }
    .form-heading h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
        color: #2c2c2c;
        letter-spacing: 0.5px;
    }

    /* ─── Form Controls ─── */
    .form-floating-custom {
        position: relative;
        margin-bottom: 1.25rem;
    }
    .form-floating-custom label {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        font-size: 0.85rem;
        color: #8a8a8a;
        pointer-events: none;
        transition: all 0.2s ease;
        background: #fff;
        padding: 0 0.25rem;
        font-weight: 400;
        letter-spacing: 0.3px;
    }
    .form-floating-custom label.label-textarea {
        top: 1rem;
        transform: none;
    }
    .form-floating-custom .form-control,
    .form-floating-custom .form-select {
        border: 1px solid #e0ddd5;
        border-radius: 4px;
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
        color: #2c2c2c;
        background: #fff;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
        height: auto;
        min-height: 48px;
    }
    .form-floating-custom .form-control:focus,
    .form-floating-custom .form-select:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 3px rgba(200,161,101,0.12);
        outline: none;
    }
    .form-floating-custom .form-control.is-invalid,
    .form-floating-custom .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: none;
    }
    .form-floating-custom .form-control:focus ~ label,
    .form-floating-custom .form-control:not(:placeholder-shown) ~ label,
    .form-floating-custom .form-select:focus ~ label,
    .form-floating-custom .form-select:not([value=""]):not([value=""]) ~ label {
        top: -0.5rem;
        left: 0.75rem;
        font-size: 0.7rem;
        color: #C8A165;
    }
    .form-floating-custom .form-control[type="date"] ~ label,
    .form-floating-custom .form-control[type="time"] ~ label {
        top: -0.5rem;
        left: 0.75rem;
        font-size: 0.7rem;
        color: #8a8a8a;
    }
    .form-floating-custom textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    .form-floating-custom .invalid-feedback {
        font-size: 0.78rem;
        margin-top: 0.3rem;
    }
    /* Show feedback when intl-tel-input wraps the input */
    .form-floating-custom:has(.iti .is-invalid) .invalid-feedback,
    .form-floating-custom:has(.iti input:invalid) .invalid-feedback,
    .iti:has(.is-invalid) ~ .invalid-feedback,
    .was-validated .iti:has(input:invalid) ~ .invalid-feedback { display: block; }

    /* ─── Submit Button ─── */
    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.9rem 3rem;
        background: #C8A165;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .btn-submit:hover {
        background: #b08d55;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(200,161,101,0.3);
        color: #fff;
    }
    .btn-submit:active {
        transform: translateY(0);
    }

    /* ─── Sidebar Cards ─── */
    .info-card {
        background: #fff;
        border: 1px solid #ece9e2;
        border-radius: 4px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        transition: border-color 0.3s ease;
    }
    .info-card:hover {
        border-color: #C8A165;
    }
    .info-card .card-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(200,161,101,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }
    .info-card .card-icon i {
        font-size: 1.4rem;
        color: #C8A165;
    }
    .info-card h5 {
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c2c2c;
        margin-bottom: 0.75rem;
        text-align: center;
    }
    .info-card p {
        font-size: 0.85rem;
        color: #6a6a6a;
        line-height: 1.7;
        margin-bottom: 0;
        text-align: center;
    }
    .info-card .phone-link {
        display: block;
        text-align: center;
        font-size: 1.15rem;
        font-weight: 600;
        color: #C8A165;
        text-decoration: none;
        margin-top: 0.5rem;
        letter-spacing: 0.5px;
    }
    .info-card .phone-link:hover {
        color: #b08d55;
    }

    /* ─── Feature List ─── */
    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f0eee8;
    }
    .feature-item:last-child {
        border-bottom: none;
    }
    .feature-item .feature-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(200,161,101,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .feature-item .feature-icon i {
        font-size: 0.6rem;
        color: #C8A165;
    }
    .feature-item span {
        font-size: 0.88rem;
        color: #4a4a4a;
        font-weight: 400;
    }

    /* ─── Flash Messages ─── */
    .alert-custom {
        border: none;
        border-radius: 4px;
        padding: 1rem 1.25rem;
        font-size: 0.9rem;
    }

    /* ─── Checkbox / Radio Cards ─── */
    .form-check-custom {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 0.25rem 0.85rem;
        padding: 1rem 1.25rem;
        background: #fff;
        border: 1px solid #e0ddd5;
        border-radius: 4px;
        cursor: pointer;
        transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease;
    }
    .form-check-custom:hover {
        border-color: #C8A165;
        background: rgba(200,161,101,0.04);
        box-shadow: 0 2px 8px rgba(200,161,101,0.08);
        transform: translateY(-1px);
    }
    .form-check-custom input[type="checkbox"]:checked ~ label,
    .form-check-custom input[type="radio"]:checked ~ label {
        color: #1a1a1a;
        font-weight: 600;
    }
    .form-check-custom:has(input[type="checkbox"]:checked),
    .form-check-custom:has(input[type="radio"]:checked) {
        border-color: #C8A165;
        background: rgba(200,161,101,0.06);
        box-shadow: 0 0 0 2px rgba(200,161,101,0.15);
    }
    .form-check-custom input[type="checkbox"],
    .form-check-custom input[type="radio"] {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        accent-color: #C8A165;
        flex-shrink: 0;
        cursor: pointer;
    }
    .form-check-custom label {
        font-size: 0.92rem;
        color: #3a3a3a;
        cursor: pointer;
        margin: 0;
        line-height: 1.5;
        font-weight: 450;
        flex: 1 1 auto;
    }
    .form-check-custom label:hover {
        color: #1a1a1a;
    }
    .form-check-desc {
        display: block;
        width: 100%;
        font-size: 0.78rem;
        color: #8a8a8a;
        padding-left: calc(20px + 0.85rem);
        margin-top: -0.1rem;
        line-height: 1.4;
    }
    .form-label-custom {
        font-size: 0.88rem;
        font-weight: 600;
        color: #2c2c2c;
        letter-spacing: 0.3px;
    }

    /* ─── Decorative Divider ─── */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 2.5rem 0 1.5rem;
    }
    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(200,161,101,0.3), transparent);
    }
    .section-divider i {
        color: #C8A165;
        font-size: 0.8rem;
        opacity: 0.5;
    }

    /* ─── Responsive ─── */
    @media (max-width: 991px) {
        .section-enquiry { padding: 3rem 0; }
        .info-card { padding: 1.5rem; }
        .form-floating-custom { margin-bottom: 1rem; }
        .btn-submit { width: 100%; justify-content: center; }
    }
    @media (max-width: 576px) {
        .enquiry-hero { min-height: 300px; height: 45vh; }
        .enquiry-hero-content h1 { font-size: 1.6rem; }
        .enquiry-hero-content p { font-size: 0.9rem; }
    }
</style>

<!-- ═══ HERO ═══ -->
<section class="enquiry-hero">
    <picture>
        <source srcset="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1400&q=80" media="(min-width: 1200px)">
        <source srcset="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1000&q=80" media="(min-width: 768px)">
        <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=600&q=80" alt="Elegant event space" loading="lazy">
    </picture>
    <div class="enquiry-hero-overlay"></div>
    <div class="enquiry-hero-content">
        <span class="overline">Brickspoint ApartHotel</span>
        <h1>Plan Your Event With Us</h1>
        <p>From intimate board meetings to grand celebrations — share your vision and our events team will craft a bespoke experience tailored to you.</p>
    </div>
</section>

<!-- ═══ FORM ═══ -->
<section class="section-enquiry">
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-triangle me-2"></i><strong>Please review the following:</strong>
                <ul class="mb-0 mt-2 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row g-5">
            <!-- ─── MAIN COLUMN ─── -->
            <div class="col-lg-7">
                <form id="enquiry-form" action="{{ route('website.meeting-enquiry.store') }}" method="POST" novalidate>
                    @csrf

                    <input type="hidden" name="_form_token" value="{{ encrypt(time()) }}">
                    <div style="position: absolute; left: -9999px;" aria-hidden="true">
                        <label for="website_url">Website</label>
                        <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                    </div>
                    <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Contact Details --}}
                    <div class="form-heading">
                        <div class="icon-circle"><i class="fas fa-user"></i></div>
                        <h5>Contact Details</h5>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder=" " required>
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder=" " required>
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="tel" class="form-control phone-input @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder=" " required>
                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company') }}" placeholder=" ">
                                <label for="company">Company / Organisation</label>
                                @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Event Details --}}
                    <div class="form-heading">
                        <div class="icon-circle"><i class="fas fa-calendar-alt"></i></div>
                        <h5>Event Details</h5>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <select class="form-select @error('event_type') is-invalid @enderror" id="event_type" name="event_type" required>
                                    <option value="" disabled {{ old('event_type') ? '' : 'selected' }}></option>
                                    <option value="Meeting" @selected(old('event_type') === 'Meeting')>Meeting</option>
                                    <option value="Conference" @selected(old('event_type') === 'Conference')>Conference</option>
                                    <option value="Wedding" @selected(old('event_type') === 'Wedding')>Wedding</option>
                                    <option value="Banquet" @selected(old('event_type') === 'Banquet')>Banquet / Dinner</option>
                                    <option value="Party" @selected(old('event_type') === 'Party')>Party / Social</option>
                                    <option value="Other" @selected(old('event_type') === 'Other')>Other</option>
                                </select>
                                <label for="event_type">Event Type <span class="text-danger">*</span></label>
                                @error('event_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="date" class="form-control @error('event_date') is-invalid @enderror" id="event_date" name="event_date" value="{{ old('event_date') }}" placeholder=" " required>
                                <label for="event_date">Event Date <span class="text-danger">*</span></label>
                                @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom">
                                <input type="number" class="form-control @error('guest_count') is-invalid @enderror" id="guest_count" name="guest_count" value="{{ old('guest_count', 10) }}" min="1" max="9999" placeholder=" " required>
                                <label for="guest_count">Number of Guests <span class="text-danger">*</span></label>
                                @error('guest_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom">
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}" placeholder=" ">
                                <label for="start_time">Start Time</label>
                                @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom">
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', '17:00') }}" placeholder=" ">
                                <label for="end_time">End Time</label>
                                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Requirements --}}
                    <div class="form-heading">
                        <div class="icon-circle"><i class="fas fa-sliders-h"></i></div>
                        <h5>Requirements &amp; Preferences</h5>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <select class="form-select @error('setup_style') is-invalid @enderror" id="setup_style" name="setup_style">
                                    <option value="" disabled {{ old('setup_style') ? '' : 'selected' }}></option>
                                    <option value="Classroom" @selected(old('setup_style') === 'Classroom')>Classroom</option>
                                    <option value="Boardroom" @selected(old('setup_style') === 'Boardroom')>Boardroom</option>
                                    <option value="Theatre" @selected(old('setup_style') === 'Theatre')>Theatre / Auditorium</option>
                                    <option value="Banquet" @selected(old('setup_style') === 'Banquet')>Banquet (Round Tables)</option>
                                    <option value="Cocktail" @selected(old('setup_style') === 'Cocktail')>Cocktail / Reception</option>
                                    <option value="U-Shape" @selected(old('setup_style') === 'U-Shape')>U-Shape</option>
                                    <option value="Hollow Square" @selected(old('setup_style') === 'Hollow Square')>Hollow Square</option>
                                </select>
                                <label for="setup_style">Preferred Setup Style</label>
                                @error('setup_style')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <select class="form-select @error('venue_interest') is-invalid @enderror" id="venue_interest" name="venue_interest">
                                    <option value="" disabled {{ old('venue_interest') ? '' : 'selected' }}></option>
                                    <option value="Grand Ballroom" @selected(old('venue_interest') === 'Grand Ballroom')>Grand Ballroom</option>
                                    <option value="Conference Hall" @selected(old('venue_interest') === 'Conference Hall')>Conference Hall</option>
                                    <option value="Executive Boardroom" @selected(old('venue_interest') === 'Executive Boardroom')>Executive Boardroom</option>
                                    <option value="Garden Terrace" @selected(old('venue_interest') === 'Garden Terrace')>Garden Terrace</option>
                                    <option value="Poolside" @selected(old('venue_interest') === 'Poolside')>Poolside</option>
                                </select>
                                <label for="venue_interest">Preferred Venue</label>
                                @error('venue_interest')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        {{-- Catering / Corkage --}}
                        <div class="col-12">
                            <label class="form-label-custom d-block mb-2">Catering Preference <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-12">
                            <div class="form-check-custom">
                                <input type="radio" id="catering_option_full" name="catering_option" value="Full Catering" {{ old('catering_option') === 'Full Catering' ? 'checked' : '' }}>
                                <label for="catering_option_full">Full F&amp;B Service</label>
                                <span class="form-check-desc">Bespoke catering &amp; food &amp; beverage service curated by our chefs</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check-custom">
                                <input type="radio" id="catering_option_corkage" name="catering_option" value="Corkage" {{ old('catering_option') === 'Corkage' ? 'checked' : '' }}>
                                <label for="catering_option_corkage">Corkage (per person)</label>
                                <span class="form-check-desc">Bring your own beverages — we handle setup, glassware &amp; service</span>
                            </div>
                        </div>
                        @error('catering_option')<div class="col-12"><div class="invalid-feedback d-block">{{ $message }}</div></div>@enderror

                        {{-- Accommodation / Overnight Stay --}}
                        <div class="col-12">
                            <div class="form-check-custom">
                                <input type="checkbox" id="accommodation_required" name="accommodation_required" value="1" {{ old('accommodation_required') ? 'checked' : '' }}>
                                <label for="accommodation_required">I require overnight accommodation for guests</label>
                            </div>
                        </div>
                        <div class="accommodation-details" id="accommodationDetails" style="display: none;">
                            <div class="row g-3 mt-1 ps-3 pe-3" style="border-left: 2px solid rgba(200,161,101,0.25);">
                                <div class="col-md-4">
                                    <div class="form-floating-custom">
                                        <input type="number" class="form-control @error('rooms_required') is-invalid @enderror" id="rooms_required" name="rooms_required" value="{{ old('rooms_required', 1) }}" min="1" max="100" placeholder=" ">
                                        <label for="rooms_required">Number of Rooms</label>
                                        @error('rooms_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating-custom">
                                        <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" id="arrival_date" name="arrival_date" value="{{ old('arrival_date') }}" placeholder=" ">
                                        <label for="arrival_date">Arrival Date</label>
                                        @error('arrival_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating-custom">
                                        <input type="date" class="form-control @error('departure_date') is-invalid @enderror" id="departure_date" name="departure_date" value="{{ old('departure_date') }}" placeholder=" ">
                                        <label for="departure_date">Departure Date</label>
                                        @error('departure_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Parking & Site Inspection --}}
                        <div class="col-sm-6">
                            <div class="form-check-custom">
                                <input type="checkbox" id="parking_required" name="parking_required" value="1" {{ old('parking_required') ? 'checked' : '' }}>
                                <label for="parking_required">Parking required for guests</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check-custom">
                                <input type="checkbox" id="site_inspection_required" name="site_inspection_required" value="1" {{ old('site_inspection_required') ? 'checked' : '' }}>
                                <label for="site_inspection_required">I would like a site inspection</label>
                            </div>
                        </div>

                        {{-- How did you hear --}}
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <select class="form-select @error('hear_about_us') is-invalid @enderror" id="hear_about_us" name="hear_about_us">
                                    <option value="" disabled {{ old('hear_about_us') ? '' : 'selected' }}></option>
                                    <option value="Google" @selected(old('hear_about_us') === 'Google')>Google Search</option>
                                    <option value="Social Media" @selected(old('hear_about_us') === 'Social Media')>Social Media</option>
                                    <option value="Friend" @selected(old('hear_about_us') === 'Friend')>Friend / Referral</option>
                                    <option value="Previous Guest" @selected(old('hear_about_us') === 'Previous Guest')>Previous Guest</option>
                                    <option value="Travel Agent" @selected(old('hear_about_us') === 'Travel Agent')>Travel Agent</option>
                                    <option value="Corporate Partner" @selected(old('hear_about_us') === 'Corporate Partner')>Corporate Partner</option>
                                    <option value="Other" @selected(old('hear_about_us') === 'Other')>Other</option>
                                </select>
                                <label for="hear_about_us">How did you hear about us?</label>
                                @error('hear_about_us')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating-custom">
                                <textarea class="form-control @error('special_requirements') is-invalid @enderror" id="special_requirements" name="special_requirements" placeholder=" ">{{ old('special_requirements') }}</textarea>
                                <label for="special_requirements">Special Requests <small class="text-muted">(dietary needs, AV equipment, decorations, etc.)</small></label>
                                @error('special_requirements')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"><i class="fas fa-asterisk"></i></div>

                    {{-- reCAPTCHA v3 hidden token --}}
                    @if(config('services.recaptcha.site_key'))
                        <input type="hidden" name="g-recaptcha-response" id="recaptcha-token">
                    @endif

                    <div class="text-center text-md-start">
                        <button type="submit" class="btn-submit" id="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            <span>Submit Enquiry</span>
                        </button>
                        <p class="text-muted small mt-3 mb-0" style="font-size:0.78rem;">
                            <i class="fas fa-lock me-1"></i>Your information is kept confidential and will not be shared.
                        </p>
                    </div>
                </form>
            </div>


            <!-- ─── SIDEBAR ─── -->
            <div class="col-lg-4 offset-lg-1">
                <div class="info-card">
                    <div class="card-icon"><i class="fas fa-phone-alt"></i></div>
                    <h5>Speak With Our Team</h5>
                    <p>Prefer to discuss your event over the phone? Our events team is ready to assist.</p>
                    <a href="tel:{{ $settings['phone'] ?? '+234 809 999 9627' }}" class="phone-link">
                        {{ $settings['phone'] ?? '+234 809 999 9627' }}
                    </a>
                </div>

                <div class="info-card">
                    <div class="card-icon"><i class="fas fa-star"></i></div>
                    <h5>Why Brickspoint?</h5>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <span>Versatile event spaces for 10–500 guests</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <span>Dedicated event coordinator from start to finish</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <span>Bespoke catering menus crafted by our chefs</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <span>State-of-the-art AV &amp; conferencing technology</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <span>Prime locations in Asokoro &amp; Wuse II, Abuja</span>
                    </div>
                </div>

                <div class="info-card">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <h5>Response Time</h5>
                    <p>Our team typically responds within <strong style="color:#2c2c2c;">24 hours</strong> on business days. For urgent enquiries, please call us directly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    {{-- Google reCAPTCHA v3 (only if configured) --}}
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('enquiry-form');
        var submitBtn = document.getElementById('submit-btn');

        // Accommodation toggle
        var toggle = document.getElementById('accommodation_required');
        var details = document.getElementById('accommodationDetails');
        function sync() { details.style.display = toggle.checked ? 'block' : 'none'; }
        if (toggle && details) {
            toggle.addEventListener('change', sync);
            sync();
        }

        // reCAPTCHA v3 token generation
        @if(config('services.recaptcha.site_key'))
        function getRecaptchaToken() {
            return new Promise(function (resolve, reject) {
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'meeting_enquiry'})
                        .then(function (token) {
                            resolve(token);
                        })
                        .catch(function (error) {
                            console.error('reCAPTCHA error:', error);
                            resolve(null);
                        });
                });
            });
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

            try {
                var token = await getRecaptchaToken();
                if (token) {
                    document.getElementById('recaptcha-token').value = token;
                }
                form.submit();
            } catch (error) {
                console.error('Error getting reCAPTCHA token:', error);
                form.submit();
            }
        });
        @endif
    });
    </script>
@endpush

@endsection
