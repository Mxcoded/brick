@extends('layouts.master')

@section('title', 'Booking Details')

@section('styles')
<style>
    :root {
        --bp-gold: #C8A165;
        --bp-gold-light: #D4B87A;
        --bp-gold-dark: #B8915A;
        --bp-charcoal: #333333;
        --bp-white: #FFFFFF;
        --bp-neutral: #F5F3EF;
        --bp-neutral-dark: #E8E4DC;
    }

    .booking-details {
        font-family: 'Proxima Nova', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .text-bp-gold { color: var(--bp-gold) !important; }
    .bg-bp-gold { background-color: var(--bp-gold) !important; }
    .border-bp-gold { border-color: var(--bp-gold) !important; }
    .text-bp-charcoal { color: var(--bp-charcoal) !important; }
    .bg-bp-neutral { background-color: var(--bp-neutral) !important; }

    .bp-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .bp-card .card-header {
        background: var(--bp-white);
        border-bottom: 2px solid var(--bp-neutral-dark);
        padding: 1rem 1.25rem;
    }

    .bp-card .card-header h6 {
        color: var(--bp-charcoal);
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .bp-card .card-header h6 i {
        color: var(--bp-gold);
    }

    .status-card {
        background: linear-gradient(135deg, var(--bp-charcoal) 0%, #1a1a1a 100%);
        color: var(--bp-white);
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .status-card .status-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border: 2px solid rgba(255,255,255,0.15);
    }

    .status-confirmed { background: rgba(40, 167, 69, 0.25); color: #4ade80; border-color: rgba(74, 222, 128, 0.3); }
    .status-pending { background: rgba(200, 161, 101, 0.25); color: var(--bp-gold); border-color: rgba(200, 161, 101, 0.3); }
    .status-cancelled { background: rgba(220, 53, 69, 0.25); color: #f87171; border-color: rgba(248, 113, 113, 0.3); }

    .status-card .status-label {
        color: rgba(255,255,255,0.55);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-card .status-value {
        color: #fff;
        font-weight: 700;
    }

    .status-card .status-divider {
        border-color: rgba(255,255,255,0.08);
        margin: 0.75rem 0;
        opacity: 1;
    }

    .badge-payment-paid {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.3);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .badge-payment-unpaid {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.85rem 1rem;
        border: none;
        border-radius: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        text-decoration: none;
    }

    .action-btn:active {
        transform: translateY(0);
    }

    .action-btn i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
    }

    .action-btn-gold {
        background: linear-gradient(135deg, #C8A165, #B8915A);
        color: #fff;
    }
    .action-btn-gold:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(200, 161, 101, 0.4);
    }

    .action-btn-teal {
        background: linear-gradient(135deg, #0EA5E9, #0284C7);
        color: #fff;
    }
    .action-btn-teal:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
    }

    .action-btn-purple {
        background: linear-gradient(135deg, #A855F7, #7C3AED);
        color: #fff;
    }
    .action-btn-purple:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);
    }

    .action-btn-amber {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #fff;
    }
    .action-btn-amber:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .action-btn-blue {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
        color: #fff;
    }
    .action-btn-blue:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }

    .action-btn-red {
        background: linear-gradient(135deg, #EF4444, #DC2626);
        color: #fff;
    }
    .action-btn-red:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .action-btn-destroy {
        justify-content: center;
        background: none;
        color: #999;
        font-weight: 400;
        font-size: 0.78rem;
        padding: 0.5rem;
        margin-top: 0.25rem;
    }
    .action-btn-destroy:hover {
        color: #EF4444;
        background: rgba(239, 68, 68, 0.06);
        transform: none;
        box-shadow: none;
    }

    .guest-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--bp-gold) 0%, var(--bp-gold-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bp-white);
        font-size: 2rem;
    }

    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 600;
        color: var(--bp-charcoal);
    }

    .date-box {
        background: var(--bp-neutral);
        border-radius: 10px;
        padding: 1rem;
        border-left: 4px solid var(--bp-gold);
    }

    .financial-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--bp-neutral-dark);
    }

    .financial-row:last-child {
        border-bottom: none;
    }

    .grand-total {
        background: var(--bp-neutral);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .grand-total .amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--bp-gold);
    }

    .badge-bp {
        background: var(--bp-gold);
        color: var(--bp-white);
        font-weight: 600;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }

    .section-divider {
        height: 2px;
        background: linear-gradient(90deg, var(--bp-gold), transparent);
        margin: 1.5rem 0;
    }

    .alert-bp-info {
        background: rgba(200, 161, 101, 0.1);
        border: 1px solid var(--bp-gold);
        border-radius: 8px;
        color: var(--bp-charcoal);
    }

    .alert-bp-info i {
        color: var(--bp-gold);
    }
