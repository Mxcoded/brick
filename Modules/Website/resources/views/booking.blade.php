@extends('website::layouts.master')

@section('title', 'Complete Your Reservation')

@section('content')
    <div class="container py-5">
        {{-- Progress Indicator --}}
        @include('website::partials.booking-progress', ['step' => isset($useCart) && $useCart ? 2 : 3])

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-user-circle me-2"></i>Guest Details
                        </h4>
                    </div>
                    <div class="card-body p-4">

                        {{-- Display Session Errors --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- AJAX Availability Alert --}}
                        <div id="availabilityAlert" class="alert alert-warning d-none"></div>

                        <form action="{{ route('website.booking.store') }}" method="POST" id="bookingForm">
                            @csrf

                            @php
                                $useCartFlow = isset($useCart) && $useCart && !empty($cart['items']);
                                $reqRoomTypeId = old('room_type_id', request('room_type_id', request('room_id', $selectedRoomType->id ?? '')));
                                $reqCheckIn = $useCartFlow ? ($cart['check_in'] ?? '') : old('check_in_date', request('check_in_date', request('check_in')));
                                $reqCheckOut = $useCartFlow ? ($cart['check_out'] ?? '') : old('check_out_date', request('check_out_date', request('check_out')));
                                // Determine which guest fields already have stored data (read-only if exists)
                                $hasPhone = Auth::check() && !empty($guest->contact_number);
                                $hasGender = Auth::check() && !empty($guest->gender);
                                $hasAddress = Auth::check() && !empty($guest->home_address);
                                $hasIdType = Auth::check() && !empty($guest->identification_type);
                                $hasIdNumber = Auth::check() && !empty($guest->identification_number);
                                $hasNationality = Auth::check() && !empty($guest->nationality);
                                $hasDob = Auth::check() && !is_null($guest->birthday);
                            @endphp

                            @if($useCartFlow)
                                {{-- CART-BASED BOOKING: Show cart summary instead of room selection --}}
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <strong>{{ $cart['total_rooms'] }} room(s) selected</strong> for 
                                    {{ \Carbon\Carbon::parse($cart['check_in'])->format('M d') }} - 
                                    {{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}
                                    ({{ $cart['nights'] }} {{ Str::plural('night', $cart['nights']) }})
                                    <a href="{{ route('website.book') }}" class="float-end">Modify Selection</a>
                                </div>
                            @else
                                {{-- LEGACY SINGLE-ROOM BOOKING: Dates & Room Type Selection --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Check-In Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="check_in_date" id="check_in_date"
                                            class="form-control form-control-lg" value="{{ $reqCheckIn }}"
                                            min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Check-Out Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="check_out_date" id="check_out_date"
                                            class="form-control form-control-lg" value="{{ $reqCheckOut }}"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Select Room Type <span class="text-danger">*</span></label>
                                    <select name="room_type_id" id="room_type_id" class="form-select form-select-lg" required>
                                        <option value="" disabled {{ empty($reqRoomTypeId) ? 'selected' : '' }}>-- Choose a
                                            Room Type --</option>
                                        @foreach ($roomTypes as $roomOption)
                                            <option value="{{ $roomOption->id }}" data-price="{{ $roomOption->price }}"
                                                data-image="{{ $roomOption->image_url }}" data-name="{{ $roomOption->name }}"
                                                data-capacity="{{ $roomOption->capacity }}"
                                                data-units="{{ $roomOption->units_count }}"
                                                {{ $reqRoomTypeId == $roomOption->id ? 'selected' : '' }}>
                                                {{ $roomOption->name }} (₦{{ number_format($roomOption->price, 2) }}) - {{ $roomOption->units_count }} units
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4" id="roomUnitSection" style="display: none;">
                                    <label class="form-label fw-bold">Select Room Unit <span class="text-muted fw-normal">(Optional)</span></label>
                                    <select name="room_unit_id" id="room_unit_id" class="form-select">
                                        <option value="">-- Auto-assign at check-in --</option>
                                    </select>
                                    <div class="form-text text-muted">Optionally choose a specific room unit (e.g., Room 101, Room 205) or leave blank for auto-assignment at check-in.</div>
                                    <div id="unitLoadingSpinner" class="text-center py-2 d-none">
                                        <span class="spinner-border spinner-border-sm text-primary"></span> Loading available units...
                                    </div>
                                </div>
                            @endif

                            {{-- Personal Information --}}
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white p-4 border-bottom">
                                    <h5 class="fw-bold mb-0 text-dark"><i
                                            class="fas fa-user-circle text-primary me-2"></i>Guest Information</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        {{-- Name & Email --}}
                                        @php
                                            $hasName = Auth::check() && !empty($guest->full_name);
                                            $hasEmail = Auth::check() && !empty($guest->email);
                                        @endphp
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="guest_name" class="form-control {{ $hasName ? 'bg-light text-muted' : '' }}"
                                                value="{{ old('guest_name', $guest->full_name ?? Auth::user()->name ?? '') }}" required
                                                {{ $hasName ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="guest_email" id="guest_email" class="form-control {{ $hasEmail ? 'bg-light text-muted' : '' }}"
                                                value="{{ old('guest_email', $guest->email ?? Auth::user()->email ?? '') }}" required
                                                {{ $hasEmail ? 'readonly' : '' }}>
                                            <div id="emailFeedback" class="invalid-feedback"></div>
                                        </div>

                                        {{-- Phone & Gender (NEW) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="tel" name="guest_phone" class="form-control {{ $hasPhone ? 'bg-light text-muted' : '' }}"
                                                value="{{ old('guest_phone', $guest->contact_number ?? '') }}" required
                                                {{ $hasPhone ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Gender <span
                                                    class="text-danger">*</span></label>
                                            @php $selGender = old('guest_gender', $guest->gender ?? ''); @endphp
                                            @if ($hasGender)
                                                <input type="hidden" name="guest_gender" value="{{ $selGender }}">
                                            @endif
                                            <select name="guest_gender" class="form-select {{ $hasGender ? 'bg-light text-muted' : '' }}" required {{ $hasGender ? 'disabled' : '' }}>
                                                <option value="" disabled {{ empty($selGender) ? 'selected' : '' }}>Select Gender...</option>
                                                <option value="male" {{ $selGender == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ $selGender == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ $selGender == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>

                                        {{-- Address (NEW) --}}
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Home Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="guest_address" class="form-control {{ $hasAddress ? 'bg-light text-muted' : '' }}"
                                                placeholder="Street Address, City, State"
                                                value="{{ old('guest_address', $guest->home_address ?? '') }}" required
                                                {{ $hasAddress ? 'readonly' : '' }}>
                                        </div>
                                        {{-- ✅ NEW: Identity Verification --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">ID Card Type <span
                                                    class="text-danger">*</span></label>
                                            @php $selIdType = old('guest_id_type', $guest->identification_type ?? ''); @endphp
                                            @if ($hasIdType)
                                                <input type="hidden" name="guest_id_type" value="{{ $selIdType }}">
                                            @endif
                                            <select name="guest_id_type" class="form-select {{ $hasIdType ? 'bg-light text-muted' : '' }}" required {{ $hasIdType ? 'disabled' : '' }}>
                                                <option value="" disabled {{ empty($selIdType) ? 'selected' : '' }}>Select ID Type...</option>
                                                <option value="International Passport" {{ $selIdType == 'International Passport' ? 'selected' : '' }}>International Passport</option>
                                                <option value="NIN" {{ $selIdType == 'NIN' ? 'selected' : '' }}>NIN (National ID)</option>
                                                <option value="Drivers License" {{ $selIdType == 'Drivers License' ? 'selected' : '' }}>Driver's License</option>
                                                <option value="Voters Card" {{ $selIdType == 'Voters Card' ? 'selected' : '' }}>Voter's Card</option>
                                                <option value="Other" {{ $selIdType == 'Other' ? 'selected' : '' }}>Other Govt ID</option>
                                            </select>
                                            <div class="form-text text-muted">Present this ID at the front desk for
                                                verification.</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">ID Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="guest_id_number" class="form-control {{ $hasIdNumber ? 'bg-light text-muted' : '' }}"
                                                placeholder="e.g. A01234567" value="{{ old('guest_id_number', $guest->identification_number ?? '') }}" required
                                                {{ $hasIdNumber ? 'readonly' : '' }}>
                                        </div>
                                        {{-- Nationality & DOB (NEW) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nationality/Country <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="guest_nationality" class="form-control {{ $hasNationality ? 'bg-light text-muted' : '' }}"
                                                value="{{ old('guest_nationality', $guest->nationality ?? 'Nigeria') }}" required
                                                {{ $hasNationality ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Date of Birth</label>
                                            <input type="date" name="guest_dob" class="form-control {{ $hasDob ? 'bg-light text-muted' : '' }}"
                                                value="{{ old('guest_dob', $guest->birthday?->format('Y-m-d') ?? '') }}"
                                                {{ $hasDob ? 'readonly' : '' }}>
                                            <div class="form-text text-muted">Required for age verification.</div>
                                        </div>

                                        {{-- Guests Count --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Adults</label>
                                            <input type="number" name="adults" class="form-control"
                                                value="{{ old('adults', 1) }}" min="1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Children</label>
                                            <input type="number" name="children" class="form-control"
                                                value="{{ old('children', 0) }}" min="0">
                                        </div>
                                    </div>

                                    {{-- Create Account Toggle (Keep this logic) --}}
                                    @if (!Auth::check())
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="createAccountToggle"
                                                name="create_account" value="1"
                                                {{ old('create_account') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="createAccountToggle">Create an
                                                account for faster booking next time</label>
                                        </div>
                                        <div class="collapse mt-3 {{ old('create_account') ? 'show' : '' }}"
                                            id="accountFields">
                                            <div class="p-3 bg-light rounded border">
                                                <label class="form-label fw-bold">Choose Password</label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="Min. 8 characters">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            <div class="mb-4">
                                <label class="form-label fw-bold">Special Requests</label>
                                <textarea name="special_requests" class="form-control" rows="3">{{ old('special_requests') }}</textarea>
                            </div>

                            {{-- ✅ NEW: Payment Options --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Payment Option <span
                                        class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check border p-3 rounded w-100">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="pay_now" value="paystack" checked>
                                        <label class="form-check-label fw-bold d-block" for="pay_now">
                                            <i class="fas fa-credit-card text-primary me-2"></i> Pay Now (Secure)
                                            <small class="d-block text-muted fw-normal">Instant confirmation via
                                                Card/Transfer</small>
                                        </label>
                                    </div>
                                    <div class="form-check border p-3 rounded w-100">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="pay_arrival" value="pay_on_arrival">
                                        <label class="form-check-label fw-bold d-block" for="pay_arrival">
                                            <i class="fas fa-hotel text-secondary me-2"></i> Pay upon Check-in
                                            <small class="d-block text-muted fw-normal">Pay at the front desk</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" id="submitBtn"
                                class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                                <span id="btnText"><i class="fas fa-lock me-2"></i>Complete Booking</span>
                                <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                            </button>
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="fas fa-shield-alt me-1"></i> Your payment is secure and encrypted
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 2rem; z-index: 10;">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-receipt me-2"></i>Summary</h5>
                    </div>

                    @if($useCartFlow)
                        {{-- CART-BASED SUMMARY --}}
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span>Check-in</span>
                                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($cart['check_in'])->format('M d, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Check-out</span>
                                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold mb-3">Selected Rooms</h6>
                            @foreach($cart['items'] as $item)
                                <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                                        <div class="text-muted small">{{ $item['quantity'] }} room × {{ $item['nights'] }} nights</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-success">₦{{ number_format($item['subtotal'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                                <div>
                                    <span class="h6 mb-0 text-muted">Total</span>
                                    <div class="small text-muted">{{ $cart['total_rooms'] }} room(s), {{ $cart['nights'] }} nights</div>
                                </div>
                                <span class="h4 mb-0 text-success fw-bold">
                                    {{ $cart['formatted_total'] }}
                                </span>
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i> Rooms assigned at check-in
                            </p>
                        </div>
                    @else
                        {{-- LEGACY SINGLE-ROOM SUMMARY --}}
                        <img id="summary-image" src="{{ $selectedRoomType->image_url ?? asset('images/default-room.jpg') }}"
                            class="card-img-top {{ $selectedRoomType ? '' : 'd-none' }}"
                            style="height: 200px; object-fit: cover;">

                        <div class="card-body p-4">
                            <h5 id="summary-name" class="fw-bold text-primary mb-1">
                                {{ $selectedRoomType->name ?? 'Select a Room Type' }}</h5>
                            <div class="mb-3 text-muted small">
                                <i class="fas fa-user-friends me-1"></i> Max <span
                                    id="summary-capacity">{{ $selectedRoomType->capacity ?? '-' }}</span> Guests
                            </div>

                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                                    <span class="text-muted small">Check-in</span>
                                    <span class="fw-bold"
                                        id="summary-checkin">{{ $reqCheckIn ? \Carbon\Carbon::parse($reqCheckIn)->format('M d, Y') : '...' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                                    <span class="text-muted small">Check-out</span>
                                    <span class="fw-bold"
                                        id="summary-checkout">{{ $reqCheckOut ? \Carbon\Carbon::parse($reqCheckOut)->format('M d, Y') : '...' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                                    <span class="text-muted small">Nights</span>
                                    <span class="fw-bold" id="summary-nights">1</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                                    <span class="text-muted small">Rate</span>
                                    <span id="summary-rate">₦{{ number_format($selectedRoomType->price ?? 0, 2) }}</span>
                                </li>
                            </ul>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <span class="h5 mb-0 text-muted">Total:</span>
                                <span class="h3 mb-0 text-success fw-bold" id="summary-total">
                                    ₦0.00
                                </span>
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i> Specific room assigned at check-in
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ FIX 2: Script moved OUTSIDE @push to guarantee execution --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const roomSelect = document.getElementById('room_type_id');
            const checkInInput = document.getElementById('check_in_date');
            const checkOutInput = document.getElementById('check_out_date');
            const emailInput = document.getElementById('guest_email');
            const emailFeedback = document.getElementById('emailFeedback');
            const accountToggle = document.getElementById('createAccountToggle');

            // Summary Elements
            const summaryName = document.getElementById('summary-name');
            const summaryRate = document.getElementById('summary-rate');
            const summaryTotal = document.getElementById('summary-total');
            const summaryImage = document.getElementById('summary-image');
            const summaryCapacity = document.getElementById('summary-capacity');
            const summaryNights = document.getElementById('summary-nights');
            const summaryCheckIn = document.getElementById('summary-checkin');
            const summaryCheckOut = document.getElementById('summary-checkout');

            const availabilityAlert = document.getElementById('availabilityAlert');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const bookingForm = document.getElementById('bookingForm');

            // Helpers
            const formatMoney = (amount) => '₦' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            const formatDate = (dateString) => {
                if (!dateString) return '...';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            };

            // --- Email Checker (Existing Logic) ---
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value;
                    if (email && email.includes('@')) {
                        fetch('/website/check-email', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                },
                                body: JSON.stringify({
                                    email: email
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.exists) {
                                    emailInput.classList.add('is-invalid');
                                    emailFeedback.style.display = 'block';
                                    emailFeedback.innerHTML =
                                        `<strong>Account found!</strong> <a href="/login">Login here</a> to book faster.`;
                                    if (accountToggle) {
                                        accountToggle.checked = false;
                                        accountToggle.disabled = true;
                                        document.getElementById('accountFields').classList.remove(
                                            'show');
                                    }
                                } else {
                                    emailInput.classList.remove('is-invalid');
                                    emailFeedback.style.display = 'none';
                                    if (accountToggle) accountToggle.disabled = false;
                                }
                            })
                            .catch(() => {});
                    }
                });
            }

            function calculateNights() {
                if (checkInInput.value && checkOutInput.value) {
                    const start = new Date(checkInInput.value);
                    const end = new Date(checkOutInput.value);
                    if (end > start) {
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        return diffDays > 0 ? diffDays : 1;
                    }
                }
                return 1;
            }

            function updateSummary() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];

                summaryCheckIn.textContent = formatDate(checkInInput.value);
                summaryCheckOut.textContent = formatDate(checkOutInput.value);

                const nights = calculateNights();
                summaryNights.textContent = nights;

                if (selectedOption.value) {
                    const price = parseFloat(selectedOption.dataset.price);

                    summaryName.textContent = selectedOption.dataset.name;
                    summaryCapacity.textContent = selectedOption.dataset.capacity;
                    summaryRate.textContent = formatMoney(price);
                    summaryTotal.textContent = formatMoney(price * nights);

                    if (selectedOption.dataset.image) {
                        summaryImage.src = selectedOption.dataset.image;
                        summaryImage.classList.remove('d-none');
                    }

                    // Trigger the AJAX Check
                    checkAvailability();
                    
                    // Load available units
                    loadAvailableUnits();
                }
            }

            function loadAvailableUnits() {
                const roomTypeId = roomSelect.value;
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                const unitSection = document.getElementById('roomUnitSection');
                const unitSelect = document.getElementById('room_unit_id');
                const unitSpinner = document.getElementById('unitLoadingSpinner');

                if (!roomTypeId || !checkIn || !checkOut) {
                    unitSection.style.display = 'none';
                    return;
                }

                // Show section and loading spinner
                unitSection.style.display = 'block';
                unitSpinner.classList.remove('d-none');
                unitSelect.disabled = true;

                const queryParams = new URLSearchParams({
                    room_type_id: roomTypeId,
                    check_in_date: checkIn,
                    check_out_date: checkOut
                });

                fetch(`/website/api/available-units?${queryParams.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    unitSpinner.classList.add('d-none');
                    unitSelect.disabled = false;
                    
                    // Clear existing options except first
                    unitSelect.innerHTML = '<option value="">-- Auto-assign at check-in --</option>';
                    
                    if (data.units && data.units.length > 0) {
                        data.units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = `Room ${unit.room_number}` + (unit.floor ? ` (Floor ${unit.floor})` : '');
                            unitSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No rooms available for these dates';
                        option.disabled = true;
                        unitSelect.appendChild(option);
                    }
                })
                .catch(err => {
                    console.error('Failed to load units:', err);
                    unitSpinner.classList.add('d-none');
                    unitSelect.disabled = false;
                });
            }

            function checkAvailability() {
                const roomTypeId = roomSelect.value;
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;

                // Only check if we have all data
                if (roomTypeId && checkIn && checkOut) {
                    // UI: Show checking state
                    btnText.textContent = 'Checking Availability...';
                    submitBtn.disabled = true;

                    const queryParams = new URLSearchParams({
                        room_type_id: roomTypeId,
                        check_in_date: checkIn,
                        check_out_date: checkOut
                    });

                    fetch(`{{ route('website.room.checkAvailability') }}?${queryParams.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.available === false) {
                                // ❌ UNAVAILABLE: Show Red Alert
                                availabilityAlert.classList.remove('d-none', 'alert-success', 'alert-warning');
                                availabilityAlert.classList.add('alert-danger');

                                availabilityAlert.innerHTML =
                                    `<i class="fas fa-times-circle me-2"></i> ${data.message}`;

                                // Add "Use Dates" button if suggestion exists
                                if (data.suggestion) {
                                    const suggestBtn = document.createElement('button');
                                    suggestBtn.type = 'button';
                                    suggestBtn.className =
                                        'btn btn-sm btn-light text-danger fw-bold mt-2 d-block';
                                    suggestBtn.innerHTML =
                                        `Use Available: ${formatDate(data.suggestion.check_in)} - ${formatDate(data.suggestion.check_out)}`;

                                    suggestBtn.onclick = function() {
                                        checkInInput.value = data.suggestion.check_in;
                                        checkOutInput.value = data.suggestion.check_out;
                                        updateSummary(); // Re-trigger check
                                    };
                                    availabilityAlert.appendChild(suggestBtn);
                                }

                                submitBtn.classList.add('btn-secondary');
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.disabled = true; // Keep disabled
                                btnText.textContent = 'Room Unavailable';

                            } else {
                                // ✅ AVAILABLE: Hide Alert & Enable Button
                                availabilityAlert.classList.add('d-none');

                                submitBtn.disabled = false;
                                submitBtn.classList.add('btn-primary');
                                submitBtn.classList.remove('btn-secondary');
                                btnText.textContent = 'Confirm & Pay Reservation';
                            }
                        })
                        .catch(err => {
                            console.error('Check failed:', err);
                            // On error (e.g. network), we default to allowing the attempt so the backend can validate
                            submitBtn.disabled = false;
                            btnText.textContent = 'Confirm & Pay Reservation';
                        });
                }
            }

            // Listeners
            roomSelect.addEventListener('change', updateSummary);
            checkInInput.addEventListener('change', updateSummary);
            checkOutInput.addEventListener('change', updateSummary);

            bookingForm.addEventListener('submit', function() {
                if (!submitBtn.disabled) {
                    submitBtn.disabled = true;
                    btnText.textContent = 'Processing...';
                    btnSpinner.classList.remove('d-none');
                }
            });

            if (accountToggle) {
                accountToggle.addEventListener('change', function() {
                    document.getElementById('accountFields').classList.toggle('show', this.checked);
                });
            }

            // Initial Run
            if (roomSelect.value) updateSummary();
        });
    </script>
@endsection
