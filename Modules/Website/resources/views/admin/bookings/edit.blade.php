@extends('layouts.master')

@section('title', 'Edit Booking - ' . $booking->booking_reference)

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

    .edit-booking { font-family: 'Proxima Nova', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .text-bp-gold { color: var(--bp-gold) !important; }
    .text-bp-charcoal { color: var(--bp-charcoal) !important; }

    .bp-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
    .bp-card .card-header { background: var(--bp-white); border-bottom: 2px solid var(--bp-neutral-dark); padding: 1rem 1.25rem; }
    .bp-card .card-header h6 { color: var(--bp-charcoal); font-weight: 700; letter-spacing: 0.5px; margin: 0; }
    .bp-card .card-header h6 i { color: var(--bp-gold); }

    .section-label {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        color: var(--bp-gold); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--bp-neutral-dark);
    }
    .summary-box { background: var(--bp-neutral); border-radius: 10px; padding: 1rem; border-left: 4px solid var(--bp-gold); }

    .form-label { font-weight: 600; color: var(--bp-charcoal); font-size: 0.85rem; }
    .form-select, .form-control { border-radius: 8px; border-color: var(--bp-neutral-dark); font-size: 0.9rem; }
    .form-select:focus, .form-control:focus { border-color: var(--bp-gold); box-shadow: 0 0 0 0.2rem rgba(200,161,101,0.15); }
    .form-control:disabled, .form-select:disabled, .form-control[readonly] {
        background-color: #f8f9fa; opacity: 0.7; cursor: not-allowed;
    }

    .btn-bp-gold { background: linear-gradient(135deg, var(--bp-gold), var(--bp-gold-dark)); color: #fff; border: none; font-weight: 600; padding: 0.6rem 1.5rem; border-radius: 8px; }
    .btn-bp-gold:hover { color: #fff; box-shadow: 0 4px 14px rgba(200,161,101,0.4); transform: translateY(-1px); }

    .status-badge { display: inline-block; padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-confirmed { background: rgba(74,222,128,0.15); color: #16a34a; border: 1px solid rgba(74,222,128,0.3); }
    .status-pending { background: rgba(200,161,101,0.15); color: var(--bp-gold-dark); border: 1px solid rgba(200,161,101,0.3); }
    .status-cancelled { background: rgba(239,68,68,0.15); color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
    .status-checked_in { background: rgba(14,165,233,0.15); color: #0284c7; border: 1px solid rgba(14,165,233,0.3); }
    .status-completed { background: rgba(168,85,247,0.15); color: #7c3aed; border: 1px solid rgba(168,85,247,0.3); }

    .pay-badge-paid { background: rgba(74,222,128,0.15); color: #16a34a; border: 1px solid rgba(74,222,128,0.3); }
    .pay-badge-pending { background: rgba(251,191,36,0.15); color: #d97706; border: 1px solid rgba(251,191,36,0.3); }
    .pay-badge-failed { background: rgba(239,68,68,0.15); color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
    .pay-badge-partial { background: rgba(14,165,233,0.15); color: #0284c7; border: 1px solid rgba(14,165,233,0.3); }

    .date-display { background: var(--bp-neutral); border-radius: 10px; padding: 0.75rem 1rem; border-left: 4px solid var(--bp-gold); margin-bottom: 1rem; }
    .date-display .date-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
    .date-display .date-value { font-weight: 700; color: var(--bp-charcoal); }

    .readonly-row { padding: 0.6rem 0; border-bottom: 1px solid var(--bp-neutral-dark); }
    .readonly-row:last-child { border-bottom: none; }
    .readonly-label { font-size: 0.8rem; color: #888; }
    .readonly-value { font-weight: 600; color: var(--bp-charcoal); font-size: 0.9rem; }

    .locked-overlay {
        position: relative;
    }
    .locked-overlay::after {
        content: '\f023  Editing locked';
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(248,249,250,0.85); display: flex; align-items: center; justify-content: center;
        font-family: 'Segoe UI', sans-serif; font-size: 0.85rem; color: #999; font-weight: 600;
        border-radius: 12px; z-index: 10;
    }

    .action-strip { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .action-strip .btn { font-size: 0.8rem; padding: 0.4rem 0.9rem; border-radius: 6px; }
</style>
@endsection

@php
    $isLocked = in_array($booking->status, ['completed', 'cancelled']);
    $isGuestLocked = in_array($booking->status, ['completed']);
    $isRoomLocked = in_array($booking->status, ['checked_in', 'completed', 'cancelled']);
    $isDateLocked = in_array($booking->status, ['checked_in', 'completed', 'cancelled']);
    $isPaid = $booking->payment_status === 'paid';
    $balanceDue = $booking->total_amount - ($booking->amount_paid ?? 0);
@endphp

@section('page-content')
<div class="container-fluid py-4 edit-booking">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-light mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Booking
            </a>
            <h1 class="h3 text-bp-charcoal mb-1" style="font-weight: 700;">Edit Booking</h1>
            <p class="mb-0">
                <span class="text-muted">Reference:</span>
                <span class="fw-bold text-bp-gold">{{ $booking->booking_reference }}</span>
                <span class="status-badge status-{{ $booking->status }} ms-2">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                <span class="status-badge pay-badge-{{ $booking->payment_status }} ms-1">{{ ucfirst($booking->payment_status) }}</span>
            </p>
        </div>
        @if($isLocked)
            <div class="text-end">
                <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i> Booking {{ ucfirst($booking->status) }} — Limited editing</span>
            </div>
        @endif
    </div>

    {{-- Success / Error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" style="border-radius: 10px;">
            <div class="d-flex align-items-center"><i class="fas fa-check-circle me-2 fs-4"></i><div><strong>Success!</strong> {{ session('success') }}</div></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" style="border-radius: 10px;">
            <div class="d-flex align-items-center mb-1"><i class="fas fa-exclamation-circle me-2 fs-4"></i><strong>Please fix the following errors:</strong></div>
            <ul class="mb-0 mt-2 ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('website.admin.bookings.update', $booking->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ========== LEFT COLUMN ========== --}}
            <div class="col-lg-8">

                {{-- Room & Dates --}}
                <div class="card bp-card mb-4 {{ $isRoomLocked ? 'locked-overlay' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-bed me-2"></i>Room & Dates</h6>
                        @if($isRoomLocked)
                            <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i> Locked</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Room Type --}}
                            <div class="col-md-12">
                                <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                @if($isRoomLocked)
                                    <input type="hidden" name="room_type_id" value="{{ $booking->room_type_id }}">
                                    <input type="text" class="form-control" value="{{ optional($booking->roomType)->name ?? 'N/A' }}" disabled>
                                @else
                                    <select name="room_type_id" id="roomTypeSelect" class="form-select" required>
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type->id }}" data-price="{{ $type->price }}" {{ $booking->room_type_id == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }} — ₦{{ number_format($type->price, 2) }}/night
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- Room Unit --}}
                            <div class="col-md-12">
                                <label class="form-label">Room Unit <small class="text-muted fw-normal">({{ $isRoomLocked ? 'managed via room assignment' : 'optional — assign later at check-in' }})</small></label>
                                @if($isRoomLocked)
                                    <input type="hidden" name="room_unit_id" value="{{ $booking->room_unit_id }}">
                                    <input type="text" class="form-control" value="{{ $booking->roomUnit ? 'Room ' . $booking->roomUnit->room_number . ' (Floor ' . ($booking->roomUnit->floor ?? 'G') . ')' : 'Not Assigned' }}" disabled>
                                @else
                                    <select name="room_unit_id" id="roomUnitSelect" class="form-select">
                                        <option value="">-- Leave as TBA --</option>
                                        @if($booking->roomType)
                                            @foreach($booking->roomType->units()->where('status', 'available')->orderBy('room_number')->get() as $unit)
                                                @php
                                                    $isAvailable = $unit->isAvailableForDates($booking->check_in_date->format('Y-m-d'), $booking->check_out_date->format('Y-m-d'), $booking->id);
                                                    $isCurrent = $booking->room_unit_id == $unit->id;
                                                @endphp
                                                <option value="{{ $unit->id }}" {{ $isCurrent ? 'selected' : '' }} {{ !$isAvailable && !$isCurrent ? 'disabled' : '' }}>
                                                    Room {{ $unit->room_number }} (Floor {{ $unit->floor ?? 'G' }}) {{ $isCurrent ? '— Current' : (!$isAvailable ? '— Occupied' : '') }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                @endif
                            </div>

                            {{-- Dates --}}
                            <div class="col-md-6">
                                <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                                @if($isDateLocked)
                                    <input type="hidden" name="check_in_date" value="{{ $booking->check_in_date->format('Y-m-d') }}">
                                    <input type="text" class="form-control" value="{{ $booking->check_in_date->format('D, M d, Y') }}" disabled>
                                @else
                                    <input type="date" name="check_in_date" id="checkInDate" class="form-control" value="{{ $booking->check_in_date->format('Y-m-d') }}" required>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                                @if($isDateLocked)
                                    <input type="hidden" name="check_out_date" value="{{ $booking->check_out_date->format('Y-m-d') }}">
                                    <input type="text" class="form-control" value="{{ $booking->check_out_date->format('D, M d, Y') }}" disabled>
                                @else
                                    <input type="date" name="check_out_date" id="checkOutDate" class="form-control" value="{{ $booking->check_out_date->format('Y-m-d') }}" required>
                                @endif
                            </div>

                            {{-- Price Summary --}}
                            <div class="col-md-12">
                                <div class="summary-box">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">
                                            <i class="fas fa-calculator me-1"></i>
                                            <span id="nightsDisplay">{{ $booking->check_in_date->diffInDays($booking->check_out_date) }}</span> night(s) × <span id="rateDisplay">₦{{ number_format(optional($booking->roomType)->price ?? 0, 2) }}</span>
                                        </span>
                                        <span class="fw-bold text-bp-gold fs-5" id="totalDisplay">₦{{ number_format($booking->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guest Information --}}
                <div class="card bp-card mb-4 {{ $isGuestLocked ? 'locked-overlay' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-user me-2"></i>Guest Information</h6>
                        @if($isGuestLocked)
                            <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i> Locked</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" class="form-control" value="{{ $booking->guest_name }}" {{ $isGuestLocked ? 'disabled' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="guest_email" class="form-control" value="{{ $booking->guest_email }}" {{ $isGuestLocked ? 'disabled' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="guest_phone" class="form-control" value="{{ $booking->guest_phone }}" {{ $isGuestLocked ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Adults <span class="text-danger">*</span></label>
                                <input type="number" name="adults" class="form-control" value="{{ $booking->adults }}" min="1" max="10" {{ $isGuestLocked ? 'disabled' : 'required' }}>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Children</label>
                                <input type="number" name="children" class="form-control" value="{{ $booking->children }}" min="0" max="10" {{ $isGuestLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Booking Status --}}
                <div class="card bp-card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-flag me-2"></i>Booking Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                @if($isLocked)
                                    <input type="hidden" name="status" value="{{ $booking->status }}">
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $booking->status)) }}" disabled>
                                @else
                                    <select name="status" class="form-select" required>
                                        @if($booking->status === 'pending')
                                            <option value="pending" selected>Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="cancelled">Cancelled</option>
                                        @elseif($booking->status === 'confirmed')
                                            <option value="confirmed" selected>Confirmed</option>
                                            <option value="checked_in">Checked In</option>
                                            <option value="cancelled">Cancelled</option>
                                        @elseif($booking->status === 'checked_in')
                                            <option value="checked_in" selected>Checked In</option>
                                            <option value="completed">Completed (Checked Out)</option>
                                        @endif
                                    </select>
                                @endif
                                @if(! $isLocked)
                                    <small class="text-muted">
                                        @if($booking->status === 'pending')
                                            Pending → Confirmed → Checked In → Completed
                                        @elseif($booking->status === 'confirmed')
                                            Confirmed → Checked In → Completed
                                        @elseif($booking->status === 'checked_in')
                                            Checked In → Completed (Check Out)
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card bp-card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Special Requests</label>
                            <textarea name="special_requests" class="form-control" rows="3" placeholder="Guest special requests..." {{ $isGuestLocked ? 'disabled' : '' }}>{{ $booking->special_requests }}</textarea>
                            <small class="text-muted">Guest-submitted requests visible to front desk.</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Internal Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Internal notes (not visible to guest)...">{{ $booking->admin_notes }}</textarea>
                            <small class="text-muted">Internal only — never shown to the guest.</small>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    @if(! $isLocked)
                        <button type="submit" class="btn btn-bp-gold btn-lg">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    @else
                        <span class="text-muted small"><i class="fas fa-lock me-1"></i> No changes allowed for {{ ucfirst($booking->status) }} bookings</span>
                    @endif
                </div>

            </div>

            {{-- ========== RIGHT COLUMN ========== --}}
            <div class="col-lg-4">

                {{-- Financial Summary (READ-ONLY) --}}
                <div class="card bp-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h6>
                        <span class="badge bg-light text-dark border"><i class="fas fa-lock me-1"></i> Read-only</span>
                    </div>
                    <div class="card-body">
                        <div class="readonly-row d-flex justify-content-between">
                            <span class="readonly-label">Total Amount</span>
                            <span class="readonly-value">₦{{ number_format($booking->total_amount, 2) }}</span>
                        </div>
                        <div class="readonly-row d-flex justify-content-between">
                            <span class="readonly-label">Amount Paid</span>
                            <span class="readonly-value text-success">₦{{ number_format($booking->amount_paid ?? 0, 2) }}</span>
                        </div>
                        <div class="readonly-row d-flex justify-content-between">
                            <span class="readonly-label">Balance Due</span>
                            <span class="readonly-value {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">₦{{ number_format($balanceDue, 2) }}</span>
                        </div>
                        <div class="readonly-row d-flex justify-content-between">
                            <span class="readonly-label">Payment Status</span>
                            <span class="status-badge pay-badge-{{ $booking->payment_status }}">{{ ucfirst($booking->payment_status) }}</span>
                        </div>
                        <div class="readonly-row d-flex justify-content-between">
                            <span class="readonly-label">Payment Method</span>
                            <span class="readonly-value">{{ ucfirst(str_replace('_', ' ', $booking->payment_method ?? 'N/A')) }}</span>
                        </div>

                        {{-- Payment Actions --}}
                        @if(! $isPaid && $balanceDue > 0 && ! $isLocked)
                            <hr class="my-3">
                            <p class="section-label"><i class="fas fa-credit-card me-1"></i> Payment Actions</p>
                            <div class="d-grid gap-2">
                                <form action="{{ route('website.admin.bookings.mark-paid', $booking->id) }}" method="POST" onsubmit="return confirm('Mark this booking as fully paid (₦{{ number_format($balanceDue, 2) }})?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 btn-sm">
                                        <i class="fas fa-check-circle me-1"></i> Mark as Paid
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Stay Summary --}}
                <div class="card bp-card mb-4">
                    <div class="card-body">
                        <p class="section-label"><i class="fas fa-calendar me-1"></i> Stay Summary</p>
                        <div class="date-display">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="date-label"><i class="fas fa-sign-in-alt me-1"></i> Check-in</span>
                                <span class="date-value text-success">{{ $booking->check_in_date->format('D, M d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="date-label"><i class="fas fa-sign-out-alt me-1"></i> Check-out</span>
                                <span class="date-value text-danger">{{ $booking->check_out_date->format('D, M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small mt-2">
                            <span class="text-muted">Duration</span>
                            <span class="fw-bold">{{ $booking->check_in_date->diffInDays($booking->check_out_date) }} night(s)</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Guests</span>
                            <span class="fw-bold">{{ $booking->adults }} adult(s){{ $booking->children > 0 ? ', ' . $booking->children . ' child(ren)' : '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Guest Quick View --}}
                <div class="card bp-card mb-4">
                    <div class="card-body">
                        <p class="section-label"><i class="fas fa-user me-1"></i> Guest</p>
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:linear-gradient(135deg,#C8A165,#B8915A);color:#fff;font-size:1.1rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-bp-charcoal">{{ $booking->guest_name }}</div>
                                <small class="text-muted">{{ $booking->guest_email }}</small>
                            </div>
                        </div>
                        @if($booking->guest_phone)
                            <div class="small mb-1"><i class="fas fa-phone text-muted me-2" style="width:16px;"></i><a href="tel:{{ $booking->guest_phone }}" class="text-decoration-none">{{ $booking->guest_phone }}</a></div>
                        @endif
                        @if($booking->source)
                            <div class="small"><i class="fas fa-globe text-muted me-2" style="width:16px;"></i>Source: <span class="fw-bold">{{ ucfirst($booking->source) }}</span></div>
                        @endif
                    </div>
                </div>

                {{-- Timestamps --}}
                <div class="card bp-card">
                    <div class="card-body text-center">
                        <small class="text-muted">Created {{ $booking->created_at->format('M d, Y \a\t g:i A') }}</small>
                        @if($booking->updated_at && $booking->updated_at != $booking->created_at)
                            <br><small class="text-muted">Updated {{ $booking->updated_at->diffForHumans() }}</small>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomTypeSelect = document.getElementById('roomTypeSelect');
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    const nightsDisplay = document.getElementById('nightsDisplay');
    const rateDisplay = document.getElementById('rateDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    if (!roomTypeSelect || !checkInInput || !checkOutInput) return;

    function updatePrice() {
        const selected = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        const price = parseFloat(selected.dataset.price) || 0;
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        const nights = Math.max(1, Math.round((checkOut - checkIn) / (1000 * 60 * 60 * 24)));
        nightsDisplay.textContent = nights;
        rateDisplay.textContent = '₦' + price.toLocaleString('en-NG', {minimumFractionDigits: 2});
        totalDisplay.textContent = '₦' + (price * nights).toLocaleString('en-NG', {minimumFractionDigits: 2});
    }

    function updateMinCheckout() {
        const nextDay = new Date(checkInInput.value);
        nextDay.setDate(nextDay.getDate() + 1);
        checkOutInput.min = nextDay.toISOString().split('T')[0];
        if (new Date(checkOutInput.value) <= new Date(checkInInput.value)) {
            checkOutInput.value = nextDay.toISOString().split('T')[0];
        }
    }

    roomTypeSelect.addEventListener('change', updatePrice);
    checkInInput.addEventListener('change', function() { updateMinCheckout(); updatePrice(); });
    checkOutInput.addEventListener('change', updatePrice);
});
</script>
@endpush
