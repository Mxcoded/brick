@extends('layouts.master')

@section('title', 'New Walk-in Registration')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header border-0 rounded-top-3 py-3"
                        style="background: linear-gradient(135deg, #C8A165 0%, #b08c54 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3">
                                <i class="fas fa-walking fa-lg" style="color: #C8A165;"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white fw-bold">Create New Walk-in Guest</h4>
                                <p class="mb-0 text-white opacity-75 small">Fill in guest details to create a draft
                                    registration</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('frontdesk.registrations.storeWalkin') }}" method="POST">
                            @csrf

                            {{-- Success Message --}}
                            @if (session('success'))
                                <div
                                    class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        {{ session('success') }}
                                    </div>
                                </div>
                            @endif

                            {{-- Error Message Block --}}
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4"
                                    role="alert">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong class="fw-bold">Please fix the errors:</strong>
                                    </div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Guest Details Section --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-user text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Guest Details</h5>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_number" class="form-label fw-semibold text-dark">Contact Number
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-phone text-muted"></i>
                                        </span>
                                        <input type="tel" id="contact_number_input"
                                            class="form-control @error('contact_number') is-invalid @enderror"
                                            name="contact_number" value="{{ old('contact_number') }}" required
                                            placeholder="+234...">
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="full_name" class="form-label fw-semibold text-dark">Full Name <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text"
                                                class="form-control @error('full_name') is-invalid @enderror"
                                                name="full_name" value="{{ old('full_name') }}" required
                                                placeholder="Enter guest's full name">
                                            @error('full_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-envelope text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" placeholder="optional@email.com">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="gender" class="form-label fw-semibold text-dark">Gender</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                            <option value="" selected disabled>Select Gender...</option>
                                            <option value="male" @selected(old('gender') == 'male')>Male</option>
                                            <option value="female" @selected(old('gender') == 'female')>Female</option>
                                            <option value="other" @selected(old('gender') == 'other')>Other</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Stay Details Section --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-calendar text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Stay Details</h5>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="check_in" class="form-label fw-semibold text-dark">Check-in Date <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-sign-in-alt text-muted"></i>
                                            </span>
                                            <input type="date"
                                                class="form-control @error('check_in') is-invalid @enderror" name="check_in"
                                                value="{{ old('check_in', now()->format('Y-m-d')) }}" required>
                                            @error('check_in')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="check_out" class="form-label fw-semibold text-dark">Check-out Date <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-sign-out-alt text-muted"></i>
                                            </span>
                                            <input type="date"
                                                class="form-control @error('check_out') is-invalid @enderror"
                                                name="check_out"
                                                value="{{ old('check_out', now()->addDay()->format('Y-m-d')) }}" required>
                                            @error('check_out')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Alert --}}
                            <div
                                class="alert alert-info border-0 bg-info bg-opacity-10 border-start border-3 border-info rounded-2 mb-4">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle text-info mt-1 me-3"></i>
                                    <div>
                                        <strong class="d-block mb-1">Important Note</strong>
                                        Submitting this form creates a "Draft" registration. You will be redirected to
                                        finalize the room and rate immediately.
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                                <a href="{{ route('frontdesk.registrations.index') }}"
                                    class="btn btn-outline-dark me-2 px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-gold px-4">
                                    Create Draft & Finalize <i class="fas fa-arrow-right ms-2"></i>
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

                fetch(`{{ route('frontdesk.registrations.lookup') }}?phone=${encodeURIComponent(phone)}`)
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
