@extends('website::layouts.master')

@section('title', 'Pre-Arrival Complete!')

@section('content')
<div class="min-vh-100 d-flex align-items-center py-5" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">

                <div class="mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                         style="width: 96px; height: 96px; background: rgba(40, 167, 69, 0.1);">
                        <i class="fas fa-check-circle fa-3x" style="color: #28a745;"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-2" style="text-transform: uppercase;">You're All Set!</h1>
                    <p class="text-muted lead">Your pre-arrival check-in is complete.</p>
                </div>

                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-body p-4 p-lg-5">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-1">{{ $registration->guest->full_name }}</h5>
                            <p class="text-muted small mb-0">Reservation: {{ $registration->reservation_code }}</p>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="p-3 rounded" style="background: #f8f6f1;">
                                    <small class="text-muted d-block">Check In</small>
                                    <strong>{{ $registration->check_in?->format('M d, Y') ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded" style="background: #f8f6f1;">
                                    <small class="text-muted d-block">Check Out</small>
                                    <strong>{{ $registration->check_out?->format('M d, Y') ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Your details have been received. When you arrive at the hotel, just let the front desk know your name to complete the check-in process quickly.
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <h6 class="fw-bold">What's Next?</h6>
                            <div class="d-flex align-items-center gap-3 text-start p-3 rounded" style="background: #f8f6f1;">
                                <span class="badge rounded-circle d-flex align-items-center justify-content-center" style="min-width: 28px; height: 28px; background: #C8A165; color: #fff;">1</span>
                                <span class="small">Present your ID at the front desk upon arrival</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-start p-3 rounded" style="background: #f8f6f1;">
                                <span class="badge rounded-circle d-flex align-items-center justify-content-center" style="min-width: 28px; height: 28px; background: #C8A165; color: #fff;">2</span>
                                <span class="small">Complete payment for any outstanding balance</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-start p-3 rounded" style="background: #f8f6f1;">
                                <span class="badge rounded-circle d-flex align-items-center justify-content-center" style="min-width: 28px; height: 28px; background: #C8A165; color: #fff;">3</span>
                                <span class="small">Receive your room key and enjoy your stay!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('website.home') }}" class="btn btn-primary px-5 py-2 fw-bold">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
