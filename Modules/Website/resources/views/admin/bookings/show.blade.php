@extends('layouts.master')

@section('title', 'Booking Details')

@section('page-content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800 mb-0">Booking Details</h1>
                <p class="text-muted small mb-0">Reference: <span
                        class="fw-bold text-primary">{{ $booking->booking_reference }}</span>
                    @if($booking->booking_group_id)
                        <span class="badge bg-info ms-2">Group: {{ $booking->booking_group_id }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        {{-- ✅ NICE UI: SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-4 text-success"></i>
                    <div>
                        <strong>Success!</strong> {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ⚠️ NICE UI: ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-exclamation-circle me-2 fs-4 text-danger"></i>
                    <strong>Please fix the following errors:</strong>
                </div>
                <ul class="mb-0 mt-2 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Guest Profile Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-circle me-2"></i>Guest Profile</h6>
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
                                    <div class="bg-primary bg-gradient rounded-circle p-3 text-white me-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-1">{{ $booking->guest->title ?? '' }} {{ $booking->guest_name }}</h4>
                                        <span class="text-muted">{{ $booking->guest->occupation ?? 'Guest' }}</span>
                                        @if($booking->guest?->company_name)
                                            <br><small class="text-muted"><i class="fas fa-building me-1"></i>{{ $booking->guest->company_name }}</small>
                                        @endif
                                    </div>
                                </div>

                                <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Contact Information</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-envelope text-primary me-3" style="width: 20px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Email</small>
                                            <a href="mailto:{{ $booking->guest_email }}">{{ $booking->guest_email }}</a>
                                        </div>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-phone text-primary me-3" style="width: 20px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Phone</small>
                                            <a href="tel:{{ $booking->guest_phone }}">{{ $booking->guest_phone ?? 'N/A' }}</a>
                                        </div>
                                    </li>
                                    @if($booking->guest?->contact_number && $booking->guest->contact_number != $booking->guest_phone)
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-mobile-alt text-primary me-3" style="width: 20px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Alt. Phone</small>
                                            <a href="tel:{{ $booking->guest->contact_number }}">{{ $booking->guest->contact_number }}</a>
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                            </div>

                            {{-- Extended Guest Info (from CRM profile) --}}
                            <div class="col-md-6 ps-md-4">
                                @if($booking->guest)
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Personal Details</h6>
                                    <div class="row g-3 mb-4">
                                        @if($booking->guest->gender)
                                        <div class="col-6">
                                            <small class="text-muted d-block">Gender</small>
                                            <span class="fw-medium">{{ ucfirst($booking->guest->gender) }}</span>
                                        </div>
                                        @endif
                                        @if($booking->guest->birthday)
                                        <div class="col-6">
                                            <small class="text-muted d-block">Birthday</small>
                                            <span class="fw-medium">{{ $booking->guest->birthday->format('M d, Y') }}</span>
                                        </div>
                                        @endif
                                        @if($booking->guest->nationality)
                                        <div class="col-6">
                                            <small class="text-muted d-block">Nationality</small>
                                            <span class="fw-medium">{{ $booking->guest->nationality }}</span>
                                        </div>
                                        @endif
                                        @if($booking->guest->identification_type)
                                        <div class="col-6">
                                            <small class="text-muted d-block">ID Type</small>
                                            <span class="fw-medium">{{ ucfirst(str_replace('_', ' ', $booking->guest->identification_type)) }}</span>
                                        </div>
                                        @endif
                                        @if($booking->guest->identification_number)
                                        <div class="col-12">
                                            <small class="text-muted d-block">ID Number</small>
                                            <span class="fw-medium font-monospace">{{ $booking->guest->identification_number }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    @if($booking->guest->home_address || $booking->guest->city || $booking->guest->state)
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Address</h6>
                                    <address class="mb-0">
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
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                        <p class="mb-0">Extended profile data not available.<br>
                                        <small>Guest booked without creating a CRM profile.</small></p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Emergency Contact (if available) --}}
                        @if($booking->guest && ($booking->guest->emergency_name || $booking->guest->emergency_contact))
                        <div class="border-top mt-4 pt-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Emergency Contact</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Name</small>
                                    <span class="fw-medium">{{ $booking->guest->emergency_name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Relationship</small>
                                    <span class="fw-medium">{{ ucfirst($booking->guest->emergency_relationship ?? 'N/A') }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Contact</small>
                                    <a href="tel:{{ $booking->guest->emergency_contact }}" class="fw-medium">{{ $booking->guest->emergency_contact ?? 'N/A' }}</a>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Guest Stats (if CRM linked) --}}
                        @if($booking->guest && $booking->guest->visit_count > 0)
                        <div class="border-top mt-4 pt-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 fw-bold text-primary mb-0">{{ $booking->guest->visit_count }}</div>
                                    <small class="text-muted">Total Visits</small>
                                </div>
                                <div class="col-4">
                                    <div class="h6 fw-bold text-secondary mb-0">{{ $booking->guest->last_visit_at ? $booking->guest->last_visit_at->diffForHumans() : 'N/A' }}</div>
                                    <small class="text-muted">Last Visit</small>
                                </div>
                                <div class="col-4">
                                    <div class="h6 fw-bold mb-0">
                                        @if($booking->guest->visit_count >= 5)
                                            <span class="text-warning"><i class="fas fa-star"></i> VIP</span>
                                        @elseif($booking->guest->visit_count >= 2)
                                            <span class="text-info"><i class="fas fa-redo"></i> Returning</span>
                                        @else
                                            <span class="text-success"><i class="fas fa-user-plus"></i> New</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">Guest Type</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Reservation Info Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-bed me-2"></i>Reservation Details</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-4">
                            <div class="col-md-6 border-end">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Stay Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-users text-primary me-3" style="width: 20px;"></i>
                                    <div>
                                        <small class="text-muted d-block">Guests</small>
                                        <span class="fw-medium">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}, {{ $booking->children }} Child{{ $booking->children != 1 ? 'ren' : '' }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-moon text-primary me-3" style="width: 20px;"></i>
                                    <div>
                                        <small class="text-muted d-block">Duration</small>
                                        <span class="fw-medium">{{ $booking->check_in_date->diffInDays($booking->check_out_date) }} Night{{ $booking->check_in_date->diffInDays($booking->check_out_date) > 1 ? 's' : '' }}</span>
                                    </div>
                                </div>
                                <div class="alert alert-light border small mb-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><i class="fas fa-sign-in-alt text-success me-1"></i> Check-in:</span>
                                        <span class="fw-bold text-success">{{ $booking->check_in_date->format('D, M d, Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-sign-out-alt text-danger me-1"></i> Check-out:</span>
                                        <span class="fw-bold text-danger">{{ $booking->check_out_date->format('D, M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Room Details</h6>
                                @php
                                    // Support both new roomType and legacy room
                                    $roomInfo = $booking->roomType ?? $booking->room;
                                @endphp
                                @if ($roomInfo)
                                    <div class="d-flex align-items-start mb-3">
                                        @if ($roomInfo->image_url)
                                            <img src="{{ $roomInfo->image_url }}" class="rounded me-3"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-bed text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $roomInfo->name }}</h6>
                                            <span class="badge bg-light text-dark border">{{ $roomInfo->bed_type ?? 'Standard' }}</span>
                                            @if($booking->roomUnit)
                                                <span class="badge bg-primary ms-1">Room {{ $booking->roomUnit->room_number }}</span>
                                            @elseif($booking->roomType)
                                                <span class="badge bg-warning text-dark ms-1">Room TBA</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($roomInfo->max_occupancy)
                                    <div class="small text-muted mb-2">
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
                                        <td>Room Charge ({{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }})</td>
                                        <td class="text-end">₦{{ number_format(optional($booking->roomType)->price ?? optional($booking->room)->price ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            {{ $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1 }}</td>
                                        <td class="text-end fw-bold">₦{{ number_format($booking->total_amount, 2) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td colspan="3" class="text-end fw-bold pt-3">Grand Total</td>
                                        <td class="text-end fw-bold text-success fs-5 pt-3">
                                            ₦{{ number_format($booking->total_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Payment Details --}}
                        <div class="border-top mt-3 pt-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Amount Paid</small>
                                    <span class="fw-bold text-success">₦{{ number_format($booking->amount_paid ?? 0, 2) }}</span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Balance Due</small>
                                    <span class="fw-bold {{ ($booking->total_amount - ($booking->amount_paid ?? 0)) > 0 ? 'text-danger' : 'text-success' }}">
                                        ₦{{ number_format($booking->total_amount - ($booking->amount_paid ?? 0), 2) }}
                                    </span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Payment Method</small>
                                    <span class="fw-medium">{{ ucfirst($booking->payment_method ?? 'N/A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Special Requests & Admin Notes --}}
                <div class="row g-4 mb-4">
                    @if ($booking->special_requests)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">
                                    <i class="fas fa-comment-dots text-info me-1"></i> Special Requests
                                </h6>
                                <p class="mb-0 text-dark fst-italic">"{{ $booking->special_requests }}"</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if ($booking->admin_notes)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">
                                    <i class="fas fa-sticky-note text-warning me-1"></i> Admin Notes
                                </h6>
                                <p class="mb-0 text-dark">{{ $booking->admin_notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark">Booking Status</h6>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            @if ($booking->status === 'confirmed')
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
                            <span
                                class="fw-bold text-uppercase {{ $booking->payment_status === 'paid' ? 'text-success' : 'text-danger' }}">
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
                            @if ($booking->status === 'pending')
                                <form action="{{ route('website.admin.bookings.confirm', $booking->id) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                        <i class="fas fa-check me-2"></i> Confirm & Mark Paid
                                    </button>
                                </form>

                                <div class="alert alert-info small mb-0 mt-2">
                                    <i class="fas fa-info-circle me-1"></i> Confirming will check Frontdesk CRM for room
                                    availability first.
                                </div>
                            @endif
                            <form action="{{ route('website.admin.bookings.resend', $booking->id) }}" method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Resend confirmation email to {{ $booking->guest_email }}?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning mt-2 w-100"
                                    title="Resend Confirmation Email">
                                    <i class="fas fa-envelope me-1"></i> Resend Confirmation Email
                                </button>
                            </form>
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#moveRoomModal">
                                <i class="fas fa-exchange-alt"></i> Move Room
                            </button>

                            <div class="modal fade" id="moveRoomModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('website.admin.bookings.move', $booking->id) }}"
                                        method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Move Reservation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Current Room: <strong>{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Not Assigned' }}</strong></p>
                                            <div class="form-group">
                                                <label>Select New Room Type</label>
                                                <select name="new_room_id" class="form-select" required>
                                                    @foreach (\Modules\Website\Models\RoomType::where('is_active', true)->get() as $roomType)
                                                        <option value="{{ $roomType->id }}" {{ ($booking->room_type_id ?? $booking->room_id) == $roomType->id ? 'selected' : '' }}>{{ $roomType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="submit" class="btn btn-primary">Save Change</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                            <a href="{{ route('website.admin.bookings.edit', $booking->id) }}"
                                class="btn btn-outline-primary mt-2">
                                <i class="fas fa-edit me-2"></i> Edit Details
                            </a>

                            @if ($booking->status !== 'cancelled')
<form action="{{ route('website.admin.bookings.cancel', $booking->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger w-100 mt-2">
                                                    <i class="fas fa-ban me-2"></i> Cancel Booking
                                                </button>
                                            </form>
     @endif

                                                        <form
                                                        action="{{ route('website.admin.bookings.destroy', $booking->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('WARNING: This will permanently delete the record. Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                        class="btn btn-link text-danger text-decoration-none w-100 mt-2">
                                                        <small>Delete Record Permanently</small>
                                                        </button>
                                                        </form>
                                                        </div>
                                                        </div>
                                                        </div>

                                                        </div>
                                                        </div>
                                                        </div>
                                                    @endsection)
