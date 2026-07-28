@extends('layouts.master')

@section('title', 'Finalize Check-in')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header border-0 rounded-top-3 py-3"
                        style="background: linear-gradient(135deg, #C8A165 0%, #b08c54 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3">
                                <i class="fas fa-check-double fa-lg text-gold"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white fw-bold">Finalize Check-in for {{ $registration->full_name }}</h4>
                                <p class="mb-0 text-white opacity-75 small">Complete booking details and assign rooms</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('frontdesk.registrations.finalize', $registration) }}" method="POST"
                            novalidate id="finalize-form">
                            @csrf

                            {{-- Session Flash Messages --}}
                            @if (session('success'))
                                <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="alert alert-info border-0 bg-info bg-opacity-10 border-start border-3 border-info rounded-2 mb-4" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ session('info') }}
                                </div>
                            @endif

                            {{-- Validation Error Notification --}}
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4"
                                    role="alert">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong class="fw-bold">There were errors with your submission:</strong>
                                    </div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- BILLING RECONCILIATION ALERT (For Online Bookings with Date Discrepancies) --}}
                            @if ($registration->booking)
                                @php
                                    $booking = $registration->booking;
                                    $today = now()->startOfDay();
                                    $originalCheckIn = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
                                    $originalCheckOut = \Carbon\Carbon::parse($booking->check_out_date)->startOfDay();
                                    $actualCheckIn = $today->gt($originalCheckIn) ? $today : $originalCheckIn;
                                    $proposedCheckOut = $registration->check_out->startOfDay();
                                    
                                    $originalNights = $originalCheckIn->diffInDays($originalCheckOut);
                                    $lateArrivalDays = $today->gt($originalCheckIn) ? $originalCheckIn->diffInDays($today) : 0;
                                    $extensionDays = $proposedCheckOut->gt($originalCheckOut) ? $originalCheckOut->diffInDays($proposedCheckOut) : 0;
                                    
                                    $roomRate = $booking->room->price ?? $registration->room_rate ?? 0;
                                    $amountPaid = $booking->amount_paid ?? $booking->total_amount ?? 0;
                                    
                                    // Strict policy: Billing from original date
                                    $strictNights = $originalCheckIn->diffInDays($proposedCheckOut);
                                    $strictTotal = $strictNights * $roomRate;
                                    $strictBalance = $strictTotal - $amountPaid;
                                    
                                    // Flexible policy: Only actual nights
                                    $actualNights = $actualCheckIn->diffInDays($proposedCheckOut);
                                    $flexibleTotal = $actualNights * $roomRate;
                                    $flexibleBalance = $flexibleTotal - $amountPaid;
                                    $unusedCredit = $amountPaid - $flexibleTotal;
                                @endphp

                                @if ($lateArrivalDays > 0 || $extensionDays > 0)
                                    <div class="card border-warning mb-5">
                                        <div class="card-header bg-warning bg-opacity-25 border-0 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-warning p-2 me-3">
                                                    <i class="fas fa-calculator text-dark"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 text-dark fw-bold">Billing Reconciliation Required</h5>
                                                    <p class="mb-0 text-muted small">Guest's actual stay differs from original booking</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            {{-- Original Booking Summary --}}
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-muted mb-2"><i class="fas fa-calendar-check me-2"></i>Original Booking</h6>
                                                    <ul class="list-unstyled mb-0">
                                                        <li><strong>Dates:</strong> {{ $originalCheckIn->format('M d') }} → {{ $originalCheckOut->format('M d, Y') }}</li>
                                                        <li><strong>Nights:</strong> {{ $originalNights }} nights @ ₦{{ number_format($roomRate) }}/night</li>
                                                        <li><strong>Amount Paid:</strong> <span class="text-success fw-bold">₦{{ number_format($amountPaid) }}</span></li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-muted mb-2"><i class="fas fa-calendar-alt me-2"></i>Actual Stay</h6>
                                                    <ul class="list-unstyled mb-0">
                                                        <li><strong>Arrival:</strong> {{ $actualCheckIn->format('M d, Y') }}
                                                            @if ($lateArrivalDays > 0)
                                                                <span class="badge bg-warning text-dark">{{ $lateArrivalDays }} days late</span>
                                                            @endif
                                                        </li>
                                                        <li><strong>Checkout:</strong> {{ $proposedCheckOut->format('M d, Y') }}
                                                            @if ($extensionDays > 0)
                                                                <span class="badge bg-info">+{{ $extensionDays }} days extended</span>
                                                            @endif
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            {{-- Billing Policy Selection --}}
                                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-balance-scale me-2"></i>Select Billing Policy</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-check card p-3 border {{ $strictBalance > 0 ? 'border-danger' : 'border-success' }}">
                                                        <input class="form-check-input" type="radio" name="billing_policy" id="policy_strict" value="strict" checked>
                                                        <label class="form-check-label w-100" for="policy_strict">
                                                            <strong class="d-block mb-1">Strict Policy</strong>
                                                            <small class="text-muted d-block mb-2">No refunds for late arrival. Bill from original check-in date.</small>
                                                            <div class="bg-light p-2 rounded small">
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Total ({{ $strictNights }} nights):</span>
                                                                    <span>₦{{ number_format($strictTotal) }}</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Amount Paid:</span>
                                                                    <span class="text-success">- ₦{{ number_format($amountPaid) }}</span>
                                                                </div>
                                                                <hr class="my-1">
                                                                <div class="d-flex justify-content-between fw-bold {{ $strictBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                                    <span>{{ $strictBalance > 0 ? 'Balance Due:' : 'Credit:' }}</span>
                                                                    <span>₦{{ number_format(abs($strictBalance)) }}</span>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check card p-3 border {{ $flexibleBalance > 0 ? 'border-warning' : 'border-success' }}">
                                                        <input class="form-check-input" type="radio" name="billing_policy" id="policy_flexible" value="flexible">
                                                        <label class="form-check-label w-100" for="policy_flexible">
                                                            <strong class="d-block mb-1">Flexible Policy</strong>
                                                            <small class="text-muted d-block mb-2">Credit unused nights. Bill only actual stay.</small>
                                                            <div class="bg-light p-2 rounded small">
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Total ({{ $actualNights }} nights):</span>
                                                                    <span>₦{{ number_format($flexibleTotal) }}</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Amount Paid:</span>
                                                                    <span class="text-success">- ₦{{ number_format($amountPaid) }}</span>
                                                                </div>
                                                                <hr class="my-1">
                                                                @if ($unusedCredit > 0)
                                                                    <div class="d-flex justify-content-between fw-bold text-success">
                                                                        <span>Guest Credit:</span>
                                                                        <span>₦{{ number_format($unusedCredit) }}</span>
                                                                    </div>
                                                                    <small class="text-muted">Can apply to extension or future stay</small>
                                                                @else
                                                                    <div class="d-flex justify-content-between fw-bold text-danger">
                                                                        <span>Balance Due:</span>
                                                                        <span>₦{{ number_format(abs($flexibleBalance)) }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Hidden fields to pass calculated values --}}
                                            <input type="hidden" name="original_check_in" value="{{ $originalCheckIn->format('Y-m-d') }}">
                                            <input type="hidden" name="amount_paid_online" value="{{ $amountPaid }}">
                                            <input type="hidden" name="late_arrival_days" value="{{ $lateArrivalDays }}">
                                            <input type="hidden" name="extension_days" value="{{ $extensionDays }}">
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- SECTION 1: Guest Submitted Data (Read-only) --}}
                            <div class="card border rounded-3 mb-5">
                                <div class="card-header bg-light border-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-white p-2 me-3">
                                            <i class="fas fa-user-check text-gold"></i>
                                        </div>
                                        <h5 class="mb-0 text-dark fw-bold">Guest Submitted Information (Review)</h5>
                                    </div>
                                </div>

                                <div class="card-body">
                                    {{-- Row 1: Basic Info + Signature --}}
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-user me-1"></i>Lead Guest</label>
                                                    <p class="fw-bold text-dark mb-0 fs-5">
                                                        {{ $registration->title }} {{ $registration->full_name }}
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-venus-mars me-1"></i>Gender</label>
                                                    <p class="fw-bold text-dark mb-0">
                                                        {{ ucfirst($registration->gender ?? 'Not specified') }}
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-phone me-1"></i>Phone</label>
                                                    <p class="fw-bold text-dark mb-0">{{ $registration->contact_number ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-envelope me-1"></i>Email</label>
                                                    <p class="fw-bold text-dark mb-0">{{ $registration->email ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <label class="form-label text-muted small mb-2">Guest Signature</label>
                                            @if ($registration->guest_signature)
                                                <img src="{{ Storage::url($registration->guest_signature) }}"
                                                    alt="Guest Signature" class="img-fluid border rounded bg-white p-2"
                                                    style="max-height: 100px;">
                                            @else
                                                <div class="border rounded p-3 bg-light">
                                                    <i class="fas fa-signature fa-2x text-muted mb-1"></i>
                                                    <p class="text-danger small mb-0">No signature</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Row 2: Stay Details + Additional Info --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-calendar-alt me-1"></i>Stay Duration</label>
                                            <p class="fw-bold text-dark mb-0">
                                                {{ $registration->check_in->format('M d, Y') }} → {{ $registration->check_out->format('M d, Y') }}
                                                <span class="badge bg-secondary ms-1">{{ $registration->no_of_nights ?? $registration->check_in->diffInDays($registration->check_out) }} nights</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-users me-1"></i>Total Guests</label>
                                            <p class="fw-bold text-dark mb-0">{{ $registration->no_of_guests ?? 1 }} guest(s)</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-globe me-1"></i>Nationality</label>
                                            <p class="fw-bold text-dark mb-0">{{ $registration->nationality ?? ($registration->guest->nationality ?? 'Not specified') }}</p>
                                        </div>
                                    </div>

                                    {{-- Row 3: Address & Personal Details --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-home me-1"></i>Home Address</label>
                                            <p class="fw-bold text-dark mb-0">{{ $registration->home_address ?? ($registration->guest->home_address ?? 'Not provided') }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-briefcase me-1"></i>Occupation</label>
                                            <p class="fw-bold text-dark mb-0">{{ $registration->occupation ?? ($registration->guest->occupation ?? 'N/A') }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-building me-1"></i>Company</label>
                                            <p class="fw-bold text-dark mb-0">{{ $registration->company_name ?? ($registration->guest->company_name ?? 'N/A') }}</p>
                                        </div>
                                    </div>

                                    {{-- Row 4: Emergency Contact --}}
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label text-muted small mb-1"><i class="fas fa-phone-alt text-danger me-1"></i>Emergency Contact</label>
                                            <div class="d-flex flex-wrap gap-4">
                                                <span class="fw-bold text-dark">
                                                    <i class="fas fa-user-shield text-muted me-1"></i>
                                                    {{ $registration->emergency_name ?? ($registration->guest->emergency_name ?? 'Not provided') }}
                                                </span>
                                                @if ($registration->emergency_contact ?? $registration->guest->emergency_contact ?? null)
                                                    <span class="fw-bold text-dark">
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        {{ $registration->emergency_contact ?? $registration->guest->emergency_contact }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BOOKED ROOM TYPE FROM WEBSITE (For FDA Reference) --}}
                            @if ($registration->booking_id && $registration->booking)
                                @php
                                    $linkedBooking = $registration->booking;
                                    $bookedRoomType = $linkedBooking->roomType;
                                @endphp
                                <div class="card border border-success rounded-3 mb-5">
                                    <div class="card-header bg-success bg-opacity-10 border-0 py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success bg-opacity-25 p-2 me-3">
                                                    <i class="fas fa-bed text-success"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 text-dark fw-bold">Booked Room Type (Website Selection)</h5>
                                                    <p class="mb-0 text-muted small">Assign a room unit from this type to the guest</p>
                                                </div>
                                            </div>
                                            <span class="badge bg-success fs-6">{{ $linkedBooking->booking_reference }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 align-items-center">
                                            @if ($bookedRoomType)
                                                <div class="col-md-2 text-center">
                                                    @if ($bookedRoomType->image_url)
                                                        <img src="{{ asset('storage/' . $bookedRoomType->image_url) }}" 
                                                            alt="{{ $bookedRoomType->name }}" 
                                                            class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded p-3">
                                                            <i class="fas fa-door-open fa-2x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <h5 class="fw-bold text-success mb-1">{{ $bookedRoomType->name }}</h5>
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-bed me-1"></i>{{ $bookedRoomType->bed_type ?? 'Standard' }} | 
                                                        <i class="fas fa-users me-1"></i>Max {{ $bookedRoomType->capacity }} guests |
                                                        <i class="fas fa-expand me-1"></i>{{ $bookedRoomType->size ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small mb-1">Booked Rate</label>
                                                    <p class="fw-bold text-dark fs-5 mb-0">₦{{ number_format($bookedRoomType->price) }}<small class="text-muted">/night</small></p>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small mb-1">Payment Status</label>
                                                    <p class="mb-0">
                                                        @if ($linkedBooking->payment_status === 'paid')
                                                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Paid (₦{{ number_format($linkedBooking->amount_paid) }})</span>
                                                        @elseif ($linkedBooking->payment_status === 'partial')
                                                            <span class="badge bg-warning fs-6"><i class="fas fa-exclamation-circle me-1"></i>Partial (₦{{ number_format($linkedBooking->amount_paid) }})</span>
                                                        @else
                                                            <span class="badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i>Unpaid</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            @else
                                                <div class="col-12">
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        Room type information not available. Please assign any appropriate room.
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($linkedBooking->special_requests)
                                                <div class="col-12 mt-2">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-comment-alt me-1"></i>Special Requests</label>
                                                    <div class="alert alert-info mb-0 py-2">
                                                        {{ $linkedBooking->special_requests }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ORIGINAL BOOKING DATES (Immutable - From Web Booking) --}}
                            @if ($registration->booking_id && $registration->original_check_in_date)
                                <div class="card border border-info rounded-3 mb-5">
                                    <div class="card-header bg-info bg-opacity-10 border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-25 p-2 me-3">
                                                <i class="fas fa-lock text-info"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 text-dark fw-bold">Original Booking Dates (Locked)</h5>
                                                <p class="mb-0 text-muted small">These dates are immutable and locked from the original web booking for audit purposes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1"><i class="fas fa-calendar-plus me-1"></i>Original Check-in</label>
                                                <p class="fw-bold text-dark mb-0 fs-5">
                                                    {{ $registration->original_check_in_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1"><i class="fas fa-calendar-minus me-1"></i>Original Check-out</label>
                                                <p class="fw-bold text-dark mb-0 fs-5">
                                                    {{ $registration->original_check_out_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1"><i class="fas fa-moon me-1"></i>Original Nights</label>
                                                <p class="fw-bold text-dark mb-0 fs-5">
                                                    {{ $registration->original_check_in_date->diffInDays($registration->original_check_out_date) }} nights
                                                </p>
                                            </div>
                                            @if ($registration->booking_group_id)
                                                <div class="col-12">
                                                    <label class="form-label text-muted small mb-1"><i class="fas fa-layer-group me-1"></i>Group Reference</label>
                                                    <p class="fw-bold text-info mb-0">
                                                        {{ $registration->booking_group_id }}
                                                        <span class="badge bg-info ms-2">Multi-Room Booking</span>
                                                    </p>
                                                </div>
                                            @endif
                                            @if ($registration->hasDateAdjustments())
                                                <div class="col-12">
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <strong>Note:</strong> Stay dates have been adjusted from the original booking.
                                                        Current stay: {{ $registration->check_in->format('M d') }} → {{ $registration->check_out->format('M d, Y') }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        {{-- Hidden fields for audit trail --}}
                                        <input type="hidden" name="original_check_in_date" value="{{ $registration->original_check_in_date?->format('Y-m-d') ?? $registration->check_in->format('Y-m-d') }}">
                                        <input type="hidden" name="original_check_out_date" value="{{ $registration->original_check_out_date?->format('Y-m-d') ?? $registration->check_out->format('Y-m-d') }}">
                                        @if ($registration->booking_group_id)
                                            <input type="hidden" name="booking_group_id" value="{{ $registration->booking_group_id }}">
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- SECTION 2: Front Desk Finalization --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-edit text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Front Desk Booking Details (Finalize)</h5>
                                </div>

                                {{-- Group Lead's Details --}}
                                <div class="card border rounded-3 mb-4">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="fas fa-user-tie text-gold me-2"></i>
                                            Group Lead's Booking
                                        </h6>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-3">
                                            @php
                                                // Determine the booked room type ID from web booking (if exists)
                                                $bookedRoomTypeId = $registration->booking?->room_type_id ?? $registration->room_type_id;
                                            @endphp
                                            <div class="col-md-6">
                                                <label for="room_unit_id" class="form-label fw-semibold text-dark">
                                                    Assign Room <span class="text-danger">*</span>
                                                    @if ($bookedRoomTypeId && $registration->booking)
                                                        <small class="text-success">(Guest booked: {{ $registration->booking->roomType->name ?? 'Unknown' }})</small>
                                                    @endif
                                                </label>
                                                {{-- HIDDEN: Stores text name for backward compatibility --}}
                                                <input type="hidden" name="room_allocation" id="lead_room_text"
                                                    value="{{ old('room_allocation', $registration->room_allocation) }}">
                                                {{-- HIDDEN: Stores room_type_id --}}
                                                <input type="hidden" name="room_type_id" id="lead_room_type_id"
                                                    value="{{ old('room_type_id', $registration->room_type_id ?? $bookedRoomTypeId) }}">

                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-door-closed text-muted"></i>
                                                    </span>
                                                    {{-- Select Dropdown using RoomUnits grouped by RoomType --}}
                                                    <select class="form-select @error('room_unit_id') is-invalid @enderror"
                                                        name="room_unit_id" id="room_unit_id"
                                                        onchange="updateLeadRoomDetails(this)">
                                                        <option value="">Select a Room...</option>
                                                        @foreach ($roomTypes as $roomType)
                                                            @if ($roomType->units->count() > 0)
                                                                @php
                                                                    // Highlight the booked room type
                                                                    $isBookedType = $bookedRoomTypeId && $roomType->id == $bookedRoomTypeId;
                                                                    $optGroupLabel = $roomType->name . ' (₦' . number_format($roomType->price) . '/night)';
                                                                    if ($isBookedType) {
                                                                        $optGroupLabel = '★ ' . $optGroupLabel . ' - BOOKED';
                                                                    }
                                                                @endphp
                                                                <optgroup label="{{ $optGroupLabel }}">
                                                                    @foreach ($roomType->units as $unit)
                                                                        @php
                                                                            $isAvailable = $availableUnits->contains('id', $unit->id);
                                                                        @endphp
                                                                        <option value="{{ $unit->id }}"
                                                                            data-name="{{ $unit->room_number }} ({{ $roomType->name }})"
                                                                            data-price="{{ $roomType->price }}"
                                                                            data-capacity="{{ $roomType->capacity }}"
                                                                            data-room-type-id="{{ $roomType->id }}"
                                                                            {{ !$isAvailable ? 'disabled' : '' }}
                                                                            @selected(old('room_unit_id', $registration->room_unit_id) == $unit->id)>
                                                                            Room {{ $unit->room_number }}{{ $isBookedType ? ' ★' : '' }}{{ !$isAvailable ? ' (Occupied)' : '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                        @endforeach
                                                        {{-- Legacy rooms fallback --}}
                                                        @if ($rooms->count() > 0)
                                                            <optgroup label="Legacy Rooms">
                                                                @foreach ($rooms as $room)
                                                                    <option value="legacy_{{ $room->id }}"
                                                                        data-name="{{ $room->name }}"
                                                                        data-price="{{ $room->price }}"
                                                                        data-capacity="{{ $room->capacity }}"
                                                                        data-legacy="true"
                                                                        @selected(old('room_id', $registration->room_id) == $room->id && !$registration->room_unit_id)>
                                                                        {{ $room->name }} (Cap: {{ $room->capacity }})
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                    </select>
                                                    @error('room_unit_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    @error('room_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="room_rate" class="form-label fw-semibold text-dark">
                                                    Room Rate (per night) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-money-bill-wave text-muted"></i>
                                                    </span>
                                                    <input type="text"
                                                        class="form-control @error('room_rate') is-invalid @enderror"
                                                        name="room_rate" id="room_rate" {{-- FIX: Check Old Input -> Then Check DB Value (Formatted) --}}
                                                        value="{{ old('room_rate', $registration->room_rate ? number_format($registration->room_rate) : '') }}"
                                                        placeholder="e.g. 50,000">
                                                    @error('room_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <small id="negotiated-rate-info" class="text-success d-none"></small>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="bed_breakfast"
                                                        value="1" id="bed_breakfast" {{-- FIX: Check Old Input -> Then Check DB Value --}}
                                                        @checked(old('bed_breakfast', $registration->bed_breakfast))>
                                                    <label class="form-check-label fw-semibold text-dark"
                                                        for="bed_breakfast">
                                                        <i class="fas fa-coffee text-gold me-1"></i>
                                                        Include Bed & Breakfast
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Group Members Table --}}
                                @if ($registration->is_group_lead && $groupMembers->count() > 0)
                                    <div class="card border rounded-3 mb-4">
                                        <div class="card-header bg-light border-0 py-3">
                                            <h6 class="mb-0 text-dark fw-bold">
                                                <i class="fas fa-users text-gold me-2"></i>
                                                Group Member Bookings ({{ $groupMembers->count() }} members)
                                            </h6>
                                        </div>

                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="border-0 py-3 ps-4">Member Name</th>
                                                            <th class="border-0 py-3">Assign Room*</th>
                                                            <th class="border-0 py-3">Room Rate*</th>
                                                            <th class="border-0 py-3 text-center">B&B</th>
                                                            <th class="border-0 py-3 pe-4">Status*</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($groupMembers as $member)
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rounded-circle bg-light p-1 me-2">
                                                                            <i class="fas fa-user fa-sm text-gold"></i>
                                                                        </div>
                                                                        <span
                                                                            class="fw-semibold text-dark">{{ $member->full_name }}</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    {{-- UPDATED: Member Room Select --}}
                                                                    <input type="hidden"
                                                                        name="group_members[{{ $member->id }}][room_allocation]"
                                                                        id="member_room_text_{{ $member->id }}"
                                                                        value="{{ old('group_members.' . $member->id . '.room_allocation', $member->room_allocation) }}">
                                                                    <input type="hidden"
                                                                        name="group_members[{{ $member->id }}][room_type_id]"
                                                                        id="member_room_type_{{ $member->id }}"
                                                                        value="{{ old('group_members.' . $member->id . '.room_type_id', $member->room_type_id) }}">

                                                                    <select
                                                                        class="form-select form-select-sm @error('group_members.' . $member->id . '.room_unit_id') is-invalid @enderror"
                                                                        name="group_members[{{ $member->id }}][room_unit_id]"
                                                                        onchange="updateMemberRoomDetails(this, {{ $member->id }})">
                                                                        <option value="">Select...</option>
                                                                        @foreach ($roomTypes as $roomType)
                                                                            @if ($roomType->units->count() > 0)
                                                                                <optgroup label="{{ $roomType->name }}">
                                                                                    @foreach ($roomType->units as $unit)
                                                                                        @php
                                                                                            $isAvailable = $availableUnits->contains('id', $unit->id);
                                                                                        @endphp
                                                                                        <option value="{{ $unit->id }}"
                                                                                            data-name="{{ $unit->room_number }} ({{ $roomType->name }})"
                                                                                            data-price="{{ $roomType->price }}"
                                                                                            data-room-type-id="{{ $roomType->id }}"
                                                                                            {{ !$isAvailable ? 'disabled' : '' }}
                                                                                            @selected(old('group_members.' . $member->id . '.room_unit_id', $member->room_unit_id) == $unit->id)>
                                                                                            Room {{ $unit->room_number }} {{ !$isAvailable ? '(Occupied)' : '' }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </optgroup>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                    @error('group_members.' . $member->id . '.room_unit_id')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span
                                                                            class="input-group-text bg-light border-end-0">₦</span>
                                                                        {{-- UX FIX: Changed type="number" to "text" to support commas --}}
                                                                        <input type="text"
                                                                            class="form-control member-rate-input @error('group_members.' . $member->id . '.room_rate') is-invalid @enderror"
                                                                            name="group_members[{{ $member->id }}][room_rate]"
                                                                            id="member_rate_{{ $member->id }}"
                                                                            placeholder="Rate"
                                                                            value="{{ old('group_members.' . $member->id . '.room_rate', $member->room_rate ? number_format($member->room_rate) : '') }}">
                                                                    </div>
                                                                    @error('group_members.' . $member->id . '.room_rate')
                                                                        <div class="invalid-feedback d-block">
                                                                            {{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="form-check d-inline-block">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="group_members[{{ $member->id }}][bed_breakfast]"
                                                                            value="1" {{-- FIX: Check Old Input -> Then Check Member DB Value --}}
                                                                            @checked(old('group_members.' . $member->id . '.bed_breakfast', $member->bed_breakfast))>
                                                                    </div>
                                                                </td>
                                                                <td class="pe-4">
                                                                    <select
                                                                        class="form-select form-select-sm @error('group_members.' . $member->id . '.status') is-invalid @enderror"
                                                                        name="group_members[{{ $member->id }}][status]">
                                                                        {{-- FIX: Default to existing status or 'checked_in' --}}
                                                                        @php
                                                                            $mStatus = old(
                                                                                'group_members.' .
                                                                                    $member->id .
                                                                                    '.status',
                                                                                $member->stay_status,
                                                                            );
                                                                            // Map 'draft_by_guest' to 'checked_in' for the dropdown initial state
                                                                            if ($mStatus === 'draft_by_guest') {
                                                                                $mStatus = 'checked_in';
                                                                            }
                                                                        @endphp
                                                                        <option value="checked_in"
                                                                            @selected($mStatus == 'checked_in')>Check-in</option>
                                                                        <option value="no_show"
                                                                            @selected($mStatus == 'no_show')>No-Show</option>
                                                                    </select>
                                                                    @error('group_members.' . $member->id . '.status')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Overall Booking Details --}}
                                <div class="card border rounded-3">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="fas fa-cog text-gold me-2"></i>
                                            Overall Booking Details
                                        </h6>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-3">
                                            @if ($registration->is_group_lead)
                                                <div class="col-md-6">
                                                    <label for="billing_type" class="form-label fw-semibold text-dark">
                                                        Billing Method <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="billing_type"
                                                        class="form-select @error('billing_type') is-invalid @enderror">
                                                        <option value="consolidate" @selected(old('billing_type', $registration->billing_type) == 'consolidate')>
                                                            Consolidate on Group Lead
                                                        </option>
                                                        <option value="individual" @selected(old('billing_type', $registration->billing_type) == 'individual')>
                                                            Individual Billing (Each Pays Own)
                                                        </option>
                                                    </select>
                                                    @error('billing_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endif

                                            <div class="col-md-6">
                                                <label for="guest_type_id" class="form-label fw-semibold text-dark">
                                                    Guest Type <span class="text-danger">*</span>
                                                </label>
                                                <select name="guest_type_id"
                                                    class="form-select @error('guest_type_id') is-invalid @enderror">
                                                    @foreach ($guestTypes as $type)
                                                        <option value="{{ $type->id }}" {{-- FIX: Check Old Input -> Then Check DB Value --}}
                                                            @selected(old('guest_type_id', $registration->guest_type_id) == $type->id)>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('guest_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="booking_source_id" class="form-label fw-semibold text-dark">
                                                    Booking Source <span class="text-danger">*</span>
                                                </label>
                                                @php
                                                    // Auto-default to 'Website' (ID: 2) for web bookings
                                                    $defaultBookingSource = $registration->booking_source_id;
                                                    if (!$defaultBookingSource && $registration->booking_id) {
                                                        $defaultBookingSource = 2; // Website
                                                    }
                                                @endphp
                                                <select name="booking_source_id"
                                                    class="form-select @error('booking_source_id') is-invalid @enderror">
                                                    @foreach ($bookingSources as $source)
                                                        <option value="{{ $source->id }}"
                                                            @selected(old('booking_source_id', $defaultBookingSource) == $source->id)>
                                                            {{ $source->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('booking_source_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="payment_method" class="form-label fw-semibold text-dark">
                                                    Payment Method <span class="text-danger">*</span>
                                                </label>
                                                @php
                                                    // Default to 'online' for web bookings that are paid
                                                    $defaultPaymentMethod = $registration->payment_method;
                                                    if (!$defaultPaymentMethod && $registration->booking_id && $registration->booking?->payment_status === 'paid') {
                                                        $defaultPaymentMethod = 'online';
                                                    }
                                                @endphp
                                                <select name="payment_method"
                                                    class="form-select @error('payment_method') is-invalid @enderror">
                                                    <option value="online" @selected(old('payment_method', $defaultPaymentMethod) == 'online')>Online Payment (Website)</option>
                                                    <option value="pos" @selected(old('payment_method', $defaultPaymentMethod) == 'pos')>POS</option>
                                                    <option value="cash" @selected(old('payment_method', $defaultPaymentMethod) == 'cash')>Cash</option>
                                                    <option value="transfer" @selected(old('payment_method', $defaultPaymentMethod) == 'transfer')>Transfer</option>
                                                    <option value="credit_balance" @selected(old('payment_method', $defaultPaymentMethod) == 'credit_balance')>Credit Balance</option>
                                                    <option value="credit" @selected(old('payment_method', $defaultPaymentMethod) == 'credit')>Credit from other branches</option>
                                                </select>
                                                @error('payment_method')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                {{-- Left Side: No-Show Button --}}
                                <div>
                                    <button type="button" class="btn btn-outline-danger px-4" 
                                        data-bs-toggle="modal" data-bs-target="#noShowModal">
                                        <i class="fas fa-user-slash me-2"></i> Mark as No-Show
                                    </button>
                                </div>
                                
                                {{-- Right Side: Cancel & Submit --}}
                                <div class="d-flex gap-2">
                                    <a href="{{ route('frontdesk.registrations.index') }}"
                                        class="btn btn-outline-dark px-4">
                                        <i class="fas fa-times me-2"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-gold px-4">
                                        @if ($registration->is_group_lead)
                                            <i class="fas fa-users me-2"></i> Complete Group Check-in
                                        @else
                                            <i class="fas fa-check-double me-2"></i> Complete Check-in
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- No-Show Confirmation Modal --}}
                        <div class="modal fade" id="noShowModal" tabindex="-1" aria-labelledby="noShowModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger bg-opacity-10 border-0">
                                        <h5 class="modal-title text-danger" id="noShowModalLabel">
                                            <i class="fas fa-user-slash me-2"></i>Confirm No-Show
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center mb-4">
                                            <div class="rounded-circle bg-danger bg-opacity-10 p-4 d-inline-block mb-3">
                                                <i class="fas fa-user-slash fa-3x text-danger"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark">Mark {{ $registration->full_name }} as No-Show?</h5>
                                        </div>
                                        
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>This action will:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Mark this registration as "No-Show"</li>
                                                @if ($registration->is_group_lead && $groupMembers->count() > 0)
                                                    <li>Mark all <strong>{{ $groupMembers->count() }}</strong> group member(s) as No-Show</li>
                                                @endif
                                                @if ($registration->booking_id)
                                                    <li>Update the linked booking status to "No-Show"</li>
                                                @endif
                                                <li>Release any reserved room inventory</li>
                                            </ul>
                                        </div>

                                        <div class="bg-light rounded p-3">
                                            <div class="row g-2 small">
                                                <div class="col-6">
                                                    <span class="text-muted">Scheduled Check-in:</span><br>
                                                    <strong>{{ $registration->check_in->format('M d, Y') }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted">Scheduled Check-out:</span><br>
                                                    <strong>{{ $registration->check_out->format('M d, Y') }}</strong>
                                                </div>
                                                @if ($registration->booking_id && $registration->booking)
                                                    <div class="col-6">
                                                        <span class="text-muted">Booking Ref:</span><br>
                                                        <strong>{{ $registration->booking->booking_reference }}</strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted">Amount Paid:</span><br>
                                                        <strong class="text-success">₦{{ number_format($registration->booking->amount_paid ?? 0) }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <form action="{{ route('frontdesk.registrations.no-show', $registration) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-user-slash me-1"></i> Confirm No-Show
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script for Comma Formatting & Auto-Fill --}}
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('finalize-form');
        
        // --- 1. GLOBAL CURRENCY FORMATTER (Handles Lead & Members) ---
        function formatCurrencyInput(input) {
            // Strip non-numeric except dot
            let value = input.value.replace(/[^0-9.]/g, '');
            if (value !== '') {
                // Split decimals to protect pennies
                let parts = value.split('.');
                parts[0] = Number(parts[0]).toLocaleString(); 
                input.value = parts.join('.');
            }
        }

        // Attach to Lead Rate
        const leadRate = document.getElementById('room_rate');
        if(leadRate) {
            leadRate.addEventListener('input', function() { formatCurrencyInput(this); });
        }

        // Attach to Member Rates
        document.querySelectorAll('.member-rate-input').forEach(input => {
            input.addEventListener('input', function() { formatCurrencyInput(this); });
        });

        // --- 2. STRIP COMMAS ON SUBMIT (CRITICAL) ---
        if (form) {
            form.addEventListener('submit', function() {
                // Strip Lead
                if(leadRate) leadRate.value = leadRate.value.replace(/,/g, '');
                
                // Strip Members
                document.querySelectorAll('.member-rate-input').forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });
            });
        }
    });

    // --- 3. AUTO-FILL LOGIC ---
    function updateLeadRoomDetails(select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const roomName = option.getAttribute('data-name');
        const roomTypeId = option.getAttribute('data-room-type-id');
        
        // Update room rate
        const rateInput = document.getElementById('room_rate');
        if (price && rateInput) {
            rateInput.value = parseFloat(price).toLocaleString();
        }
        
        // Update hidden fields for room allocation and type
        const roomTextInput = document.getElementById('lead_room_text');
        if (roomName && roomTextInput) {
            roomTextInput.value = roomName;
        }
        
        const roomTypeInput = document.getElementById('lead_room_type_id');
        if (roomTypeId && roomTypeInput) {
            roomTypeInput.value = roomTypeId;
        }
    }

    function updateMemberRoomDetails(select, memberId) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const roomName = option.getAttribute('data-name');
        const roomTypeId = option.getAttribute('data-room-type-id');

        // Update room rate
        const rateInput = document.getElementById('member_rate_' + memberId);
        if (price && rateInput) {
            rateInput.value = parseFloat(price).toLocaleString();
        }
        
        // Update hidden fields
        const roomTextInput = document.getElementById('member_room_text_' + memberId);
        if (roomName && roomTextInput) {
            roomTextInput.value = roomName;
        }
        
        const roomTypeInput = document.getElementById('member_room_type_' + memberId);
        if (roomTypeId && roomTypeInput) {
            roomTypeInput.value = roomTypeId;
        }
    }

    // --- 4. NEGOTIATED RATE LOOKUP (GuestType + RoomType → Rate) ---
    function lookupNegotiatedRate() {
        const guestTypeId = document.getElementById('guest_type_id')?.value;
        const roomTypeId = document.getElementById('lead_room_type_id')?.value;
        const rateInput = document.getElementById('room_rate');
        const rateInfo = document.getElementById('negotiated-rate-info');

        if (!guestTypeId || !roomTypeId || !rateInput) return;

        fetch(`{{ route('frontdesk.guest-types.negotiated-rate', ['guestType' => '__GT__', 'roomTypeId' => '__RT__']) }}`
            .replace('__GT__', guestTypeId).replace('__RT__', roomTypeId))
            .then(r => r.json())
            .then(data => {
                if (data.has_negotiated_rate) {
                    rateInput.value = parseFloat(data.rate).toLocaleString();
                    rateInput.classList.add('border-success');
                    if (rateInfo) {
                        rateInfo.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Negotiated rate applied';
                        rateInfo.classList.remove('d-none');
                    }
                } else {
                    rateInput.classList.remove('border-success');
                    if (rateInfo) {
                        rateInfo.classList.add('d-none');
                    }
                }
            })
            .catch(() => {});
    }

    document.getElementById('guest_type_id')?.addEventListener('change', lookupNegotiatedRate);
    document.getElementById('room_unit_id')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const roomTypeId = option.getAttribute('data-room-type-id');
        if (roomTypeId) {
            document.getElementById('lead_room_type_id').value = roomTypeId;
            lookupNegotiatedRate();
        }
    });
</script>
@endsection
