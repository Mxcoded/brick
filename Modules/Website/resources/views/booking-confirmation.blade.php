@extends('website::layouts.master')

@section('title', 'Booking Confirmed')

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-5 text-center">

                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle"
                                    style="width: 80px; height: 80px;">
                                    <i class="fas fa-check fa-3x"></i>
                                </div>
                            </div>

                            <h1 class="h2 fw-bold text-success mb-3">Booking Confirmed!</h1>
                            <p class="lead text-muted mb-4">
                                Thank you, <strong>{{ $booking->guest_name }}</strong>. Your reservation request has been
                                received.
                            </p>

                            <div class="bg-light border rounded p-3 d-inline-block mb-4">
                                <small class="text-uppercase text-muted fw-bold d-block mb-1">Booking Reference</small>
                                <span class="h3 fw-bold text-primary mb-0">{{ $booking->booking_reference }}</span>
                            </div>

                            <div class="row text-start bg-light rounded p-4 mb-4 g-3">
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold">Check-in</small>
                                    <p class="fw-bold mb-0">{{ $booking->check_in_date->format('D, M d, Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold">Check-out</small>
                                    <p class="fw-bold mb-0">{{ $booking->check_out_date->format('D, M d, Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold">Room Type</small>
                                    <p class="fw-bold mb-0">{{ $booking->room->name ?? 'Room Name' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold">Total Amount</small>
                                    <p class="fw-bold mb-0">₦{{ number_format($booking->total_amount, 2) }}</p>
                                </div>
                            </div>

                           {{-- Email Controls Section --}}
<div class="mt-5 pt-4 border-top">
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Options</h5>
    
    <div class="row">
        <div class="col-md-8">
            <p class="text-muted small">
                Did not receive your confirmation email at <strong>{{ $booking->guest_email }}</strong>? 
                Check your spam folder or resend it below. If you made a typo, you can correct it here.
            </p>

            <form action="{{ route('website.booking.resend') }}" method="POST" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEmailEdit()">
                        <i class="fas fa-pen me-1"></i> Fix Typo
                    </button>
                </div>

                {{-- Hidden Email Input for Corrections --}}
                <div class="col-auto d-none" id="emailEditField">
                    <input type="email" name="email" class="form-control form-control-sm" 
                           placeholder="Enter correct email" value="{{ $booking->guest_email }}" required>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-paper-plane me-1"></i> Resend Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



                            <div class="d-grid gap-2 d-sm-flex justify-content-center">
                                {{-- ✅ THE NAVIGATION BUTTONS --}}
                                <div class="d-grid gap-2">
                                    @auth
                                        {{-- They are logged in (Scenario A or B) --}}
                                        <a href="{{ route('guest.dashboard') }}" class="btn btn-primary btn-lg">
                                            <i class="fas fa-tachometer-alt me-2"></i> Manage My Booking
                                        </a>
                                    @else
                                        {{-- They are a pure guest (Scenario C) --}}
                                        <p class="text-muted small">An account was not created. Please save your reference code.
                                        </p>
                                        <a href="{{ route('website.home') }}" class="btn btn-outline-primary">Return Home</a>
                                    @endauth
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Simple Script to Toggle Input --}}
<script>
    function toggleEmailEdit() {
        const field = document.getElementById('emailEditField');
        const input = field.querySelector('input');
        
        if (field.classList.contains('d-none')) {
            field.classList.remove('d-none'); // Show input
            input.focus();
        } else {
            field.classList.add('d-none'); // Hide input
            input.value = '{{ $booking->guest_email }}'; // Reset value
        }
    }
</script>
@endsection
