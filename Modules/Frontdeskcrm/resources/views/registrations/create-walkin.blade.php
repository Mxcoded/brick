@extends('layouts.master')

@section('title', 'New Walk-in / Reservation')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header border-0 rounded-top-3 py-3"
                        style="background: linear-gradient(135deg, #C8A165 0%, #b08c54 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3">
                                <i class="fas fa-walking fa-lg" style="color: #C8A165;"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white fw-bold">Walk-in & Reservation</h4>
                                <p class="mb-0 text-white opacity-75 small">Create an immediate check-in or future reservation</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('frontdesk.registrations.storeWalkin') }}" method="POST">
                            @csrf

                            {{-- Success Message --}}
                            @if (session('success'))
                                <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
                                    <i class="fas fa-check-circle text-success me-2"></i> {{ session('success') }}
                                </div>
                            @endif

                            {{-- Error Block --}}
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong class="fw-bold">Please fix the errors:</strong>
                                    </div>
                                    <ul class="mb-0 ps-3 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- SECTION 1: GUEST DETAILS --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-user text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Guest Details</h5>
                                </div>

                                <div class="row g-3">
                                    {{-- Phone (Lookup) --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Contact Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="tel" class="form-control" name="contact_number" value="{{ old('contact_number') }}" required placeholder="Search phone...">
                                        </div>
                                    </div>

                                    {{-- Full Name --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                            <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                                        </div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Email</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                                    </div>

                                    {{-- Gender --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Gender</label>
                                        <select class="form-select" name="gender">
                                            <option value="" selected disabled>Select...</option>
                                            <option value="male" @selected(old('gender') == 'male')>Male</option>
                                            <option value="female" @selected(old('gender') == 'female')>Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: STAY & ROOM DETAILS --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-bed text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Reservation Details</h5>
                                </div>

                                <div class="row g-3">
                                    {{-- Dates --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Check-in <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="check_in" value="{{ old('check_in', now()->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Check-out <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="check_out" value="{{ old('check_out', now()->addDay()->format('Y-m-d')) }}" required>
                                    </div>

                                    {{-- Number of Guests (NEW) --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Guests <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-users text-muted"></i></span>
                                            <input type="number" class="form-control" name="no_of_guests" value="{{ old('no_of_guests', 1) }}" min="1" max="10" required>
                                        </div>
                                    </div>

                                    {{-- Room Selection (NEW) --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Select Room (Optional)</label>
                                        <select class="form-select @error('room_id') is-invalid @enderror" name="room_id">
                                            <option value="">-- Assign Later / Draft --</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                                    {{ $room->name }} (₦{{ number_format($room->price) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('room_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Leave blank to assign a room later.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-gold px-4 fw-bold">
                                    <i class="fas fa-check me-2"></i> Save Reservation / Check-in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('input[name="contact_number"]');
            const nameInput = document.querySelector('input[name="full_name"]');
            const emailInput = document.querySelector('input[name="email"]');
            const genderSelect = document.querySelector('select[name="gender"]');

            // Create a feedback element
            const feedbackMsg = document.createElement('div');
            feedbackMsg.className = 'form-text fw-bold mt-1';
            feedbackMsg.style.display = 'none';
            phoneInput.parentNode.appendChild(feedbackMsg);

            let typingTimer;

            phoneInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                feedbackMsg.style.display = 'none';

                // Wait for user to stop typing for 500ms
                typingTimer = setTimeout(performLookup, 800);
            });

            function performLookup() {
                const phone = phoneInput.value;
                if (phone.length < 10) return; // Too short to be valid

                // Show loading indicator
                feedbackMsg.textContent = 'Checking guest records...';
                feedbackMsg.className = 'form-text text-muted mt-1';
                feedbackMsg.style.display = 'block';

                fetch(`{{ route('frontdesk.registrations.lookup') }}?contact_number=${encodeURIComponent(phone)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.found) {
                            // SUCCESS: Returning Guest Found
                            feedbackMsg.innerHTML =
                                '<i class="fas fa-check-circle text-success me-1"></i> Returning guest found! Details auto-filled.';
                            feedbackMsg.className = 'form-text text-success fw-bold mt-1';

                            // Auto-fill fields with a nice highlight effect
                            fillAndHighlight(nameInput, data.guest.full_name);
                            fillAndHighlight(emailInput, data.guest.email);

                            if (data.guest.gender) {
                                genderSelect.value = data.guest.gender;
                                genderSelect.classList.add('bg-success', 'bg-opacity-10');
                                setTimeout(() => genderSelect.classList.remove('bg-success', 'bg-opacity-10'),
                                    1500);
                            }

                        } else {
                            // Not Found (New Guest)
                            feedbackMsg.innerHTML =
                                '<i class="fas fa-user-plus text-muted me-1"></i> New guest. Please complete all fields.';
                            feedbackMsg.className = 'form-text text-muted mt-1';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        feedbackMsg.style.display = 'none';
                    });
            }

            function fillAndHighlight(input, value) {
                if (value) {
                    input.value = value;
                    // Visual feedback
                    input.classList.add('is-valid', 'bg-success', 'bg-opacity-10');
                    setTimeout(() => {
                        input.classList.remove('is-valid', 'bg-success', 'bg-opacity-10');
                    }, 2000);
                }
            }
        });
    </script>
@endsection
