@extends('layouts.master')

@section('title', 'Booking Details')

@section('page-content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">Booking Details</h1>
            <p class="text-muted small mb-0">Reference: <span class="fw-bold text-primary">{{ $booking->booking_reference }}</span></p>
        </div>
        <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Reservation Info</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Guest Details</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-3 text-primary me-3">
                                    <i class="fas fa-user fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">{{ $booking->guest_name }}</h5>
                                    <small class="text-muted">Registered Guest</small>
                                </div>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-envelope text-muted me-2 fixed-width-icon"></i> {{ $booking->guest_email }}</li>
                                <li class="mb-2"><i class="fas fa-phone text-muted me-2 fixed-width-icon"></i> {{ $booking->guest_phone }}</li>
                                <li><i class="fas fa-users text-muted me-2 fixed-width-icon"></i> {{ $booking->adults }} Adults, {{ $booking->children }} Children</li>
                            </ul>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Room Details</h6>
                            @if($booking->room)
                                <div class="d-flex align-items-start mb-3">
                                    @if($booking->room->image_url)
                                        <img src="{{ $booking->room->image_url }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $booking->room->name }}</h6>
                                        <span class="badge bg-light text-dark border">{{ $booking->room->bed_type }}</span>
                                    </div>
                                </div>
                                <div class="alert alert-light border small mb-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Check-in:</span>
                                        <span class="fw-bold text-success">{{ $booking->check_in_date->format('D, M d, Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Check-out:</span>
                                        <span class="fw-bold text-danger">{{ $booking->check_out_date->format('D, M d, Y') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Room has been deleted from inventory.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-center">Nights</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Room Charge ({{ $booking->room->name ?? 'Room' }})</td>
                                    <td class="text-end">₦{{ number_format($booking->room->price ?? 0, 2) }}</td>
                                    <td class="text-center">{{ $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1 }}</td>
                                    <td class="text-end fw-bold">₦{{ number_format($booking->total_amount, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td colspan="3" class="text-end fw-bold pt-3">Grand Total</td>
                                    <td class="text-end fw-bold text-success fs-5 pt-3">₦{{ number_format($booking->total_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($booking->special_requests)
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2">Special Requests</h6>
                    <p class="mb-0 text-dark fst-italic">"{{ $booking->special_requests }}"</p>
                </div>
            </div>
            @endif

        </div>

        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark">Booking Status</h6>
                </div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        @if($booking->status === 'confirmed')
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h4 class="fw-bold text-success">Confirmed</h4>
                        @elseif($booking->status === 'pending')
                            <i class="fas fa-clock text-warning fa-4x mb-3"></i>
                            <h4 class="fw-bold text-warning">Pending Approval</h4>
                        @elseif($booking->status === 'cancelled')
                            <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                            <h4 class="fw-bold text-danger">Cancelled</h4>
                        @else
                            <i class="fas fa-circle text-secondary fa-4x mb-3"></i>
                            <h4 class="fw-bold text-secondary">{{ ucfirst($booking->status) }}</h4>
                        @endif
                    </div>
                    
                    <div class="d-flex justify-content-between px-4 mt-4 text-muted small">
                        <span>Payment:</span>
                        <span class="fw-bold text-uppercase {{ $booking->payment_status === 'paid' ? 'text-success' : 'text-danger' }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between px-4 mt-2 text-muted small">
                        <span>Created:</span>
                        <span>{{ $booking->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Actions</h6>
                    
                    <div class="d-grid gap-2">
                        @if($booking->status === 'pending')
                            <form action="{{ route('website.admin.bookings.confirm', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                    <i class="fas fa-check me-2"></i> Confirm Booking
                                </button>
                            </form>
                            
                            <div class="alert alert-info small mb-0 mt-2">
                                <i class="fas fa-info-circle me-1"></i> Confirming will check Frontdesk CRM for room availability first.
                            </div>
                        @endif

                        <a href="{{ route('website.admin.bookings.edit', $booking->id) }}" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-edit me-2"></i> Edit Details
                        </a>

                        @if($booking->status !== 'cancelled')
                            <form action="{{ route('website.admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 mt-2">
                                    <i class="fas fa-ban me-2"></i> Cancel Booking
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('website.admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('WARNING: This will permanently delete the record. Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger text-decoration-none w-100 mt-2">
                                <small>Delete Record Permanently</small>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection