@extends('layouts.master')

@section('title', 'Walk-in Check-In / Reservation')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-11">
                @if (session('success'))
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
                        <i class="fas fa-check-circle text-success me-2"></i> {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong class="fw-bold">Please fix the following errors:</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('frontdesk.registrations.storeWalkin') }}" method="POST">
                    @csrf

                    {{-- ===== STEP 1: GUEST SEARCH ===== --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-search text-gold"></i>
                                </div>
                                <h5 class="mb-0 text-dark fw-bold">Find or Create Guest</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold text-dark mb-1">
                                        <i class="fas fa-phone-alt text-gold me-1"></i> Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="tel" class="form-control form-control-lg" name="contact_number"
                                            id="phoneLookup" value="{{ old('contact_number') }}" required
                                            placeholder="080 XXX XXX XX">
                                    </div>
                                    <div id="lookupFeedback" class="form-text mt-1"></div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold text-dark mb-1">
                                        <i class="fas fa-envelope text-gold me-1"></i> Email (Optional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" name="email" id="guestEmail"
                                            value="{{ old('email') }}" placeholder="guest@example.com">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-gold w-100" id="lookupBtn" disabled>
                                        <i class="fas fa-search me-1"></i> Lookup
                                    </button>
                                </div>
                            </div>
                            <div id="returningGuestCard" class="mt-3 d-none">
                                <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 py-2 px-3 mb-0 d-flex align-items-center justify-content-between">
                                    <span><i class="fas fa-check-circle text-success me-2"></i> Returning guest <strong id="returningGuestName"></strong></span>
                                    <small class="text-muted">Fields auto-filled from profile</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== STEP 2: GUEST INFORMATION ===== --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-user-circle text-gold"></i>
                                </div>
                                <h5 class="mb-0 text-dark fw-bold">Guest Information</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Title</label>
                                    <select class="form-select" name="title">
                                        <option value="">--</option>
                                        <option value="Mr." @selected(old('title') == 'Mr.')>Mr.</option>
                                        <option value="Mrs." @selected(old('title') == 'Mrs.')>Mrs.</option>
                                        <option value="Ms." @selected(old('title') == 'Ms.')>Ms.</option>
                                        <option value="Dr." @selected(old('title') == 'Dr.')>Dr.</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" id="guestName"
                                        value="{{ old('full_name') }}" required placeholder="Surname, First name">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" name="gender" id="guestGender" required>
                                        <option value="">Select</option>
                                        <option value="male" @selected(old('gender') == 'male')>Male</option>
                                        <option value="female" @selected(old('gender') == 'female')>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Nationality</label>
                                    <input type="text" class="form-control" name="nationality"
                                        value="{{ old('nationality', 'Nigerian') }}" placeholder="e.g. Nigerian">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Date of Birth</label>
                                    <input type="date" class="form-control" name="birthday" value="{{ old('birthday') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Occupation</label>
                                    <input type="text" class="form-control" name="occupation" value="{{ old('occupation') }}" placeholder="e.g. Engineer">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Company / Organization</label>
                                    <input type="text" class="form-control" name="company_name" value="{{ old('company_name') }}" placeholder="Company name">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">ID Type</label>
                                    <select class="form-select" name="identification_type">
                                        <option value="">-- Select --</option>
                                        <option value="International Passport" @selected(old('identification_type') == 'International Passport')>International Passport</option>
                                        <option value="National ID (NIN)" @selected(old('identification_type') == 'National ID (NIN)')>National ID (NIN)</option>
                                        <option value="Driver's License" @selected(old('identification_type') == "Driver's License")>Driver's License</option>
                                        <option value="Voter's Card" @selected(old('identification_type') == "Voter's Card")>Voter's Card</option>
                                        <option value="Other" @selected(old('identification_type') == 'Other')>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">ID Number</label>
                                    <input type="text" class="form-control" name="identification_number"
                                        value="{{ old('identification_number') }}" placeholder="ID document number">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Home Address</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="home_address"
                                                value="{{ old('home_address') }}" placeholder="Street address">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="city"
                                                value="{{ old('city') }}" placeholder="City">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="state"
                                                value="{{ old('state') }}" placeholder="State">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="zip_code"
                                                value="{{ old('zip_code') }}" placeholder="ZIP">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== STEP 3: EMERGENCY CONTACT ===== --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-shield-alt text-gold"></i>
                                </div>
                                <h5 class="mb-0 text-dark fw-bold">Emergency Contact</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark">Full Name</label>
                                    <input type="text" class="form-control" name="emergency_name"
                                        value="{{ old('emergency_name') }}" placeholder="Emergency contact name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark">Relationship</label>
                                    <input type="text" class="form-control" name="emergency_relationship"
                                        value="{{ old('emergency_relationship') }}" placeholder="e.g. Spouse, Parent">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark">Phone Number</label>
                                    <input type="tel" class="form-control" name="emergency_contact"
                                        value="{{ old('emergency_contact') }}" placeholder="Emergency phone">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== STEP 4: RESERVATION DETAILS ===== --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-calendar-check text-gold"></i>
                                </div>
                                <h5 class="mb-0 text-dark fw-bold">Reservation Details</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-sign-in-alt text-gold me-1"></i> Check-In <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-lg" name="check_in"
                                        id="checkIn" value="{{ old('check_in', now()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-sign-out-alt text-gold me-1"></i> Check-Out <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-lg" name="check_out"
                                        id="checkOut" value="{{ old('check_out', now()->addDay()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Nights</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-moon text-muted"></i></span>
                                        <input type="text" class="form-control" id="nightCount"
                                            value="{{ \Carbon\Carbon::parse(old('check_in', now()->format('Y-m-d')))->diffInDays(\Carbon\Carbon::parse(old('check_out', now()->addDay()->format('Y-m-d')))) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-info-circle text-muted"></i></span>
                                        <input type="text" class="form-control bg-light" id="stayStatusPreview" value="Today: Check-In" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-bed text-gold me-1"></i> Room Type
                                    </label>
                                    <select class="form-select" name="room_type_id" id="roomTypeSelect">
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $rt)
                                            <option value="{{ $rt->id }}"
                                                data-price="{{ $rt->price }}"
                                                data-capacity="{{ $rt->capacity }}"
                                                @selected(old('room_type_id') == $rt->id)>
                                                {{ $rt->name }} (₦{{ number_format($rt->price) }}/night)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-door-closed text-gold me-1"></i> Room Unit
                                    </label>
                                    <select class="form-select" name="room_unit_id" id="roomUnitSelect">
                                        <option value="">-- Auto Assign / Select Room --</option>
                                    </select>
                                    <div class="form-text mt-1">Leave empty for auto-assignment at finalization</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-tag text-gold me-1"></i> Room Rate (₦/night)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₦</span>
                                        <input type="number" class="form-control" name="room_rate" id="roomRate"
                                            value="{{ old('room_rate') }}" min="0" step="100"
                                            placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">
                                        <i class="fas fa-users text-gold me-1"></i> Number of Guests <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user-friends text-muted"></i></span>
                                        <input type="number" class="form-control" name="no_of_guests"
                                            id="guestsCount" value="{{ old('no_of_guests', 1) }}" min="1" max="20" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Billing Type</label>
                                    <select class="form-select" name="billing_type">
                                        <option value="consolidate" @selected(old('billing_type', 'consolidate') == 'consolidate')>Consolidated (Single Bill)</option>
                                        <option value="split" @selected(old('billing_type') == 'split')>Split by Guest</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Payment Method</label>
                                    <select class="form-select" name="payment_method">
                                        <option value="">-- Select --</option>
                                        <option value="cash" @selected(old('payment_method') == 'cash')>Cash</option>
                                        <option value="card" @selected(old('payment_method') == 'card')>Card</option>
                                        <option value="transfer" @selected(old('payment_method') == 'transfer')>Bank Transfer</option>
                                        <option value="pos" @selected(old('payment_method') == 'pos')>POS</option>
                                        <option value="invoice" @selected(old('payment_method') == 'invoice')>Invoice / Company</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">&nbsp;</label>
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="bed_breakfast" value="1" id="bedBreakfast" @checked(old('bed_breakfast'))>
                                        <label class="form-check-label fw-semibold text-dark" for="bedBreakfast">
                                            <i class="fas fa-coffee text-gold me-1"></i> Bed & Breakfast
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">&nbsp;</label>
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="opt_in_data_save" value="1" id="optInData" @checked(old('opt_in_data_save', true))>
                                        <label class="form-check-label fw-semibold text-dark" for="optInData">
                                            <i class="fas fa-save text-gold me-1"></i> Save guest profile
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Discount & Deposit Row --}}
                            <div class="row g-3 mt-2">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark"><i class="fas fa-tag text-gold me-1"></i> Discount Type</label>
                                    <select class="form-select" name="discount_type">
                                        <option value="">No Discount</option>
                                        <option value="percentage" @selected(old('discount_type') == 'percentage')>Percentage (%)</option>
                                        <option value="fixed" @selected(old('discount_type') == 'fixed')>Fixed Amount (₦)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Discount Value</label>
                                    <input type="number" class="form-control" name="discount_value" value="{{ old('discount_value') }}" min="0" step="0.01" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Discount %</label>
                                    <input type="number" class="form-control" name="discount_percent" value="{{ old('discount_percent') }}" min="0" max="100" step="0.01" placeholder="0%">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Reason</label>
                                    <input type="text" class="form-control" name="discount_reason" value="{{ old('discount_reason') }}" placeholder="e.g. Corporate rate">
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="deposit_required" value="1" id="depositRequired" @checked(old('deposit_required'))>
                                        <label class="form-check-label fw-semibold text-dark" for="depositRequired">
                                            <i class="fas fa-hand-holding-usd text-gold me-1"></i> Deposit Required
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Deposit Amount (₦)</label>
                                    <input type="number" class="form-control" name="deposit_amount" value="{{ old('deposit_amount') }}" min="0" step="100" placeholder="0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark">Deposit Deadline</label>
                                    <input type="date" class="form-control" name="deposit_deadline" value="{{ old('deposit_deadline') }}">
                                </div>
                            </div>

                            {{-- Total Preview --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-md-6 offset-md-6">
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted">Room Rate:</span>
                                            <span class="fw-bold" id="previewRate">₦0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted">Nights:</span>
                                            <span class="fw-bold" id="previewNights">0</span>
                                        </div>
                                        <hr class="my-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark">Estimated Total:</span>
                                            <span class="fw-bold fs-5 text-gold" id="previewTotal">₦0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== ACTIONS ===== --}}
                    <div class="d-flex justify-content-between align-items-center gap-3 pt-3">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="statusDescription">A walk-in check-in will be created and ready for room assignment.</span>
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-light px-4 border">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-gold px-5 fw-bold">
                                <i class="fas fa-check-circle me-2"></i> <span id="submitBtnText">Check In Now</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ==================== ELEMENTS ====================
            const phoneInput = document.getElementById('phoneLookup');
            const lookupBtn = document.getElementById('lookupBtn');
            const lookupFeedback = document.getElementById('lookupFeedback');
            const returningGuestCard = document.getElementById('returningGuestCard');
            const returningGuestName = document.getElementById('returningGuestName');
            const nameInput = document.getElementById('guestName');
            const emailInput = document.getElementById('guestEmail');
            const genderSelect = document.getElementById('guestGender');

            const checkInInput = document.getElementById('checkIn');
            const checkOutInput = document.getElementById('checkOut');
            const nightCount = document.getElementById('nightCount');
            const stayStatusPreview = document.getElementById('stayStatusPreview');
            const statusDescription = document.getElementById('statusDescription');
            const submitBtnText = document.getElementById('submitBtnText');

            const roomTypeSelect = document.getElementById('roomTypeSelect');
            const roomUnitSelect = document.getElementById('roomUnitSelect');
            const roomRateInput = document.getElementById('roomRate');

            const previewRate = document.getElementById('previewRate');
            const previewNights = document.getElementById('previewNights');
            const previewTotal = document.getElementById('previewTotal');

            let roomTypesData = @json($roomTypesJson ?? []);

            // ==================== PHONE LOOKUP ====================
            let lookupTimer;

            phoneInput.addEventListener('input', function() {
                clearTimeout(lookupTimer);
                lookupBtn.disabled = phoneInput.value.length < 6;

                if (phoneInput.value.length >= 10) {
                    lookupTimer = setTimeout(performLookup, 700);
                } else {
                    lookupFeedback.innerHTML = '';
                    returningGuestCard.classList.add('d-none');
                }
            });

            lookupBtn.addEventListener('click', performLookup);

            function performLookup() {
                const phone = phoneInput.value.trim();
                if (phone.length < 6) return;

                lookupBtn.disabled = true;
                lookupBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Searching...';
                lookupFeedback.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Checking guest records...</span>';

                fetch(`{{ route('frontdesk.registrations.lookup') }}?contact_number=${encodeURIComponent(phone)}`)
                    .then(response => response.json())
                    .then(data => {
                        lookupBtn.innerHTML = '<i class="fas fa-search me-1"></i> Lookup';
                        lookupBtn.disabled = false;

                        if (data.found) {
                            lookupFeedback.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Returning guest found</span>';
                            returningGuestName.textContent = data.guest.full_name;
                            returningGuestCard.classList.remove('d-none');

                            // Auto-fill all profile fields
                            const fields = [
                                { el: nameInput, val: data.guest.full_name },
                                { el: emailInput, val: data.guest.email },
                                { el: genderSelect, val: data.guest.gender },
                                { el: document.querySelector('select[name="title"]'), val: data.guest.title },
                                { el: document.querySelector('input[name="nationality"]'), val: data.guest.nationality },
                                { el: document.querySelector('input[name="home_address"]'), val: data.guest.home_address },
                                { el: document.querySelector('input[name="occupation"]'), val: data.guest.occupation },
                                { el: document.querySelector('input[name="company_name"]'), val: data.guest.company_name },
                                { el: document.querySelector('select[name="identification_type"]'), val: data.guest.identification_type },
                                { el: document.querySelector('input[name="identification_number"]'), val: data.guest.identification_number },
                                { el: document.querySelector('input[name="birthday"]'), val: data.guest.birthday },
                            ];
                            fields.forEach(function(f) {
                                if (f.el && f.val) {
                                    f.el.value = f.val;
                                    highlightField(f.el);
                                }
                            });
                        } else {
                            lookupFeedback.innerHTML = '<span class="text-muted"><i class="fas fa-user-plus me-1"></i> New guest — complete all fields below</span>';
                            returningGuestCard.classList.add('d-none');
                        }
                    })
                    .catch(() => {
                        lookupBtn.innerHTML = '<i class="fas fa-search me-1"></i> Lookup';
                        lookupBtn.disabled = false;
                        lookupFeedback.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> Lookup failed</span>';
                    });
            }

            function highlightField(el) {
                el.classList.add('bg-success', 'bg-opacity-10');
                setTimeout(() => el.classList.remove('bg-success', 'bg-opacity-10'), 2000);
            }

            // ==================== DATE LOGIC ====================
            function updateDateSummary() {
                const ckin = checkInInput.value;
                const ckout = checkOutInput.value;

                if (ckin && ckout && ckout > ckin) {
                    const d1 = new Date(ckin + 'T00:00:00');
                    const d2 = new Date(ckout + 'T00:00:00');
                    const nights = Math.round((d2 - d1) / (86400000));
                    nightCount.value = nights;

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (d1.getTime() === today.getTime()) {
                        stayStatusPreview.value = 'Today: Check-In';
                        stayStatusPreview.className = 'form-control text-success fw-bold bg-light';
                        statusDescription.textContent = 'This will be an immediate walk-in check-in. Proceed to room assignment after saving.';
                        submitBtnText.textContent = 'Check In Now';
                    } else if (d1 > today) {
                        stayStatusPreview.value = 'Future: Reserved';
                        stayStatusPreview.className = 'form-control text-info fw-bold bg-light';
                        statusDescription.textContent = 'This is a future reservation. It will be saved as a reserved booking.';
                        submitBtnText.textContent = 'Save Reservation';
                    } else {
                        stayStatusPreview.value = 'Past: Late Arrival';
                        stayStatusPreview.className = 'form-control text-warning fw-bold bg-light';
                        statusDescription.textContent = 'Check-in is in the past. A late arrival will be created.';
                        submitBtnText.textContent = 'Check In (Late)';
                    }
                } else {
                    nightCount.value = '--';
                    stayStatusPreview.value = 'Pending dates';
                    stayStatusPreview.className = 'form-control bg-light';
                }
                updateTotal();
            }

            checkInInput.addEventListener('change', updateDateSummary);
            checkOutInput.addEventListener('change', updateDateSummary);
            updateDateSummary();

            // ==================== ROOM TYPE to UNIT DYNAMIC DROPDOWN ====================
            roomTypeSelect.addEventListener('change', function() {
                const rtId = this.value;
                roomUnitSelect.innerHTML = '<option value="">-- Select Room Unit --</option>';
                roomRateInput.value = '';

                if (!rtId || !roomTypesData[rtId]) return;

                const rt = roomTypesData[rtId];
                roomRateInput.value = rt.price;

                let hasAvailable = false;
                rt.units.forEach(function(unit) {
                    const isAvailable = unit.status === 'available' || unit.status === 'booked';
                    if (isAvailable) {
                        hasAvailable = true;
                        const opt = document.createElement('option');
                        opt.value = unit.id;
                        opt.textContent = 'Room ' + unit.room_number;
                        roomUnitSelect.appendChild(opt);
                    }
                });

                if (!hasAvailable) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.disabled = true;
                    opt.textContent = 'No available units for this type';
                    roomUnitSelect.appendChild(opt);
                }

                updateTotal();
            });

            roomRateInput.addEventListener('input', updateTotal);

            // ==================== TOTAL PREVIEW ====================
            function updateTotal() {
                const rate = parseFloat(roomRateInput.value) || 0;
                const nights = parseInt(nightCount.value) || 0;
                const total = rate * nights;

                previewRate.textContent = '\u20A6' + rate.toLocaleString('en-US', {minimumFractionDigits: 2});
                previewNights.textContent = nights;
                previewTotal.textContent = '\u20A6' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
            }

            updateTotal();

            // ==================== FORM VALIDATION ENHANCEMENT ====================
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                requiredFields.forEach(function(f) {
                    if (!f.value.trim()) {
                        f.classList.add('is-invalid');
                        valid = false;
                    } else {
                        f.classList.remove('is-invalid');
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
            });

            form.querySelectorAll('[required]').forEach(function(f) {
                f.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
@endsection