</style>
@endsection

@section('page-content')
<div class="container-fluid py-4 booking-details">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-sm btn-light mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Bookings
            </a>
            <h1 class="h3 text-bp-charcoal mb-1" style="font-weight: 700;">Booking Details</h1>
            <p class="mb-0">
                <span class="text-muted">Reference:</span> 
                <span class="fw-bold text-bp-gold">{{ $booking->booking_reference }}</span>
                @if($booking->booking_group_id)
                    <span class="badge badge-bp ms-2">Group: {{ $booking->booking_group_id }}</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-4"></i>
                <div><strong>Success!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error Messages --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2 fs-4"></i>
                <div><strong>Error!</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-circle me-2 fs-4"></i>
                <strong>Please fix the following errors:</strong>
            </div>
            <ul class="mb-0 mt-2 ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">

            {{-- Guest Profile Card --}}
            <div class="card bp-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0"><i class="fas fa-user-circle me-2"></i>Guest Profile</h6>
                    @if($booking->guest)
                        <span class="badge bg-success"><i class="fas fa-link me-1"></i>CRM Linked</span>
                    @else
                        <span class="badge bg-secondary"><i class="fas fa-user me-1"></i>Website Guest</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Primary Guest Info --}}
                        <div class="col-md-6 border-end">
                            <div class="d-flex align-items-center mb-4">
                                <div class="guest-avatar me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1 text-bp-charcoal">{{ $booking->guest->title ?? '' }} {{ $booking->guest_name }}</h4>
                                    <span class="text-muted">{{ $booking->guest->occupation ?? 'Guest' }}</span>
                                    @if($booking->guest?->company_name)
                                        <br><small class="text-muted"><i class="fas fa-building me-1"></i>{{ $booking->guest->company_name }}</small>
                                    @endif
                                </div>
                            </div>

                            <p class="info-label mb-3 border-bottom pb-2">Contact Information</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-envelope text-bp-gold me-3" style="width: 20px;"></i>
                                    <div>
                                        <span class="info-label d-block">Email</span>
                                        <a href="mailto:{{ $booking->guest_email }}" class="info-value text-decoration-none">{{ $booking->guest_email }}</a>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-phone text-bp-gold me-3" style="width: 20px;"></i>
                                    <div>
                                        <span class="info-label d-block">Phone</span>
                                        <a href="tel:{{ $booking->guest_phone }}" class="info-value text-decoration-none">{{ $booking->guest_phone ?? 'N/A' }}</a>
                                    </div>
                                </li>
                                @if($booking->guest?->contact_number && $booking->guest->contact_number != $booking->guest_phone)
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-mobile-alt text-bp-gold me-3" style="width: 20px;"></i>
                                    <div>
                                        <span class="info-label d-block">Alt. Phone</span>
                                        <a href="tel:{{ $booking->guest->contact_number }}" class="info-value text-decoration-none">{{ $booking->guest->contact_number }}</a>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>

                        {{-- Extended Guest Info --}}
                        <div class="col-md-6 ps-md-4">
                            @if($booking->guest)
                                <p class="info-label mb-3 border-bottom pb-2">Personal Details</p>
                                <div class="row g-3 mb-4">
                                    @if($booking->guest->gender)
                                    <div class="col-6">
                                        <span class="info-label d-block">Gender</span>
                                        <span class="info-value">{{ ucfirst($booking->guest->gender) }}</span>
                                    </div>
                                    @endif
                                    @if($booking->guest->birthday)
                                    <div class="col-6">
                                        <span class="info-label d-block">Birthday</span>
                                        <span class="info-value">{{ $booking->guest->birthday->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                    @if($booking->guest->nationality)
                                    <div class="col-6">
                                        <span class="info-label d-block">Nationality</span>
                                        <span class="info-value">{{ $booking->guest->nationality }}</span>
                                    </div>
                                    @endif
                                    @if($booking->guest->identification_type)
                                    <div class="col-6">
                                        <span class="info-label d-block">ID Type</span>
                                        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $booking->guest->identification_type)) }}</span>
                                    </div>
                                    @endif
                                    @if($booking->guest->identification_number)
                                    <div class="col-12">
                                        <span class="info-label d-block">ID Number</span>
                                        <span class="info-value font-monospace">{{ $booking->guest->identification_number }}</span>
                                    </div>
                                    @endif
                                </div>

                                @if($booking->guest->home_address || $booking->guest->city || $booking->guest->state)
                                <p class="info-label mb-3 border-bottom pb-2">Address</p>
                                <address class="mb-0 info-value">
                                    @if($booking->guest->home_address)
                                        {{ $booking->guest->home_address }}<br>
                                    @endif
                                    @if($booking->guest->city || $booking->guest->state || $booking->guest->zip_code)
                                        {{ $booking->guest->city }}{{ $booking->guest->city && $booking->guest->state ? ', ' : '' }}{{ $booking->guest->state }} {{ $booking->guest->zip_code }}
                                    @endif
                                </address>
                                @endif
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block text-bp-gold"></i>
                                    <p class="mb-0">Extended profile data not available.<br>
                                    <small>Guest booked without creating a CRM profile.</small></p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    @if($booking->guest && ($booking->guest->emergency_name || $booking->guest->emergency_contact))
                    <div class="section-divider"></div>
                    <p class="info-label mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Emergency Contact</p>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="info-label d-block">Name</span>
                            <span class="info-value">{{ $booking->guest->emergency_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label d-block">Relationship</span>
                            <span class="info-value">{{ ucfirst($booking->guest->emergency_relationship ?? 'N/A') }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label d-block">Contact</span>
                            <a href="tel:{{ $booking->guest->emergency_contact }}" class="info-value text-decoration-none">{{ $booking->guest->emergency_contact ?? 'N/A' }}</a>
                        </div>
                    </div>
                    @endif

                    {{-- Guest Stats --}}
                    @if($booking->guest && $booking->guest->visit_count > 0)
                    <div class="section-divider"></div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h3 fw-bold text-bp-gold mb-0">{{ $booking->guest->visit_count }}</div>
                            <small class="text-muted">Total Visits</small>
                        </div>
                        <div class="col-4">
                            <div class="h6 fw-bold text-bp-charcoal mb-0">{{ $booking->guest->last_visit_at ? $booking->guest->last_visit_at->diffForHumans() : 'N/A' }}</div>
                            <small class="text-muted">Last Visit</small>
                        </div>
                        <div class="col-4">
                            <div class="h6 fw-bold mb-0">
                                @if($booking->guest->visit_count >= 5)
                                    <span class="text-bp-gold"><i class="fas fa-crown"></i> VIP</span>
                                @elseif($booking->guest->visit_count >= 2)
                                    <span class="text-info"><i class="fas fa-redo"></i> Returning</span>
                                @else
                                    <span class="text-success"><i class="fas fa-user-plus"></i> New</span>
                                @endif
                            </div>
                            <small class="text-muted">Guest Type</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Reservation Details Card --}}
            <div class="card bp-card mb-4">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-bed me-2"></i>Reservation Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <p class="info-label mb-3">Stay Information</p>
                            
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-users text-bp-gold me-3" style="width: 24px;"></i>
                                <div>
                                    <span class="info-label d-block">Guests</span>
                                    <span class="info-value">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}, {{ $booking->children }} Child{{ $booking->children != 1 ? 'ren' : '' }}</span>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-moon text-bp-gold me-3" style="width: 24px;"></i>
                                <div>
                                    <span class="info-label d-block">Duration</span>
                                    <span class="info-value">{{ $booking->check_in_date->diffInDays($booking->check_out_date) }} Night{{ $booking->check_in_date->diffInDays($booking->check_out_date) > 1 ? 's' : '' }}</span>
                                </div>
                            </div>

                            <div class="date-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted"><i class="fas fa-sign-in-alt me-1"></i> Check-in</span>
                                    <span class="fw-bold text-success">{{ $booking->check_in_date->format('D, M d, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-sign-out-alt me-1"></i> Check-out</span>
                                    <span class="fw-bold text-danger">{{ $booking->check_out_date->format('D, M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <p class="info-label mb-3">Room Details</p>
                            @php
                                $roomInfo = $booking->roomType ?? $booking->room;
                            @endphp
                            @if ($roomInfo)
                                <div class="d-flex align-items-start mb-3">
                                    @if ($roomInfo->image_url)
                                        <img src="{{ $roomInfo->image_url }}" class="rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                                    @else
                                        <div class="bg-bp-neutral rounded me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                            <i class="fas fa-bed text-bp-gold fa-lg"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h5 class="fw-bold mb-1 text-bp-charcoal">{{ $roomInfo->name }}</h5>
                                        <span class="badge bg-light text-dark border">{{ $roomInfo->bed_type ?? 'Standard' }}</span>
                                        @if($booking->roomUnit)
                                            <span class="badge badge-bp ms-1">Room {{ $booking->roomUnit->room_number }}</span>
                                        @elseif($booking->roomType)
                                            <span class="badge bg-warning text-dark ms-1">Room TBA</span>
                                        @endif
                                    </div>
                                </div>
                                @if($roomInfo->max_occupancy)
                                <div class="small text-muted">
                                    <i class="fas fa-users me-1"></i> Max Occupancy: {{ $roomInfo->max_occupancy }} guests
                                </div>
                                @endif
                            @else
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Room has been deleted from inventory.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Summary Card --}}
            <div class="card bp-card mb-4">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h6>
                </div>
                <div class="card-body">
                    <div class="financial-row d-flex justify-content-between align-items-center">
                        <div>
                            <span class="info-value">Room Charge</span>
                            <small class="text-muted d-block">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="text-muted">₦{{ number_format(optional($booking->roomType)->price ?? optional($booking->room)->price ?? 0, 2) }} × {{ $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1 }} nights</span>
                        </div>
                    </div>

                    <div class="grand-total d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-bp-charcoal">Grand Total</span>
                        <span class="amount">₦{{ number_format($booking->total_amount, 2) }}</span>
                    </div>

                    <div class="row text-center mt-4 pt-3 border-top">
                        <div class="col-4">
                            <span class="info-label d-block">Amount Paid</span>
                            <span class="fw-bold text-success fs-5">₦{{ number_format($booking->amount_paid ?? 0, 2) }}</span>
                        </div>
                        <div class="col-4">
                            <span class="info-label d-block">Balance Due</span>
                            <span class="fw-bold fs-5 {{ ($booking->total_amount - ($booking->amount_paid ?? 0)) > 0 ? 'text-danger' : 'text-success' }}">
                                ₦{{ number_format($booking->total_amount - ($booking->amount_paid ?? 0), 2) }}
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="info-label d-block">Payment Method</span>
                            <span class="info-value">{{ ucfirst($booking->payment_method ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Special Requests & Admin Notes --}}
            <div class="row g-4 mb-4">
                @if ($booking->special_requests)
                <div class="col-md-6">
                    <div class="card bp-card border-start border-4 h-100" style="border-color: var(--bp-gold) !important;">
                        <div class="card-body">
                            <p class="info-label mb-2"><i class="fas fa-comment-dots text-bp-gold me-1"></i> Special Requests</p>
                            <p class="mb-0 text-bp-charcoal fst-italic">"{{ $booking->special_requests }}"</p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($booking->admin_notes)
                <div class="col-md-6">
                    <div class="card bp-card border-start border-4 border-warning h-100">
                        <div class="card-body">
                            <p class="info-label mb-2"><i class="fas fa-sticky-note text-warning me-1"></i> Admin Notes</p>
                            <p class="mb-0 text-bp-charcoal">{{ $booking->admin_notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- Status Card --}}
            <div class="status-card mb-4 p-4">
                <div class="text-center">
                    <div class="status-icon {{ $booking->status === 'confirmed' ? 'status-confirmed' : ($booking->status === 'pending' ? 'status-pending' : 'status-cancelled') }}">
                        @if ($booking->status === 'confirmed')
                            <i class="fas fa-check-circle fa-3x"></i>
                        @elseif($booking->status === 'pending')
                            <i class="fas fa-clock fa-3x"></i>
                        @elseif($booking->status === 'cancelled')
                            <i class="fas fa-times-circle fa-3x"></i>
                        @else
                            <i class="fas fa-circle fa-3x"></i>
                        @endif
                    </div>
                    <h4 class="fw-bold mb-3">
                        @if ($booking->status === 'confirmed')
                            Confirmed
                        @elseif($booking->status === 'pending')
                            Pending Confirmation
                        @elseif($booking->status === 'cancelled')
                            Cancelled
                        @else
                            {{ ucfirst($booking->status) }}
                        @endif
                    </h4>

                    <hr class="status-divider">

                    <div class="d-flex justify-content-between align-items-center px-3 mb-2">
                        <span class="status-label">Payment Status</span>
                        <span class="badge-payment-{{ $booking->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                            <i class="fas fa-{{ $booking->payment_status === 'paid' ? 'check-circle' : 'hourglass-half' }} me-1"></i>
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between px-3">
                        <span class="status-label">Created</span>
                        <span class="status-value">{{ $booking->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($booking->source)
                    <div class="d-flex justify-content-between px-3 mt-2">
                        <span class="status-label">Source</span>
                        <span class="badge bg-secondary">{{ ucfirst($booking->source) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card bp-card actions-card">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </div>

                    <div class="d-grid gap-2">

                        {{-- Confirm Booking (Only for Pending) --}}
                        @if ($booking->status === 'pending')
                            <form action="{{ route('website.admin.bookings.confirm', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="action-btn action-btn-gold">
                                    <i class="fas fa-check-circle"></i>
                                    Confirm Booking
                                </button>
                            </form>
                        @endif

                        {{-- Assign Room --}}
                        <button type="button" class="action-btn action-btn-teal" data-bs-toggle="modal" data-bs-target="#assignRoomModal">
                            <i class="fas fa-door-open"></i>
                            {{ $booking->roomUnit ? 'Change Room' : 'Assign Room' }}
                        </button>

                        {{-- Change Room Type --}}
                        <button type="button" class="action-btn action-btn-purple" data-bs-toggle="modal" data-bs-target="#changeRoomTypeModal">
                            <i class="fas fa-exchange-alt"></i>
                            Change Room Type
                        </button>

                        {{-- Resend Confirmation --}}
                        <form action="{{ route('website.admin.bookings.resend', $booking->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="action-btn action-btn-amber">
                                <i class="fas fa-envelope"></i>
                                Resend Confirmation
                            </button>
                        </form>

                        {{-- Edit --}}
                        <a href="{{ route('website.admin.bookings.edit', $booking->id) }}" class="action-btn action-btn-blue">
                            <i class="fas fa-pen"></i>
                            Edit Details
                        </a>

                        {{-- Cancel --}}
                        @if ($booking->status !== 'cancelled')
                            <form action="{{ route('website.admin.bookings.cancel', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="action-btn action-btn-red">
                                    <i class="fas fa-ban"></i>
                                    Cancel Booking
                                </button>
                            </form>
                        @endif

                        {{-- Delete --}}
                        <form action="{{ route('website.admin.bookings.destroy', $booking->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn action-btn-destroy">
                                <i class="fas fa-trash-alt"></i>
                                Delete permanently
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Assign Room Modal --}}
<div class="modal fade" id="assignRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('website.admin.bookings.assign-room', $booking->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background: var(--bp-neutral);">
                <h5 class="modal-title text-bp-charcoal">
                    <i class="fas fa-door-open me-2 text-bp-gold"></i>
                    {{ $booking->roomUnit ? 'Change Room Assignment' : 'Assign Room' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Current Info --}}
                <div class="date-box mb-4">
                    <div class="row small">
                        <div class="col-6">
                            <span class="info-label">Room Type</span>
                            <div class="info-value">{{ optional($booking->roomType)->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <span class="info-label">Current Room</span>
                            <div class="info-value">
                                @if($booking->roomUnit)
                                    <span class="text-bp-gold">{{ $booking->roomUnit->room_number }}</span>
                                @else
                                    <span class="text-warning">Not Assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row small">
                        <div class="col-6">
                            <span class="info-label">Check-in</span>
                            <div class="info-value">{{ $booking->check_in_date->format('M d, Y') }}</div>
                        </div>
                        <div class="col-6">
                            <span class="info-label">Check-out</span>
                            <div class="info-value">{{ $booking->check_out_date->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Room Selection --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-bp-charcoal">Select Room Unit <span class="text-danger">*</span></label>
                    <select name="room_unit_id" class="form-select" required>
                        <option value="">-- Select a Room --</option>
                        @php
                            $roomType = $booking->roomType;
                            $availableUnits = collect();
                            if ($roomType) {
                                $availableUnits = $roomType->units()
                                    ->where('status', 'available')
                                    ->orderBy('room_number')
                                    ->get();
                            }
                        @endphp
                        @forelse($availableUnits as $unit)
                            @php
                                $isAvailable = $unit->isAvailableForDates(
                                    $booking->check_in_date->format('Y-m-d'),
                                    $booking->check_out_date->format('Y-m-d'),
                                    $booking->id
                                );
                                $isCurrentUnit = $booking->room_unit_id == $unit->id;
                            @endphp
                            <option value="{{ $unit->id }}" 
                                {{ $isCurrentUnit ? 'selected' : '' }}
                                {{ !$isAvailable && !$isCurrentUnit ? 'disabled' : '' }}>
                                Room {{ $unit->room_number }} 
                                ({{ $unit->floor ? 'Floor ' . $unit->floor : 'Ground' }})
                                @if($isCurrentUnit)
                                    - Current
                                @elseif(!$isAvailable)
                                    - Occupied
                                @else
                                    - Available
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>No rooms available for this type</option>
                        @endforelse
                    </select>
                    <small class="text-muted">Only showing rooms available for the booking dates</small>
                </div>

                {{-- Unassign Option --}}
                @if($booking->roomUnit)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="unassign" id="unassignRoom" value="1">
                    <label class="form-check-label text-danger small" for="unassignRoom">
                        <i class="fas fa-times-circle me-1"></i> Unassign current room (leave as TBA)
                    </label>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-bp-gold">
                    <i class="fas fa-save me-1"></i> Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Change Room Type Modal --}}
<div class="modal fade" id="changeRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('website.admin.bookings.change-room-type', $booking->id) }}" method="POST" class="modal-content" id="changeRoomTypeForm">
            @csrf
            <div class="modal-header" style="background: var(--bp-neutral);">
                <h5 class="modal-title text-bp-charcoal">
                    <i class="fas fa-exchange-alt me-2 text-bp-gold"></i>
                    Change Room Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Current Booking Summary --}}
                <div class="date-box mb-4">
                    <div class="row small">
                        <div class="col-4">
                            <span class="info-label">Current Room Type</span>
                            <div class="info-value">{{ optional($booking->roomType)->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-4">
                            <span class="info-label">Current Price/Night</span>
                            <div class="info-value">₦{{ number_format(optional($booking->roomType)->price ?? 0, 2) }}</div>
                        </div>
                        <div class="col-4">
                            <span class="info-label">Total Amount</span>
                            <div class="info-value text-bp-gold">₦{{ number_format($booking->total_amount, 2) }}</div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row small">
                        <div class="col-6">
                            <span class="info-label">Check-in</span>
                            <div class="info-value">{{ $booking->check_in_date->format('M d, Y') }}</div>
                        </div>
                        <div class="col-6">
                            <span class="info-label">Check-out</span>
                            <div class="info-value">{{ $booking->check_out_date->format('M d, Y') }} ({{ $booking->check_in_date->diffInDays($booking->check_out_date) }} nights)</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Room Type Selection --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-bp-charcoal">New Room Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" id="newRoomTypeSelect" class="form-select" required>
                                <option value="">-- Select Room Type --</option>
                                @php
                                    $allRoomTypes = \App\Models\RoomType::active()->ordered()->get();
                                    $nights = $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1;
                                @endphp
                                @foreach($allRoomTypes as $type)
                                    @php
                                        $availableCount = $type->getAvailabilityCountForDates(
                                            $booking->check_in_date->format('Y-m-d'),
                                            $booking->check_out_date->format('Y-m-d')
                                        );
                                        $isCurrent = $booking->room_type_id == $type->id;
                                    @endphp
                                    <option value="{{ $type->id }}" 
                                        data-price="{{ $type->price }}"
                                        data-units="{{ $availableCount }}"
                                        {{ $isCurrent ? 'selected' : '' }}>
                                        {{ $type->name }} - ₦{{ number_format($type->price, 2) }}/night
                                        @if($isCurrent)
                                            (Current)
                                        @elseif($availableCount > 0)
                                            ({{ $availableCount }} available)
                                        @else
                                            (No availability)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Price Recalculation Option --}}
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="recalculate_price" id="recalculatePrice" value="1" checked>
                                <label class="form-check-label" for="recalculatePrice">
                                    <strong>Recalculate price</strong>
                                    <small class="text-muted d-block">Update total amount based on new room type price</small>
                                </label>
                            </div>
                        </div>

                        {{-- Price Preview --}}
                        <div class="alert alert-bp-info" id="pricePreview">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calculator me-2"></i>New Total:</span>
                                <strong class="fs-5" id="newPriceDisplay">₦{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                            <small class="text-muted" id="priceBreakdown">{{ $nights }} nights × ₦{{ number_format(optional($booking->roomType)->price ?? 0, 2) }}</small>
                        </div>
                    </div>

                    {{-- Room Unit Selection --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-bp-charcoal">Assign Room Unit <small class="text-muted">(Optional)</small></label>
                            <select name="room_unit_id" id="newRoomUnitSelect" class="form-select">
                                <option value="">-- Leave as TBA --</option>
                                {{-- Will be populated dynamically --}}
                            </select>
                            <small class="text-muted">Select a specific room or leave as TBA</small>
                        </div>

                        <div id="roomUnitsList" class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background: #fafafa;">
                            <p class="text-muted text-center mb-0 small"><i class="fas fa-info-circle me-1"></i>Select a room type to see available units</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-bp-gold">
                    <i class="fas fa-save me-1"></i> Change Room Type
                </button>
            </div>
        </form>
    </div>
</div>

@php
    // Prepare room units data for JavaScript
    $roomUnitsDataArray = $allRoomTypes->mapWithKeys(function($type) use ($booking) {
        $units = $type->units()
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get()
            ->map(function($unit) use ($booking) {
                $isAvailable = $unit->isAvailableForDates(
                    $booking->check_in_date->format('Y-m-d'),
                    $booking->check_out_date->format('Y-m-d'),
                    $booking->id
                );
                return [
                    'id' => $unit->id,
                    'room_number' => $unit->room_number,
                    'floor' => $unit->floor,
                    'is_available' => $isAvailable,
                ];
            });
        return [$type->id => $units];
    })->toArray();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomTypeSelect = document.getElementById('newRoomTypeSelect');
    const roomUnitSelect = document.getElementById('newRoomUnitSelect');
    const roomUnitsList = document.getElementById('roomUnitsList');
    const recalculateCheckbox = document.getElementById('recalculatePrice');
    const pricePreview = document.getElementById('pricePreview');
    const newPriceDisplay = document.getElementById('newPriceDisplay');
    const priceBreakdown = document.getElementById('priceBreakdown');
    const nights = {{ $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1 }};
    const currentTotal = {{ $booking->total_amount }};
    const checkIn = '{{ $booking->check_in_date->format('Y-m-d') }}';
    const checkOut = '{{ $booking->check_out_date->format('Y-m-d') }}';
    const bookingId = {{ $booking->id }};

    // Room units data by room type
    const roomUnitsData = @json($roomUnitsDataArray);

    function updateRoomUnits(roomTypeId) {
        roomUnitSelect.innerHTML = '<option value="">-- Leave as TBA --</option>';
        roomUnitsList.innerHTML = '';

        if (!roomTypeId || !roomUnitsData[roomTypeId]) {
            roomUnitsList.innerHTML = '<p class="text-muted text-center mb-0 small"><i class="fas fa-info-circle me-1"></i>Select a room type to see available units</p>';
            return;
        }

        const units = roomUnitsData[roomTypeId];
        if (units.length === 0) {
            roomUnitsList.innerHTML = '<p class="text-warning text-center mb-0 small"><i class="fas fa-exclamation-triangle me-1"></i>No room units configured for this type</p>';
            return;
        }

        let availableCount = 0;
        let html = '<div class="small">';
        
        units.forEach(unit => {
            const statusClass = unit.is_available ? 'text-success' : 'text-danger';
            const statusIcon = unit.is_available ? 'fa-check-circle' : 'fa-times-circle';
            const statusText = unit.is_available ? 'Available' : 'Occupied';
            
            if (unit.is_available) {
                availableCount++;
                roomUnitSelect.innerHTML += `<option value="${unit.id}">Room ${unit.room_number} (Floor ${unit.floor || 'G'})</option>`;
            }

            html += `<div class="d-flex justify-content-between py-1 border-bottom">
                <span>Room ${unit.room_number} <small class="text-muted">(Floor ${unit.floor || 'G'})</small></span>
                <span class="${statusClass}"><i class="fas ${statusIcon} me-1"></i>${statusText}</span>
            </div>`;
        });

        html += '</div>';
        html = `<p class="mb-2 fw-bold"><small>${availableCount} of ${units.length} available</small></p>` + html;
        roomUnitsList.innerHTML = html;
    }

    function updatePricePreview() {
        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        const newPrice = parseFloat(selectedOption.dataset.price) || 0;
        const shouldRecalculate = recalculateCheckbox.checked;

        if (shouldRecalculate) {
            const newTotal = newPrice * nights;
            newPriceDisplay.textContent = '₦' + newTotal.toLocaleString('en-NG', {minimumFractionDigits: 2});
            priceBreakdown.textContent = `${nights} nights × ₦${newPrice.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
            
            if (newTotal > currentTotal) {
                pricePreview.classList.remove('alert-bp-info');
                pricePreview.classList.add('alert-warning');
            } else if (newTotal < currentTotal) {
                pricePreview.classList.remove('alert-bp-info', 'alert-warning');
                pricePreview.classList.add('alert-success');
            } else {
                pricePreview.classList.remove('alert-warning', 'alert-success');
                pricePreview.classList.add('alert-bp-info');
            }
        } else {
            newPriceDisplay.textContent = '₦' + currentTotal.toLocaleString('en-NG', {minimumFractionDigits: 2});
            priceBreakdown.textContent = 'Price will remain unchanged';
            pricePreview.classList.remove('alert-warning', 'alert-success');
            pricePreview.classList.add('alert-bp-info');
        }
    }

    roomTypeSelect.addEventListener('change', function() {
        updateRoomUnits(this.value);
        updatePricePreview();
    });

    recalculateCheckbox.addEventListener('change', updatePricePreview);

    // Initialize with current room type
    if (roomTypeSelect.value) {
        updateRoomUnits(roomTypeSelect.value);
    }
});
</script>
@endpush
@endsection
