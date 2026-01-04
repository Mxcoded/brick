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
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle" style="width: 80px; height: 80px;">
                                <i class="fas fa-check fa-3x"></i>
                            </div>
                        </div>

                        <h1 class="h2 fw-bold text-success mb-3">Booking Confirmed!</h1>
                        <p class="lead text-muted mb-4">
                            Thank you, <strong>{{ $booking->guest_name }}</strong>. Your reservation request has been received.
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

                        <p class="small text-muted mb-4">
                            A confirmation email has been sent to <strong>{{ $booking->guest_email }}</strong>.<br>
                            Our team will review your request and contact you shortly to finalize payment.
                        </p>

                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="{{ route('website.home') }}" class="btn btn-outline-secondary px-4">Return to Home</a>
                            <a href="{{ route('website.contact') }}" class="btn btn-primary px-4">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection