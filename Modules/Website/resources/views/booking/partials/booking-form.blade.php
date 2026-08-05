<form action="{{ route('website.booking.store') }}" method="POST" id="bookingForm"
    @if ($useCartFlow) data-cart-flow="1" @endif
    data-cart-max-guests="{{ max(1, $cartMaxGuests) }}">
    @csrf

    <div style="position: absolute; left: -9999px;" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>
    <input type="hidden" name="register_time" value="{{ time() }}">

    <div id="draftStatus" class="draft-saved-indicator" role="status" aria-live="polite"></div>

    @php $sectionStep = 0; @endphp

    @if ($useCartFlow)
        <div class="alert alert-info availability-banner mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-shopping-cart me-3 fa-lg"></i>
                <div>
                    <strong>{{ $cart['total_rooms'] }} room(s) selected</strong> for
                    {{ \Carbon\Carbon::parse($cart['check_in'])->format('M d') }} -
                    {{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}
                    ({{ $cart['nights'] }} {{ Str::plural('night', $cart['nights']) }})
                </div>
                <a href="{{ route('website.book') }}"
                    class="ms-auto text-nowrap btn-outline-brand btn-sm">Modify</a>
            </div>
        </div>
    @else
        @php $sectionStep = 1; @endphp
        <div class="form-section reveal" data-step="{{ $sectionStep }}">
            <div class="form-section-header">
                <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                <h5>Stay Dates &amp; Room</h5>
                <span class="step-badge">Step {{ $sectionStep }}</span>
            </div>
            <div class="form-section-body">
                <div class="date-chips" id="dateChips">
                    <span class="text-muted small me-1 align-self-center">Quick pick:</span>
                    <button type="button" class="date-chip" data-offset="0" data-nights="1">Tonight</button>
                    <button type="button" class="date-chip" data-offset="1" data-nights="1">Tomorrow</button>
                    <button type="button" class="date-chip" data-offset="3" data-nights="3">3 Nights</button>
                    <button type="button" class="date-chip" data-offset="7" data-nights="7">1 Week</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="check_in_date">Check-In Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" id="check_in_date"
                            class="form-control form-control-lg" value="{{ $reqCheckIn }}"
                            min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="check_out_date">Check-Out Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="check_out_date" id="check_out_date"
                            class="form-control form-control-lg" value="{{ $reqCheckOut }}"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                    <div class="col-12">
                        <div id="availabilityStatus" class="d-none text-muted small py-1">
                            <i class="fas fa-circle-notch fa-spin me-1"></i>Checking availability...
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Choose Your Room <span class="text-danger">*</span></label>
                        <div class="room-cards mb-2" id="roomCards">
                            @if ($roomTypes->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-bed fa-2x mb-2 d-block"
                                        style="color: var(--brand-gold-light);"></i>
                                    <p class="mb-1">No room types are currently available.</p>
                                    <small>Please contact us for assistance or try different dates.</small>
                                </div>
                            @endif
                            @foreach ($roomTypes as $roomOption)
                                <div class="room-card {{ $reqRoomTypeId == $roomOption->id ? 'selected' : '' }}"
                                    role="button" tabindex="0"
                                    aria-label="{{ $roomOption->name }}: ₦{{ number_format($roomOption->display_price, 2) }} per night, capacity {{ $roomOption->capacity }} guests"
                                    data-room-type-id="{{ $roomOption->id }}"
                                    data-price="{{ $roomOption->display_price }}"
                                    data-flat-price="{{ $roomOption->price }}"
                                    data-rate-code-id="{{ $roomOption->rate_code_id ?? '' }}"
                                    data-image="{{ $roomOption->image_url }}"
                                    data-name="{{ $roomOption->name }}"
                                    data-capacity="{{ $roomOption->capacity }}"
                                    data-base-occupancy="{{ $roomOption->base_occupancy ?? 2 }}"
                                    data-extra-adult-fee="{{ $roomOption->extra_adult_fee ?? 0 }}"
                                    data-extra-child-fee="{{ $roomOption->extra_child_fee ?? 0 }}"
                                    data-units="{{ $roomOption->units_count }}">
                                    <img src="{{ $roomOption->image_url ?? asset('images/default-room.jpg') }}"
                                        alt="{{ $roomOption->name }}" class="card-img-top" loading="lazy"
                                        onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22120%22 fill=%22%23C8A165%22%3E%3Crect width=%22400%22 height=%22120%22 fill=%22%23faf8f5%22/%3E%3Ctext x=%22200%22 y=%2265%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2214%22 fill=%22%23b08c54%22%3ENo Image Available%3C/text%3E%3C/svg%3E'">
                                    <span class="capacity-badge"><i
                                            class="fas fa-user-friends me-1"></i>{{ $roomOption->capacity }}</span>
                                    <span class="selected-badge"><i class="fas fa-check me-1"></i>Selected</span>
                                    <div class="card-body">
                                        <div class="card-title">{{ $roomOption->name }}</div>
                                        <div class="card-price">₦{{ number_format($roomOption->display_price, 2) }}<span
                                                class="card-meta"> /night</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <select name="room_type_id" id="room_type_id" class="d-none" aria-hidden="true" tabindex="-1">
                            <option value="" disabled {{ empty($reqRoomTypeId) ? 'selected' : '' }}></option>
                            @foreach ($roomTypes as $roomOption)
                                <option value="{{ $roomOption->id }}"
                                    data-price="{{ $roomOption->display_price }}"
                                    data-flat-price="{{ $roomOption->price }}"
                                    data-rate-code-id="{{ $roomOption->rate_code_id ?? '' }}"
                                    data-image="{{ $roomOption->image_url }}"
                                    data-name="{{ $roomOption->name }}"
                                    data-capacity="{{ $roomOption->capacity }}"
                                    data-base-occupancy="{{ $roomOption->base_occupancy ?? 2 }}"
                                    data-extra-adult-fee="{{ $roomOption->extra_adult_fee ?? 0 }}"
                                    data-extra-child-fee="{{ $roomOption->extra_child_fee ?? 0 }}"
                                    data-units="{{ $roomOption->units_count }}"
                                    {{ $reqRoomTypeId == $roomOption->id ? 'selected' : '' }}>
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12" id="roomUnitSection" style="display: none;">
                        <label class="form-label" for="room_unit_id">Specific Room Unit <span
                                class="text-muted fw-normal">(optional)</span></label>
                        <select name="room_unit_id" id="room_unit_id" class="form-select">
                            <option value="">-- Auto-assign at check-in --</option>
                        </select>
                        <div class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle me-1"></i> Choose a specific room or leave blank
                            for auto-assignment.
                        </div>
                        <div id="unitLoadingSpinner" class="text-center py-2 d-none">
                            <span class="spinner-border spinner-border-sm"
                                style="color: var(--brand-gold);"></span> Loading available units...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php $sectionStep = $useCartFlow ? 1 : 2; @endphp
    <div class="form-section reveal" data-step="{{ $sectionStep }}">
        <div class="form-section-header">
            <div class="section-icon"><i class="fas fa-user"></i></div>
            <h5>Guest Information</h5>
            <span class="step-badge">Step {{ $sectionStep }}</span>
        </div>
        <div class="form-section-body">
            @php
                $hasName = Auth::check() && $guest && !empty($guest->full_name);
                $hasEmail = Auth::check() && $guest && !empty($guest->email);
            @endphp
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="guest_name">Full Name <span
                            class="text-danger">*</span></label>
                    <input type="text" name="guest_name" id="guest_name"
                        class="form-control {{ $hasName ? 'bg-light text-muted' : '' }}"
                        value="{{ old('guest_name', $draft['guest_name'] ?? ($guest->full_name ?? (Auth::user()->name ?? ''))) }}"
                        required {{ $hasName ? 'readonly' : '' }} placeholder="e.g. John Doe">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_email">Email Address <span
                            class="text-danger">*</span></label>
                    <input type="email" name="guest_email" id="guest_email"
                        class="form-control {{ $hasEmail ? 'bg-light text-muted' : '' }}"
                        value="{{ old('guest_email', $draft['guest_email'] ?? ($guest->email ?? (Auth::user()->email ?? ''))) }}"
                        required {{ $hasEmail ? 'readonly' : '' }} placeholder="your@email.com"
                        aria-describedby="emailFeedback">
                    <div id="emailFeedback" class="invalid-feedback" aria-live="polite"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_phone">Phone Number <span
                            class="text-danger">*</span></label>
                    <input type="tel" name="guest_phone" id="guest_phone"
                        class="form-control phone-input {{ $hasPhone ? 'bg-light text-muted' : '' }}"
                        value="{{ old('guest_phone', $draft['guest_phone'] ?? ($guest->contact_number ?? '')) }}" required
                        {{ $hasPhone ? 'readonly' : '' }} placeholder="+234 800 000 0000">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_gender">Gender <span
                            class="text-danger">*</span></label>
                    @php $selGender = old('guest_gender', $draft['guest_gender'] ?? ($guest->gender ?? '')); @endphp
                    @if ($hasGender)
                        <input type="hidden" name="guest_gender" value="{{ $selGender }}">
                    @endif
                    <select name="guest_gender" id="guest_gender"
                        class="form-select {{ $hasGender ? 'bg-light text-muted' : '' }}" required
                        {{ $hasGender ? 'disabled' : '' }}>
                        <option value="" disabled {{ empty($selGender) ? 'selected' : '' }}>Select
                            Gender...</option>
                        <option value="male" {{ $selGender == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ $selGender == 'female' ? 'selected' : '' }}>Female
                        </option>
                        <option value="other" {{ $selGender == 'other' ? 'selected' : '' }}>Other
                        </option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="guest_address">Home Address <span
                            class="text-danger">*</span></label>
                    <input type="text" name="guest_address" id="guest_address"
                        class="form-control {{ $hasAddress ? 'bg-light text-muted' : '' }}"
                        placeholder="Street Address, City, State"
                        value="{{ old('guest_address', $draft['guest_address'] ?? ($guest->home_address ?? '')) }}" required
                        {{ $hasAddress ? 'readonly' : '' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_nationality">Nationality <span
                            class="text-danger">*</span></label>
                    <input type="text" name="guest_nationality" id="guest_nationality"
                        class="form-control {{ $hasNationality ? 'bg-light text-muted' : '' }}"
                        value="{{ old('guest_nationality', $draft['guest_nationality'] ?? ($guest->nationality ?? 'Nigeria')) }}" required
                        {{ $hasNationality ? 'readonly' : '' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_dob">Date of Birth</label>
                    <input type="date" name="guest_dob" id="guest_dob"
                        class="form-control {{ $hasDob ? 'bg-light text-muted' : '' }}"
                        value="{{ old('guest_dob', $draft['guest_dob'] ?? ($guest && $guest->birthday ? $guest->birthday->format('Y-m-d') : '')) }}"
                        {{ $hasDob ? 'readonly' : '' }}>
                    <div class="form-text text-muted mt-1"><i class="fas fa-info-circle me-1"></i>
                        Required for age verification</div>
                </div>
            </div>
        </div>
    </div>

    @php $sectionStep = $useCartFlow ? 2 : 3; @endphp
    <div class="form-section reveal" data-step="{{ $sectionStep }}">
        <div class="form-section-header">
            <div class="section-icon"><i class="fas fa-id-card"></i></div>
            <h5>Identity Verification</h5>
            <span class="step-badge">Step {{ $sectionStep }}</span>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="guest_id_type">ID Card Type <span
                            class="text-danger">*</span></label>
                    @php $selIdType = old('guest_id_type', $draft['guest_id_type'] ?? ($guest->identification_type ?? '')); @endphp
                    @if ($hasIdType)
                        <input type="hidden" name="guest_id_type" value="{{ $selIdType }}">
                    @endif
                    <select name="guest_id_type" id="guest_id_type"
                        class="form-select {{ $hasIdType ? 'bg-light text-muted' : '' }}" required
                        {{ $hasIdType ? 'disabled' : '' }}>
                        <option value="" disabled {{ empty($selIdType) ? 'selected' : '' }}>Select
                            ID Type...</option>
                        <option value="International Passport"
                            {{ $selIdType == 'International Passport' ? 'selected' : '' }}>International
                            Passport</option>
                        <option value="NIN" {{ $selIdType == 'NIN' ? 'selected' : '' }}>NIN (National
                            ID)</option>
                        <option value="Drivers License" {{ $selIdType == 'Drivers License' ? 'selected' : '' }}>
                            Driver's License
                        </option>
                        <option value="Voters Card" {{ $selIdType == 'Voters Card' ? 'selected' : '' }}>
                            Voter's Card</option>
                        <option value="Other" {{ $selIdType == 'Other' ? 'selected' : '' }}>Other Govt ID
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_id_number">ID Number <span
                            class="text-danger">*</span></label>
                    <input type="text" name="guest_id_number" id="guest_id_number"
                        class="form-control {{ $hasIdNumber ? 'bg-light text-muted' : '' }}"
                        placeholder="e.g. A01234567"
                        value="{{ old('guest_id_number', $draft['guest_id_number'] ?? ($guest->identification_number ?? '')) }}" required
                        {{ $hasIdNumber ? 'readonly' : '' }}>
                </div>
            </div>
            <div class="form-text text-muted mt-2">
                <i class="fas fa-shield-alt me-1" style="color: var(--brand-gold);"></i>
                Your ID information is collected for check-in verification and is securely stored.
            </div>
        </div>
    </div>

    @php $sectionStep = $useCartFlow ? 3 : 4; @endphp
    <div class="form-section reveal" data-step="{{ $sectionStep }}">
        <div class="form-section-header">
            <div class="section-icon"><i class="fas fa-users"></i></div>
            <h5>Guests &amp; Requests</h5>
            <span class="step-badge">Step {{ $sectionStep }}</span>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="guest-count-box">
                        <div
                            class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 text-nowrap" for="adults">Adults</label>
                                    <div class="guest-stepper">
                                        <button type="button" class="step-dec" data-target="adults"
                                            aria-label="Decrease adults">−</button>
                                        <input type="number" name="adults" id="adults"
                                            value="{{ old('adults', $draft['adults'] ?? 1) }}" min="1"
                                            max="{{ max(1, $cartMaxGuests) }}" required readonly>
                                        <button type="button" class="step-inc" data-target="adults"
                                            aria-label="Increase adults">+</button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 text-nowrap" for="children">Children</label>
                                    <div class="guest-stepper">
                                        <button type="button" class="step-dec" data-target="children"
                                            aria-label="Decrease children">−</button>
                                        <input type="number" name="children" id="children"
                                            value="{{ old('children', $draft['children'] ?? 0) }}" min="0"
                                            max="{{ max(0, $cartMaxGuests - 1) }}" readonly>
                                        <button type="button" class="step-inc" data-target="children"
                                            aria-label="Increase children">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="capacity-pill{{ $hasSelectedRoom ? ' has-room' : '' }}"
                                id="capacityPill">
                                <span id="capacityPillText">
                                    <i class="fas fa-users me-1"></i>{{ $useCartFlow ? $cart['total_rooms'].' room(s) in cart' : ($hasSelectedRoom ? 'Max '.$initialCapacity.' guests' : 'Select a room') }}
                                </span>
                            </div>
                        </div>
                        <div class="occupancy-bar-wrap">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="occupancy-label" id="occupancyLabel">
                                    <i class="fas fa-bed me-1"></i>{{ $useCartFlow ? $cart['total_rooms'].' room(s) for '.$cart['total_guests'].' guests' : ($hasSelectedRoom ? e($selectedRoomType->name).' — room for '.$initialCapacity.' guests' : 'Select a room') }}
                                </span>
                                <span class="occupancy-count" id="occupancyCount" aria-live="polite"></span>
                            </div>
                            <div class="occupancy-bar">
                                <div class="occupancy-bar-fill" id="occupancyBarFill"
                                    style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" id="capacityHint">
                                    <i class="fas fa-info-circle me-1"></i>{{ $useCartFlow ? 'Guest count across all rooms selected.' : ($hasSelectedRoom ? 'Room capacity: '.$initialCapacity.' guests max.' : 'Pick a room above to see occupancy & fees') }}
                                </small>
                                <small class="occupancy-fee-hint d-none" id="guestFeeHint"></small>
                            </div>
                        </div>
                    </div>
                    <div id="capacityError" class="text-danger small mt-2 d-none" aria-live="polite"></div>
                </div>
                <div class="col-12">
                    <div class="special-requests-box">
                        <label class="form-label mb-2" for="special_requests"><i
                                class="far fa-comment-dots me-2" style="color: var(--brand-gold);"></i>Special
                            Requests</label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="request-chip" data-text="Late check-in">Late
                                check-in</button>
                            <button type="button" class="request-chip" data-text="Early check-in">Early
                                check-in</button>
                            <button type="button" class="request-chip" data-text="Extra pillows">Extra
                                pillows</button>
                            <button type="button" class="request-chip" data-text="Extra towels">Extra
                                towels</button>
                            <button type="button" class="request-chip" data-text="Airport transfer">Airport
                                transfer</button>
                            <button type="button" class="request-chip" data-text="Anniversary setup">Anniversary
                                setup</button>
                        </div>
                        <textarea name="special_requests" id="special_requests" class="form-control"
                            rows="3"
                            placeholder="e.g. Late check-in, extra pillows, anniversary celebration...">{{ old('special_requests', $draft['special_requests'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            @if (!Auth::check())
                <div class="mt-4">
                    <div class="create-account-box">
                        <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input" type="checkbox" id="createAccountToggle"
                                name="create_account" value="1"
                                {{ old('create_account', $draft['create_account'] ?? 0) ? 'checked' : '' }}
                                style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                            <label class="form-check-label fw-bold" for="createAccountToggle"
                                style="cursor: pointer;">
                                <i class="fas fa-user-plus me-2" style="color: var(--brand-gold);"></i>Create
                                an account for faster
                                booking next time
                            </label>
                        </div>
                        <div class="collapse mt-3 {{ old('create_account', $draft['create_account'] ?? 0) ? 'show' : '' }}"
                            id="accountFields">
                            <div class="p-3 bg-white rounded border">
                                <label class="form-label" for="password">Choose Password</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Min. 8 characters" style="max-width: 350px;">
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @php $sectionStep = $useCartFlow ? 4 : 5; @endphp
    <div class="form-section reveal" data-step="{{ $sectionStep }}">
        <div class="form-section-header">
            <div class="section-icon"><i class="fas fa-credit-card"></i></div>
            <h5>Payment Method</h5>
            <span class="step-badge">Step {{ $sectionStep }}</span>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="paystack"
                            {{ old('payment_method', $draft['payment_method'] ?? 'paystack') === 'paystack' ? 'checked' : '' }}>
                        <div class="payment-content d-flex align-items-start gap-3">
                            <div class="payment-icon pay-now"><i class="fas fa-credit-card"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">Pay Now</div>
                                <div class="small text-muted">Instant confirmation via card or transfer
                                </div>
                            </div>
                            <div class="check-indicator"><i class="fas fa-check"></i></div>
                        </div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="pay_on_arrival"
                            {{ old('payment_method', $draft['payment_method'] ?? '') === 'pay_on_arrival' ? 'checked' : '' }}>
                        <div class="payment-content d-flex align-items-start gap-3">
                            <div class="payment-icon pay-later"><i class="fas fa-hotel"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">Pay at Hotel</div>
                                <div class="small text-muted">Settle payment at the front desk upon arrival
                                </div>
                            </div>
                            <div class="check-indicator"><i class="fas fa-check"></i></div>
                        </div>
                    </label>
                </div>
            </div>
            <p class="payment-hint text-center mt-2 mb-0">
                <i class="fas fa-lock me-1"></i> We never store your card details — payments are securely
                processed by Paystack.
            </p>
        </div>
    </div>

    <div class="stepper-nav" id="stepperNav">
        <button type="button" class="btn btn-outline-brand" id="stepperBack"
            aria-label="Go back to the previous step">
            <i class="fas fa-arrow-left me-2"></i>Back
        </button>
        <button type="button" class="btn btn-brand" id="stepperNext" aria-label="Continue to the next step">
            Next<i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>

    <div class="review-strip" id="reviewStrip" @if ($useCartFlow) style="display:none;" @endif>
        <div class="rv-item">
            <i class="fas fa-calendar-alt"></i>
            <span class="rv-value" id="reviewRoom">Select a room</span>
        </div>
        <div class="rv-divider"></div>
        <div class="rv-item">
            <i class="fas fa-calendar-day"></i>
            <span><span class="rv-value" id="reviewDates">—</span></span>
        </div>
        <div class="rv-divider"></div>
        <div class="rv-item">
            <i class="fas fa-moon"></i>
            <span><span class="rv-value" id="reviewNights">1</span> night</span>
        </div>
        <div class="rv-divider"></div>
        <div class="rv-item">
            <i class="fas fa-users"></i>
            <span><span class="rv-value" id="reviewGuests">1 Adult</span></span>
        </div>
        <div class="rv-divider"></div>
        <div class="rv-item">
            <i class="fas fa-naira-sign"></i>
            <span class="rv-value" id="reviewTotal">₦0.00</span>
        </div>
    </div>

    <div class="booking-cta" id="bookingCta">
        @if ($useCartFlow)
            <div class="total-row">
                <span class="total-label">Total Amount</span>
                <span class="total-amount">{{ $cart['formatted_total'] }}</span>
            </div>
        @endif
        <button type="submit" id="submitBtn" class="btn btn-brand btn-lg w-100">
            <span id="btnText"><i class="fas fa-lock me-2 lock-icon"></i>Complete Booking</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"
                role="status"></span>
        </button>
        <p class="secure-badge">
            <i class="fas fa-shield-alt"></i>
            Secured with SSL encryption
        </p>
    </div>
</form>
