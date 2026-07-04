@extends('website::layouts.master')

@section('title', 'Pre-Arrival Check-In')

@section('content')
<div class="min-vh-100 d-flex align-items-center py-5" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="text-center mb-5">
                    <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
                         alt="Brickspoint Logo" style="max-height: 60px;" class="mb-4">
                    <h1 class="display-5 fw-bold" style="text-transform: uppercase;">Pre-Arrival Check-In</h1>
                    <p class="text-muted mx-auto" style="max-width: 500px;">
                        Save time at the front desk. Complete your check-in details before you arrive.
                    </p>
                </div>

                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-body p-4 p-lg-5">
                        <h5 class="fw-bold mb-3">Find Your Reservation</h5>
                        <p class="text-muted small mb-4">Enter your reservation code and the email or phone number used during booking.</p>

                        <form method="POST" action="{{ route('guest.pre-arrival.lookup') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reservation Code</label>
                                <input type="text" name="reservation_code"
                                       class="form-control @error('reservation_code') is-invalid @enderror"
                                       value="{{ old('reservation_code') }}"
                                       placeholder="e.g. BRK-2025-00123" required>
                                @error('reservation_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Email or Phone Number</label>
                                <input type="text" name="contact"
                                       class="form-control @error('contact') is-invalid @enderror"
                                       value="{{ old('contact') }}"
                                       placeholder="your@email.com or +234 800 000 0000" required>
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="lookupBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="lookupSpinner"></span>
                                <span id="lookupText"><i class="fas fa-search me-2"></i> Find My Reservation</span>
                            </button>
                        </form>

                        @if(session('error'))
                            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
                        @endif
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="small text-muted">
                        <i class="fas fa-lock me-1"></i> Your information is secure and encrypted.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('form')?.addEventListener('submit', function() {
    const btn = document.getElementById('lookupBtn');
    const spinner = document.getElementById('lookupSpinner');
    const text = document.getElementById('lookupText');
    btn.disabled = true;
    spinner.classList.remove('d-none');
    text.innerHTML = 'Searching...';
});
</script>
@endpush
