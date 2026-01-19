@extends('website::layouts.master')

@section('title', 'Complete Your Reservation')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h4 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-user-circle me-2"></i>Guest Details
                    </h4>
                </div>
                <div class="card-body p-4">
                    
                    {{-- ✅ FIX 1: Display Session Errors (The "Silent Return" Fix) --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- AJAX Availability Alert --}}
                    <div id="availabilityAlert" class="alert alert-warning d-none"></div>

                    <form action="{{ route('website.booking.store') }}" method="POST" id="bookingForm">
                        @csrf
                        
                        @php
                            $reqRoomId = old('room_id', request('room_id', $selectedRoom->id ?? ''));
                            $reqCheckIn = old('check_in_date', request('check_in_date', request('check_in')));
                            $reqCheckOut = old('check_out_date', request('check_out_date', request('check_out')));
                        @endphp

                        {{-- Dates Section --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Check-In Date <span class="text-danger">*</span></label>
                                <input type="date" name="check_in_date" id="check_in_date" class="form-control form-control-lg" 
                                       value="{{ $reqCheckIn }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Check-Out Date <span class="text-danger">*</span></label>
                                <input type="date" name="check_out_date" id="check_out_date" class="form-control form-control-lg" 
                                       value="{{ $reqCheckOut }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>
                        </div>

                        {{-- Room Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Room <span class="text-danger">*</span></label>
                            <select name="room_id" id="room_id" class="form-select form-select-lg" required>
                                <option value="" disabled {{ empty($reqRoomId) ? 'selected' : '' }}>-- Choose a Room --</option>
                                @foreach($rooms as $roomOption)
                                    <option value="{{ $roomOption->id }}" 
                                        data-price="{{ $roomOption->price }}"
                                        data-image="{{ $roomOption->image_url }}"
                                        data-name="{{ $roomOption->name }}"
                                        data-capacity="{{ $roomOption->capacity }}"
                                        {{ $reqRoomId == $roomOption->id ? 'selected' : '' }}>
                                        {{ $roomOption->name }} (₦{{ number_format($roomOption->price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Personal Information --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" class="form-control form-control-lg" 
                                       value="{{ old('guest_name', Auth::user()->name ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="guest_email" id="guest_email" class="form-control form-control-lg" 
                                       value="{{ old('guest_email', Auth::user()->email ?? '') }}" required>
                                <div id="emailFeedback" class="invalid-feedback" style="display: none; font-size: 0.95rem;"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="guest_phone" class="form-control form-control-lg" 
                                       value="{{ old('guest_phone', Auth::user()->guestProfile->phone ?? '') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Adults <span class="text-danger">*</span></label>
                                <select name="adults" class="form-select form-select-lg" required>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('adults') == $i ? 'selected' : '' }}>{{ $i }} Adult{{ $i>1?'s':'' }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Children</label>
                                <select name="children" class="form-select form-select-lg">
                                    <option value="0">None</option>
                                    @for($i = 1; $i <= 4; $i++)
                                        <option value="{{ $i }}" {{ old('children') == $i ? 'selected' : '' }}>{{ $i }} Child{{ $i>1?'ren':'' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Account Creation --}}
                        @guest
                            <div class="bg-light p-4 rounded-3 mb-4 border">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="createAccountToggle" name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="createAccountToggle">
                                        Create an account to manage my booking
                                    </label>
                                </div>
                                <div class="collapse {{ old('create_account') ? 'show' : '' }} mt-3" id="accountFields">
                                    <div class="form-group">
                                        <label class="form-label small text-muted text-uppercase fw-bold">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters">
                                    </div>
                                </div>
                            </div>
                        @endguest

                        <div class="mb-4">
                            <label class="form-label fw-bold">Special Requests</label>
                            <textarea name="special_requests" class="form-control" rows="3">{{ old('special_requests') }}</textarea>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                            <span id="btnText">Confirm & Pay Reservation</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>
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
                
                <img id="summary-image" src="{{ $selectedRoom->image_url ?? asset('images/default-room.jpg') }}" 
                     class="card-img-top {{ $selectedRoom ? '' : 'd-none' }}" 
                     style="height: 200px; object-fit: cover;">
                
                <div class="card-body p-4">
                    <h5 id="summary-name" class="fw-bold text-primary mb-1">{{ $selectedRoom->name ?? 'Select a Room' }}</h5>
                    <div class="mb-3 text-muted small">
                        <i class="fas fa-user-friends me-1"></i> Max <span id="summary-capacity">{{ $selectedRoom->capacity ?? '-' }}</span> Guests
                    </div>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                            <span class="text-muted small">Check-in</span>
                            <span class="fw-bold" id="summary-checkin">{{ $reqCheckIn ? \Carbon\Carbon::parse($reqCheckIn)->format('M d, Y') : '...' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                            <span class="text-muted small">Check-out</span>
                            <span class="fw-bold" id="summary-checkout">{{ $reqCheckOut ? \Carbon\Carbon::parse($reqCheckOut)->format('M d, Y') : '...' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                            <span class="text-muted small">Nights</span>
                            <span class="fw-bold" id="summary-nights">1</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                            <span class="text-muted small">Rate</span>
                            <span id="summary-rate">₦{{ number_format($selectedRoom->price ?? 0, 2) }}</span>
                        </li>
                    </ul>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="h5 mb-0 text-muted">Total:</span>
                        <span class="h3 mb-0 text-success fw-bold" id="summary-total">
                            ₦0.00
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ FIX 2: Script moved OUTSIDE @push to guarantee execution --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Booking Script Loaded"); // Debug check

        // Elements
        const roomSelect = document.getElementById('room_id');
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
            if(!dateString) return '...';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        };

        // --- Email Checker ---
        if(emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value;
                if(email && email.includes('@')) {
                    // Note: Ensure this route exists in your web.php or validation will fail silently
                    // We assume website.checkEmail is defined as previously discussed
                    fetch('/website/check-email', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ email: email })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.exists) {
                            emailInput.classList.add('is-invalid');
                            emailFeedback.style.display = 'block';
                            emailFeedback.innerHTML = `<strong>Account found!</strong> <a href="/login">Login here</a> to book faster.`;
                            if(accountToggle) {
                                accountToggle.checked = false;
                                accountToggle.disabled = true;
                                document.getElementById('accountFields').classList.remove('show');
                            }
                        } else {
                            emailInput.classList.remove('is-invalid');
                            emailFeedback.style.display = 'none';
                            if(accountToggle) accountToggle.disabled = false;
                        }
                    })
                    .catch(err => console.log('Email check skipped (route might be missing)'));
                }
            });
        }

        function calculateNights() {
            if (checkInInput.value && checkOutInput.value) {
                const start = new Date(checkInInput.value);
                const end = new Date(checkOutInput.value);
                if(end > start) {
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
                
                if(selectedOption.dataset.image) {
                    summaryImage.src = selectedOption.dataset.image;
                    summaryImage.classList.remove('d-none');
                }
                
                checkAvailability();
            }
        }

        function checkAvailability() {
            const roomId = roomSelect.value;
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (roomId && checkIn && checkOut) {
                submitBtn.disabled = true;
                availabilityAlert.classList.add('d-none');

                const queryParams = new URLSearchParams({
                    room_id: roomId,
                    check_in_date: checkIn, // Controller expects check_in_date
                    check_out_date: checkOut // Controller expects check_out_date
                });

                // ✅ FIX: Added 'Accept': 'application/json'
                fetch(`{{ route('website.room.checkAvailability') }}?${queryParams.toString()}`, {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' 
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if(data.available === false) {
                        // ✅ Room is NOT available
                        availabilityAlert.classList.remove('d-none');
                        availabilityAlert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
                        submitBtn.classList.add('btn-secondary');
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.disabled = true; // Keep disabled
                    } else {
                        // ✅ Room IS available
                        submitBtn.disabled = false; // Enable
                        submitBtn.classList.add('btn-primary');
                        submitBtn.classList.remove('btn-secondary');
                    }
                })
                .catch(err => {
                    console.error('Check failed:', err);
                    submitBtn.disabled = false; // Allow try anyway on error
                });
            }
        }

        // Listeners
        roomSelect.addEventListener('change', updateSummary);
        checkInInput.addEventListener('change', updateSummary);
        checkOutInput.addEventListener('change', updateSummary);
        
        bookingForm.addEventListener('submit', function() {
            if(!submitBtn.disabled) {
                submitBtn.disabled = true;
                btnText.textContent = 'Processing...';
                btnSpinner.classList.remove('d-none');
            }
        });
        
        if(accountToggle) {
            accountToggle.addEventListener('change', function() {
                document.getElementById('accountFields').classList.toggle('show', this.checked);
            });
        }
        
        // Initial Run
        if(roomSelect.value) updateSummary();
    });
</script>
@endsection