@extends('layouts.master')

@section('title', 'Booking Details')

@section('page-content')
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Booking Status</h4>
                <div class="text-center py-4">
                    <h2 class="fw-bold text-primary mb-2">{{ $booking->booking_reference }}</h2>
                    <span class="badge rounded-pill px-3 py-2 bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }} text-white">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                
                <hr>
                
                @if($booking->status === 'pending')
                    <div class="d-grid gap-2">
                        <form action="{{ route('website.admin.bookings.confirm', $booking->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block w-100 mb-2">
                                <i class="mdi mdi-check-circle me-1"></i> Confirm Booking
                            </button>
                        </form>
                        
                        <form action="{{ route('website.admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-block w-100">
                                <i class="mdi mdi-close-circle me-1"></i> Reject / Cancel
                            </button>
                        </form>
                    </div>
                    <p class="text-muted small mt-3 text-center">
                        <i class="mdi mdi-information"></i> Confirming will check Frontdesk availability first.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Reservation Details</h4>
                
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <p class="text-muted mb-1">Guest Name</p>
                        <h5 class="fw-bold">{{ $booking->guest_name }}</h5>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-1">Contact Info</p>
                        <p class="mb-0">{{ $booking->guest_email }}</p>
                        <p class="mb-0">{{ $booking->guest_phone }}</p>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4">
                    <div class="row">
                        <div class="col-sm-4">
                            <small class="text-uppercase text-muted">Room Type</small>
                            <p class="fw-bold">{{ $booking->room->name ?? 'Deleted Room' }}</p>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-uppercase text-muted">Check In</small>
                            <p class="fw-bold">{{ $booking->check_in_date->format('d M Y') }}</p>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-uppercase text-muted">Check Out</small>
                            <p class="fw-bold">{{ $booking->check_out_date->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <p class="text-muted mb-1">Guests</p>
                        <p>{{ $booking->adults }} Adults, {{ $booking->children }} Children</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-1">Total Amount</p>
                        <h4 class="text-success">₦{{ number_format($booking->total_amount, 2) }}</h4>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div class="alert alert-info mt-3">
                        <strong>Special Requests:</strong><br>
                        {{ $booking->special_requests }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection