@extends('layouts.master')

@section('title', 'View Registration')

@section('page-content')
    <div class="container-fluid py-4">
        @php
            // ### ROBUST CALCULATIONS ###
            $isGroupLead = $registration->is_group_lead;
            $status = $registration->stay_status;

            // 1. Setup Dates (Strip time components for accurate day math)
            $today = \Carbon\Carbon::now()->startOfDay();
            $checkIn = $registration->check_in ? $registration->check_in->copy()->startOfDay() : $today;
            $checkOut = $registration->check_out
                ? $registration->check_out->copy()->startOfDay()
                : $today->copy()->addDay();

            // 2. Calculate Total Stay Duration
            $totalNights = $checkIn->diffInDays($checkOut);
            if ($totalNights < 1) {
                $totalNights = 1;
            } // Minimum 1 night

            // 3. Calculate Days Passed
            if ($today->lt($checkIn)) {
                $daysPassed = 0; // Not started
            } elseif ($today->gte($checkOut)) {
                $daysPassed = $totalNights; // Finished
            } else {
                $daysPassed = $checkIn->diffInDays($today); // Current day count (0-indexed)
                if ($daysPassed == 0) {
                    $daysPassed = 1;
                } // UI Fix: It's technically "Day 1", not "Day 0"
}

// 4. Calculate Percentage
$progress = 0;
if ($status === 'checked_in') {
    $progress = round(($daysPassed / $totalNights) * 100);
    // Visual Fix: Ensure bar is visible even on Day 1
    if ($progress < 5) {
        $progress = 5;
    }
    if ($progress > 100) {
        $progress = 100;
    }
} elseif ($status === 'checked_out') {
    $progress = 100;
}

// 5. Days Remaining
$daysRemaining = $totalNights - $daysPassed;
if ($daysRemaining < 0) {
    $daysRemaining = 0;
}

// --- Status Badges ---
$statusBadgeClass = 'bg-secondary';
$statusText = ucfirst(str_replace('_', ' ', $status));

if ($status === 'checked_in') {
    if ($checkOut->isSameDay($today)) {
        $statusBadgeClass = 'bg-warning text-dark';
        $statusText = 'Departing Today';
    } elseif ($checkOut->lt($today)) {
        $statusBadgeClass = 'bg-danger';
        $statusText = 'Overstayed';
    } else {
        $statusBadgeClass = 'bg-info';
        $statusText = 'Checked In';
    }
} elseif ($status === 'checked_out') {
    $statusBadgeClass = 'bg-success';
}

// --- Confirmations ---
$checkoutConfirmMsg = $isGroupLead
    ? 'Are you sure you want to check out the GROUP LEAD? This usually implies the whole group is leaving.'
    : 'Are you sure you want to check out ' . e($registration->full_name) . '?';
        @endphp

        {{-- Header Section --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light p-2 me-3">
                        <i class="fas {{ $isGroupLead ? 'fa-user-tie' : 'fa-user' }} fa-lg text-gold"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 text-dark fw-bold">
                            Registration #{{ $registration->id }} - {{ $registration->full_name }}
                            @if ($isGroupLead)
                                <span class="badge bg-secondary ms-2">Group Lead</span>
                            @endif
                            @if ($registration->booking_id)
                                <span class="badge bg-info ms-2"><i class="fas fa-globe me-1"></i>Online Booking</span>
                            @endif
                        </h3>
                        <p class="text-muted mb-0">
                            Created {{ $registration->created_at->format('M d, Y h:i A') }}
                            @if ($registration->booking)
                                <span class="ms-2">| Ref: <strong>{{ $registration->booking->booking_reference }}</strong></span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
                <a href="{{ route('frontdesk.registrations.print', $registration) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print me-1"></i> Print
                </a>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($registration->stay_status == 'pending_approval')
    <div class="card border-primary shadow-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 fw-bold"><i class="fas fa-user-clock me-2"></i>Finalize Guest Check-in</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">This guest has self-registered via mobile. Please verify ID and assign a room to complete check-in.</p>
            
            <form action="{{ route('frontdesk.registrations.update', $registration->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="stay_status" value="checked_in">
                
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Assign Room</label>
                        <select name="room_unit_id" class="form-select form-select-lg" required>
                            <option value="">Select available room...</option>
                            @foreach(\App\Models\RoomUnit::whereIn('status', ['available', 'occupied'])->with('roomType')->orderBy('room_number')->get() as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->room_number }} ({{ $unit->roomType->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Nights</label>
                        <input type="number" name="no_of_nights" class="form-control form-control-lg" value="1">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                            <i class="fas fa-check"></i> Complete Check-in
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
        <div class="row">
            {{-- Left Column - Main Details --}}
            <div class="col-lg-8 mb-4">
                {{-- Booking Summary Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-2 me-3">
                                <i class="fas {{ $isGroupLead ? 'fa-user-tie' : 'fa-user' }} text-gold"></i>
                            </div>
                            <h5 class="mb-0 text-dark fw-bold">
                                {{ $isGroupLead ? 'Lead Guest Summary' : 'Guest Booking Summary' }}</h5>
                        </div>
                        <span class="badge fs-6 px-3 py-2 {{ $statusBadgeClass }}">{{ $statusText }}</span>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-bed text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Room Allocation</p>
                                        <p class="mb-0 fw-bold text-dark">
                                            {{ $registration->room ? $registration->room->name : $registration->room_allocation ?? 'Not Assigned' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-calendar-alt text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Stay Duration</p>
                                        <p class="mb-0 fw-bold text-dark">
                                            {{ $checkIn->format('M d') }} - {{ $checkOut->format('M d, Y') }}
                                            <span class="text-gold">({{ $totalNights }}
                                                {{ Str::plural('Night', $totalNights) }})</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-hourglass-half text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Stay Progress</p>
                                        <p class="mb-0 fw-bold text-dark">
                                            Day {{ $daysPassed }} of {{ $totalNights }}
                                            <small class="text-muted">({{ $daysRemaining }} left)</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-wallet text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Total Bill</p>
                                        <p class="mb-0 fw-bold fs-4 text-dark">
                                            &#8358;{{ number_format($registration->total_amount, 2) }}</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        {{-- ✅ COMPREHENSIVE FINANCIAL BREAKDOWN --}}
                        @php
                            // Calculate all financial components
                            $roomRate = $registration->room_rate ?? 0;
                            $nights = $registration->no_of_nights ?? $totalNights;
                            $roomCharges = $roomRate * $nights;
                            
                            // Online booking data
                            $hasOnlineBooking = $registration->booking !== null;
                            $onlineAmountPaid = $hasOnlineBooking ? ($registration->booking->amount_paid ?? 0) : 0;
                            $originalBookingNights = $hasOnlineBooking ? $registration->booking->check_in_date->diffInDays($registration->booking->check_out_date) : 0;
                            $originalBookingAmount = $hasOnlineBooking ? ($registration->booking->total_amount ?? 0) : 0;
                            
                            // Calculate adjustments
                            $lateArrivalDays = 0;
                            $extensionDays = 0;
                            $unusedNightsCredit = 0;
                            
                            if ($hasOnlineBooking) {
                                $originalCheckIn = $registration->booking->check_in_date->startOfDay();
                                $originalCheckOut = $registration->booking->check_out_date->startOfDay();
                                $actualCheckIn = $registration->check_in->startOfDay();
                                $actualCheckOut = $registration->check_out->startOfDay();
                                
                                // Late arrival: actual check-in is after original
                                if ($actualCheckIn->gt($originalCheckIn)) {
                                    $lateArrivalDays = $originalCheckIn->diffInDays($actualCheckIn);
                                }
                                
                                // Extension: actual checkout is after original
                                if ($actualCheckOut->gt($originalCheckOut)) {
                                    $extensionDays = $originalCheckOut->diffInDays($actualCheckOut);
                                }
                                
                                // Credit from unused nights (if flexible billing was applied)
                                if ($lateArrivalDays > 0 && $onlineAmountPaid > $roomCharges) {
                                    $unusedNightsCredit = $onlineAmountPaid - $roomCharges;
                                }
                            }
                            
                            // Staff recorded payments
                            $staffPayments = $registration->payments->sum('amount');
                            
                            // Total paid and balance
                            $totalPaid = $onlineAmountPaid + $staffPayments;
                            $totalCharges = $registration->total_amount ?? $roomCharges;
                            $balanceDue = $totalCharges - $totalPaid;
                            $hasCredit = $balanceDue < 0;
                        @endphp
                        
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h6>
                            
                            {{-- Charges Breakdown --}}
                            <div class="mb-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2">Charges</p>
                                <div class="bg-white rounded p-2">
                                    <div class="d-flex justify-content-between small py-1">
                                        <span>Room ({{ $nights }} {{ Str::plural('night', $nights) }} @ ₦{{ number_format($roomRate) }})</span>
                                        <span>₦{{ number_format($roomCharges, 2) }}</span>
                                    </div>
                                    @if ($registration->bed_breakfast)
                                        <div class="d-flex justify-content-between small py-1 text-muted">
                                            <span><i class="fas fa-coffee me-1"></i>Bed & Breakfast</span>
                                            <span>Included</span>
                                        </div>
                                    @endif
                                    @if ($extensionDays > 0)
                                        <div class="d-flex justify-content-between small py-1 text-info">
                                            <span><i class="fas fa-calendar-plus me-1"></i>Extension ({{ $extensionDays }} {{ Str::plural('night', $extensionDays) }})</span>
                                            <span>+ ₦{{ number_format($extensionDays * $roomRate, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between small py-1 border-top fw-bold">
                                        <span>Total Charges</span>
                                        <span>₦{{ number_format($totalCharges, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Payments Breakdown --}}
                            <div class="mb-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2">Payments Received</p>
                                <div class="bg-white rounded p-2">
                                    @if ($hasOnlineBooking && $onlineAmountPaid > 0)
                                        <div class="d-flex justify-content-between small py-1">
                                            <span>
                                                <i class="fas fa-globe text-info me-1"></i>Online Payment
                                                <small class="text-muted">({{ $registration->booking->booking_reference }})</small>
                                            </span>
                                            <span class="text-success">+ ₦{{ number_format($onlineAmountPaid, 2) }}</span>
                                        </div>
                                        @if ($originalBookingNights != $nights)
                                            <div class="small text-muted ps-3 py-1 fst-italic">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Originally booked: {{ $originalBookingNights }} nights (₦{{ number_format($originalBookingAmount, 2) }})
                                                @if ($lateArrivalDays > 0)
                                                    <span class="badge bg-warning text-dark ms-1">{{ $lateArrivalDays }}d late</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                    
                                    @forelse($registration->payments as $payment)
                                        <div class="d-flex justify-content-between small py-1">
                                            <span>
                                                <i class="fas fa-money-bill-wave text-success me-1"></i>
                                                {{ ucfirst($payment->payment_method) }}
                                                @if($payment->payment_type && $payment->payment_type !== 'payment')
                                                    <span class="badge bg-info bg-opacity-10 text-info small ms-1">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
                                                @endif
                                                <small class="text-muted">({{ $payment->payment_date->format('M d') }})</small>
                                                @if ($payment->reference)
                                                    <small class="text-muted">- {{ $payment->reference }}</small>
                                                @endif
                                            </span>
                                            <span class="text-success">+ ₦{{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                    @empty
                                        @if (!$hasOnlineBooking || $onlineAmountPaid <= 0)
                                            <div class="text-center text-muted fst-italic small py-2">
                                                No payments recorded yet
                                            </div>
                                        @endif
                                    @endforelse
                                    
                                    <div class="d-flex justify-content-between small py-1 border-top fw-bold text-success">
                                        <span>Total Paid</span>
                                        <span>₦{{ number_format($totalPaid, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Balance Summary --}}
                            <div class="p-3 rounded {{ $hasCredit ? 'bg-success bg-opacity-10 border border-success' : ($balanceDue > 0 ? 'bg-danger bg-opacity-10 border border-danger' : 'bg-success bg-opacity-10 border border-success') }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0 fw-bold {{ $hasCredit ? 'text-success' : ($balanceDue > 0 ? 'text-danger' : 'text-success') }}">
                                            @if ($hasCredit)
                                                <i class="fas fa-gift me-2"></i>Guest Credit
                                            @elseif ($balanceDue > 0)
                                                <i class="fas fa-exclamation-circle me-2"></i>Balance Due
                                            @else
                                                <i class="fas fa-check-circle me-2"></i>Fully Paid
                                            @endif
                                        </p>
                                        @if ($hasCredit)
                                            <small class="text-muted">Can apply to extras or refund</small>
                                        @elseif ($balanceDue > 0)
                                            <small class="text-muted">Collect before checkout</small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0 fw-bold fs-4 {{ $hasCredit ? 'text-success' : ($balanceDue > 0 ? 'text-danger' : 'text-success') }}">
                                            @if ($hasCredit)
                                                ₦{{ number_format(abs($balanceDue), 2) }}
                                            @elseif ($balanceDue > 0)
                                                ₦{{ number_format($balanceDue, 2) }}
                                            @else
                                                ₦ 0.00
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Folio / Charge Posting Section --}}
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-receipt me-2"></i>Guest Folio
                                <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#chargeModal">
                                    <i class="fas fa-plus"></i> Post Charge
                                </button>
                                <a href="{{ route('frontdesk.registrations.invoice', $registration) }}" class="btn btn-sm btn-outline-secondary ms-1" target="_blank">
                                    <i class="fas fa-print"></i> Invoice
                                </a>
                            </h6>

                            @php
                                $folioTransactions = collect();

                                $roomChargesItem = (object) [
                                    'type' => 'charge',
                                    'date' => $registration->check_in,
                                    'description' => "Room Charge ({$nights} nights @ ₦" . number_format($roomRate) . "/night)",
                                    'amount' => $roomCharges,
                                    'icon' => 'bed',
                                ];
                                $folioTransactions->push($roomChargesItem);

                                foreach ($registration->folioCharges as $fc) {
                                    $folioTransactions->push((object) [
                                        'type' => 'charge',
                                        'date' => $fc->created_at,
                                        'description' => $fc->description . ' (' . $fc->quantity . 'x ₦' . number_format($fc->unit_price) . ')',
                                        'amount' => $fc->amount,
                                        'icon' => $fc->chargeType->icon ?? 'receipt',
                                    ]);
                                }

                                foreach ($registration->payments as $payment) {
                                    $folioTransactions->push((object) [
                                        'type' => 'payment',
                                        'date' => $payment->payment_date,
                                        'description' => $payment->payment_method . ($payment->payment_type && $payment->payment_type !== 'payment' ? ' ('.ucfirst(str_replace('_', ' ', $payment->payment_type)).')' : '') . ($payment->reference ? ' - ' . $payment->reference : ''),
                                        'amount' => -abs($payment->amount),
                                        'icon' => 'money-bill-wave',
                                    ]);
                                }

                                $folioTransactions = $folioTransactions->sortBy('date');
                                $runningBalance = 0;
                            @endphp

                            <div class="bg-white rounded p-2">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-end">Charge</th>
                                            <th class="text-end">Payment</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($folioTransactions as $txn)
                                            @php $runningBalance += $txn->amount; @endphp
                                            <tr class="{{ $txn->type === 'payment' ? 'table-success' : '' }}">
                                                <td class="small">{{ optional($txn->date)->format('M d') ?? '—' }}</td>
                                                <td class="small">
                                                    <i class="fas fa-{{ $txn->icon }} me-1 text-muted"></i>
                                                    {{ $txn->description }}
                                                </td>
                                                <td class="text-end small">
                                                    @if($txn->type === 'charge' && $txn->amount > 0)
                                                        ₦{{ number_format($txn->amount, 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-end small">
                                                    @if($txn->type === 'payment')
                                                        ₦{{ number_format(abs($txn->amount), 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-end small fw-bold">
                                                    ₦{{ number_format($runningBalance, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="4" class="text-end">Outstanding Balance</td>
                                            <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                ₦{{ number_format($runningBalance, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- Security Deposit & Pre-Authorization Section --}}
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Deposits & Pre-Authorizations
                            </h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="bg-white rounded p-2 text-center">
                                        <small class="text-muted d-block">Security Deposit</small>
                                        <strong class="text-dark">₦{{ number_format($registration->security_deposit_amount ?? 0, 2) }}</strong>
                                        @if($registration->security_deposit_status === 'collected')
                                            <span class="badge bg-success d-block mt-1">Collected</span>
                                            @if(in_array($registration->stay_status, ['checked_in', 'checked_out']))
                                                <div class="mt-2 d-flex gap-1 justify-content-center">
                                                    <form action="{{ route('frontdesk.registrations.security-deposit.refund', $registration) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Refund security deposit?')">
                                                            <i class="fas fa-undo"></i> Refund
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('frontdesk.registrations.security-deposit.forfeit', $registration) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Forfeit security deposit? This cannot be undone.')">
                                                            <i class="fas fa-times"></i> Forfeit
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @elseif($registration->security_deposit_status === 'refunded')
                                            <span class="badge bg-secondary d-block mt-1">Refunded</span>
                                        @elseif($registration->security_deposit_status === 'forfeited')
                                            <span class="badge bg-danger d-block mt-1">Forfeited</span>
                                        @else
                                            <span class="badge bg-warning text-dark d-block mt-1">Pending</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-white rounded p-2 text-center">
                                        <small class="text-muted d-block">Deposit Required</small>
                                        @if($registration->deposit_required)
                                            <strong class="text-dark">₦{{ number_format($registration->deposit_amount ?? 0, 2) }}</strong>
                                            <span class="badge bg-info d-block mt-1">
                                                @if($registration->total_deposit_paid >= ($registration->deposit_amount ?? 0))
                                                    Fulfilled
                                                @else
                                                    Outstanding (₦{{ number_format(($registration->deposit_amount ?? 0) - $registration->total_deposit_paid, 2) }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">Not Required</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-white rounded p-2 text-center">
                                        <small class="text-muted d-block">Pre-Authorization</small>
                                        <strong class="text-dark">₦{{ number_format($registration->pre_authorization_amount ?? 0, 2) }}</strong>
                                        @if($registration->pre_authorization_reference)
                                            <small class="d-block text-muted">Ref: {{ $registration->pre_authorization_reference }}</small>
                                        @endif
                                        @if($registration->pre_authorization_status)
                                            <span class="badge bg-{{ $registration->pre_authorization_status === 'approved' || $registration->pre_authorization_status === 'active' ? 'primary' : ($registration->pre_authorization_status === 'captured' ? 'success' : 'secondary') }} d-block mt-1">
                                                {{ ucfirst($registration->pre_authorization_status) }}
                                            </span>
                                            @if(in_array($registration->pre_authorization_status, ['approved', 'active']))
                                                <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
                                                    <form action="{{ route('frontdesk.registrations.pre-auth.update', [$registration, 'capture']) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Capture pre-authorization of ₦{{ number_format($registration->pre_authorization_amount, 2) }}?')">
                                                            <i class="fas fa-check"></i> Capture
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('frontdesk.registrations.pre-auth.update', [$registration, 'void']) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Void pre-authorization?')">
                                                            <i class="fas fa-ban"></i> Void
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('frontdesk.registrations.pre-auth.update', [$registration, 'expire']) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Mark pre-authorization as expired?')">
                                                            <i class="fas fa-clock"></i> Expire
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-white rounded p-2 text-center">
                                        <small class="text-muted d-block">Discount Applied</small>
                                        @if($registration->total_discount > 0)
                                            <strong class="text-success">-₦{{ number_format($registration->total_discount, 2) }}</strong>
                                            <small class="d-block text-muted">{{ $registration->discount_reason ?? '—' }}</small>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($registration->stay_status === 'checked_in')
                            {{-- Progress Bar --}}
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Timeline</span>
                                    <span class="fw-bold text-dark">{{ $progress }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $progress }}%; background: linear-gradient(90deg, #C8A165 0%, #b08c54 100%);"
                                        aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <hr class="my-4">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-gold" data-bs-toggle="modal"
                                    data-bs-target="#adjustStayModal-{{ $registration->id }}">
                                    <i class="fas fa-calendar-plus me-1"></i> Extend Stay
                                </button>
                                <button type="button" class="btn btn-success text-white" data-bs-toggle="modal"
                                    data-bs-target="#paymentModal">
                                    <i class="fas fa-money-bill-wave me-1"></i> Record a Payment
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#upgradeModal">
                                    <i class="fas fa-arrow-up me-1"></i> Upgrade Room
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                    <i class="fas fa-sign-out-alt me-1"></i> Check Out
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Group Members Section --}}
                @if ($isGroupLead)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-users text-gold"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark fw-bold">Group Members</h5>
                                    <p class="text-muted small mb-0">{{ $groupMembers->count() }} members</p>
                                </div>
                            </div>
                            {{-- NEW: Add Member Button --}}
                            @if ($registration->stay_status === 'checked_in')
                                <button type="button" class="btn btn-sm btn-gold" data-bs-toggle="modal"
                                    data-bs-target="#addMemberModal">
                                    <i class="fas fa-user-plus me-1"></i> Add Member
                                </button>
                            @endif
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0 ps-4">Guest Name</th>
                                            <th class="border-0">Room</th>
                                            <th class="border-0">Rate</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0 text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($groupMembers as $member)
                                            <tr>
                                                <td class="align-middle ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-light p-1 me-2">
                                                            <i class="fas fa-user fa-sm text-gold"></i>
                                                        </div>
                                                        <span
                                                            class="fw-semibold text-dark">{{ $member->full_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    {{ $member->room ? $member->room->name : $member->room_allocation ?? 'N/A' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $member->room_rate ? '₦' . number_format($member->room_rate, 2) : 'N/A' }}
                                                </td>
                                                <td class="align-middle">
                                                    @if ($member->stay_status == 'checked_in')
                                                        <span class="badge bg-success">Checked In</span>
                                                    @elseif($member->stay_status == 'checked_out')
                                                        <span class="badge bg-secondary">Checked Out</span>
                                                    @elseif($member->stay_status == 'no_show')
                                                        <span class="badge bg-danger">No-Show</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Draft</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-end pe-4">
                                                    @if ($member->stay_status == 'checked_in')
                                                        <form
                                                            action="{{ route('frontdesk.registrations.checkout', $member) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Check out this guest?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                title="Check Out">
                                                                <i class="fas fa-sign-out-alt"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($member->stay_status == 'draft_by_guest')
                                                        {{-- NEW: Finalize Late Arrival --}}
                                                        <a href="{{ route('frontdesk.registrations.finalize.form', $member) }}"
                                                            class="btn btn-sm btn-warning" title="Finalize">
                                                            <i class="fas fa-check-double"></i>
                                                        </a>
                                                    @elseif($member->stay_status == 'no_show' || $member->stay_status == 'checked_out')
                                                        <form
                                                            action="{{ route('frontdesk.registrations.reopen', $member) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Re-open this guest?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                                title="Re-open">
                                                                <i class="fas fa-redo"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No group members found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column - Sidebar Content --}}
            <div class="col-lg-4">
                {{-- Group Financial Summary --}}
                @if ($isGroupLead)
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header border-0 py-3"
                            style="background: linear-gradient(135deg, #333333 0%, #444444 100%);">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-calculator text-white"></i>
                                </div>
                                <h5 class="mb-0 text-white fw-bold">Group Financial Summary</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3">
                                    <div><span class="text-muted small">Lead's Personal Bill</span></div>
                                    <span
                                        class="fw-bold text-dark">₦{{ number_format($groupFinancialSummary['lead_personal_bill'], 2) }}</span>
                                </div>
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3">
                                    <div><span class="text-muted small">Active Members' Bill</span></div>
                                    <span
                                        class="fw-bold text-dark">₦{{ number_format($groupFinancialSummary['members_bill'], 2) }}</span>
                                </div>
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3 bg-light rounded-2 mt-2">
                                    <div><span class="fw-bold text-dark">Total Outstanding</span></div>
                                    <span
                                        class="fw-bold fs-5 text-dark">₦{{ number_format($groupFinancialSummary['total_outstanding'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Guest Profile Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header border-0 bg-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-2 me-3">
                                <i class="fas fa-id-card text-gold"></i>
                            </div>
                            <h5 class="mb-0 text-dark fw-bold">Guest Profile</h5>
                        </div>
                    </div>

                    <div class="card-body">
                        <h6 class="card-title mb-3 text-dark">
                            {{ $registration->title }} {{ $registration->guest->full_name ?? $registration->full_name }}
                        </h6>
                        
                        {{-- Contact Info --}}
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase fw-bold">Contact</small>
                            <ul class="list-unstyled mb-0 mt-2">
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-envelope text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->email ?? $registration->guest->email ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-phone text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->contact_number ?? $registration->guest->contact_number ?? 'N/A' }}</span>
                                </li>
                                <li class="d-flex">
                                    <i class="fas fa-map-marker-alt text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->home_address ?? $registration->guest->home_address ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Personal Info --}}
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase fw-bold">Personal</small>
                            <ul class="list-unstyled mb-0 mt-2">
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-flag text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->nationality ?? $registration->guest->nationality ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-venus-mars text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ ucfirst($registration->gender ?? $registration->guest->gender ?? 'N/A') }}</span>
                                </li>
                                <li class="d-flex">
                                    <i class="fas fa-briefcase text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->occupation ?? $registration->guest->occupation ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Emergency Contact --}}
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase fw-bold">Emergency Contact</small>
                            <ul class="list-unstyled mb-0 mt-2">
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-user-shield text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->emergency_name ?? $registration->guest?->emergency_name ?? 'N/A' }}</span>
                                </li>
                                <li class="d-flex">
                                    <i class="fas fa-phone-alt text-muted mt-1 me-3" style="width: 16px;"></i>
                                    <span>{{ $registration->emergency_contact ?? $registration->guest?->emergency_contact ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Guest History --}}
                        <div>
                            <small class="text-muted text-uppercase fw-bold">History</small>
                            <div class="mt-2 d-flex align-items-center">
                                <i class="fas fa-history text-muted me-3" style="width: 16px;"></i>
                                <span class="badge {{ ($registration->guest?->visit_count ?? 1) > 1 ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($registration->guest?->visit_count ?? 1) > 1 ? 'Returning Guest' : 'New Guest' }}
                                </span>
                                <small class="text-muted ms-2">({{ $registration->guest?->visit_count ?? 1 }} visits)</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Booking Source Card (if from online booking) --}}
                @if ($registration->booking)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header border-0 bg-info bg-opacity-10 py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-globe text-info"></i>
                                </div>
                                <h5 class="mb-0 text-dark fw-bold">Online Booking Details</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Booking Ref:</span>
                                    <span class="fw-bold">{{ $registration->booking->booking_reference }}</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Booked On:</span>
                                    <span>{{ $registration->booking->created_at->format('M d, Y') }}</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Payment Status:</span>
                                    <span class="badge {{ $registration->booking->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($registration->booking->payment_status ?? 'Pending') }}
                                    </span>
                                </li>
                                @if ($registration->booking->amount_paid > 0)
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted">Amount Paid Online:</span>
                                        <span class="fw-bold text-success">₦{{ number_format($registration->booking->amount_paid, 2) }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODALS --}}

        {{-- 1. Adjust Stay Modal (Lead) --}}
        @include('frontdeskcrm::registrations.partials._adjust_stay_modal', ['guest' => $registration])

        {{-- 2. Adjust Stay Modal (Members) --}}
        @if ($isGroupLead && $groupMembers->count() > 0)
            @foreach ($groupMembers as $member)
                @include('frontdeskcrm::registrations.partials._adjust_stay_modal', ['guest' => $member])
            @endforeach
        @endif

        {{-- 3. Add Member Modal (NEW) --}}
        @if ($isGroupLead)
            <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Group Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="{{ route('frontdesk.registrations.add-member', $registration) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" required
                                        placeholder="Guest Name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Number (Optional)</label>
                                    <input type="text" name="contact_number" class="form-control"
                                        placeholder="+234...">
                                </div>
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    You will be redirected to finalize this guest's room and rate immediately.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-gold">Create & Finalize</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Record New Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('frontdesk.registrations.payment.store', $registration) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Received (₦) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold text-success" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash">Cash</option>
                                <option value="POS">POS / Card</option>
                                <option value="Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Complimentary">Complimentary / Waived</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Type</label>
                            <select name="payment_type" class="form-select">
                                <option value="payment">Regular Payment</option>
                                <option value="deposit">Deposit</option>
                                <option value="advance">Advance Payment</option>
                                <option value="security_deposit">Security Deposit</option>
                                <option value="pre_authorization">Pre-Authorization</option>
                                <option value="refund">Refund</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference / Receipt No. (Optional)</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. POS-123456">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Deposit for room and bar"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Post Charge Modal --}}
@php $chargeTypes = \Modules\Frontdeskcrm\Models\ChargeType::active()->ordered()->get(); @endphp
<div class="modal fade" id="chargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-receipt me-2"></i>Post Charge to Folio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('frontdesk.registrations.charges.store', $registration) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Charge Type <span class="text-danger">*</span></label>
                        <select name="charge_type_id" class="form-select" required>
                            <option value="">Select charge type...</option>
                            @foreach($chargeTypes as $ct)
                                <option value="{{ $ct->id }}" {{ old('charge_type_id') == $ct->id ? 'selected' : '' }}>
                                    @if($ct->icon)<i class="{{ $ct->icon }} me-1"></i>@endif
                                    {{ $ct->name }} ({{ $ct->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Mini Bar - Whisky" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label">Qty</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="999" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label">Unit Price (₦) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="0.00" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-plus"></i> Post Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Checkout Confirmation Modal --}}
@php
    $checkoutRoomCharges = $roomCharges + ($extensionDays > 0 ? $extensionDays * $roomRate : 0);
    $checkoutExtraCharges = $registration->folioCharges()->sum('amount');
    $checkoutDiscount = $registration->total_discount * $nights;
    $checkoutTotalCharges = $checkoutRoomCharges + $checkoutExtraCharges;
    $checkoutTotalPaid = $registration->total_paid;
    $checkoutBalance = $checkoutTotalCharges - $checkoutDiscount - $checkoutTotalPaid;
    $taxRate = app(\App\Services\PropertyService::class)->taxRate();
    $taxAmount = round(($checkoutTotalCharges - $checkoutDiscount) * $taxRate / 100, 2);
    $grandTotal = $checkoutTotalCharges - $checkoutDiscount + $taxAmount;
@endphp
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('frontdesk.registrations.checkout', $registration) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-sign-out-alt me-2"></i>Check Out: {{ $registration->full_name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Folio Summary --}}
                    <div class="bg-light rounded p-3 mb-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-receipt me-2"></i>Final Bill Summary</h6>
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Room Charges ({{ $nights }} {{ Str::plural('night', $nights) }} @ ₦{{ number_format($roomRate) }})</td>
                                <td class="text-end">₦{{ number_format($checkoutRoomCharges, 2) }}</td>
                            </tr>
                            @if($checkoutExtraCharges > 0)
                            <tr>
                                <td>Extra Charges ({{ $registration->folioCharges->count() }} items)</td>
                                <td class="text-end">₦{{ number_format($checkoutExtraCharges, 2) }}</td>
                            </tr>
                            @endif
                            @if($checkoutDiscount > 0)
                            <tr class="text-success">
                                <td><i class="fas fa-tag me-1"></i>Discount{{ $registration->discount_reason ? ' ('.$registration->discount_reason.')' : '' }}</td>
                                <td class="text-end">- ₦{{ number_format($checkoutDiscount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">₦{{ number_format($checkoutTotalCharges, 2) }}</td>
                            </tr>
                            <tr>
                                <td>VAT ({{ $taxRate }}%)</td>
                                <td class="text-end">₦{{ number_format($taxAmount, 2) }}</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td>Total Charges (incl. tax)</td>
                                <td class="text-end">₦{{ number_format($grandTotal, 2) }}</td>
                            </tr>
                            <tr class="text-success">
                                <td><i class="fas fa-money-bill-wave me-1"></i>Total Paid</td>
                                <td class="text-end">- ₦{{ number_format($checkoutTotalPaid, 2) }}</td>
                            </tr>
                            <tr class="fw-bold {{ $checkoutBalance > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.1rem;">
                                <td>{{ $checkoutBalance > 0 ? 'Balance Due' : ($checkoutBalance < 0 ? 'Guest Credit' : 'Fully Paid') }}</td>
                                <td class="text-end">₦{{ number_format(abs($checkoutBalance), 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Loyalty Points Preview --}}
                    @if($registration->guest && $guest = $registration->guest)
                    <div class="bg-white border rounded p-3 mb-3">
                        <h6 class="fw-bold mb-1"><i class="fas fa-star text-gold me-2"></i>Loyalty Points</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">
                                    {{ $guest->loyaltyTier?->name ?? 'Bronze' }} Member
                                    @if($guest->loyaltyTier?->multiplier && $guest->loyaltyTier->multiplier > 1)
                                        ({{ number_format($guest->loyaltyTier->multiplier, 1) }}x multiplier)
                                    @endif
                                </small>
                                <small class="text-muted">Available: {{ number_format($guest->total_points) }} pts</small>
                            </div>
                            <div class="text-end">
                                @php
                                    $estPoints = (int) floor($grandTotal * ($guest->loyaltyTier?->multiplier ?? 1.0));
                                @endphp
                                <span class="h5 fw-bold text-gold mb-0 d-block">+{{ number_format($estPoints) }}</span>
                                <small class="text-muted">estimated points</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Security Deposit Handling --}}
                    @if($registration->security_deposit_status === 'collected' && $registration->security_deposit_amount > 0)
                    <div class="bg-warning bg-opacity-10 border border-warning rounded p-3 mb-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-shield-alt me-2 text-warning"></i>Security Deposit: ₦{{ number_format($registration->security_deposit_amount, 2) }}</h6>
                        <p class="small text-muted mb-2">Choose how to handle the collected security deposit:</p>
                        <div class="d-flex gap-3">
                            <label class="form-check-label d-flex align-items-center gap-1">
                                <input type="radio" name="security_deposit_action" value="refund" checked>
                                <i class="fas fa-undo text-secondary"></i> Refund to guest
                            </label>
                            <label class="form-check-label d-flex align-items-center gap-1">
                                <input type="radio" name="security_deposit_action" value="forfeit">
                                <i class="fas fa-times text-danger"></i> Forfeit (damages)
                            </label>
                        </div>
                    </div>
                    @endif

                    {{-- Final Payment (if balance > 0) --}}
                    @if($checkoutBalance > 0)
                    <div class="bg-white border rounded p-3 mb-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-cash-register me-2"></i>Record Final Payment</h6>

                        @if($registration->corporate_account_id)
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="billing_to_account" id="billingToAccount" value="1">
                            <label class="form-check-label fw-medium" for="billingToAccount">
                                <i class="fas fa-building me-1 text-muted"></i>
                                Bill to Account ({{ $registration->corporateAccount?->company_name ?? 'Corporate Account' }})
                            </label>
                        </div>
                        @endif

                        <div id="checkoutPaymentFields">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Amount</label>
                                    <input type="number" step="0.01" name="payment_amount" class="form-control" value="{{ number_format($checkoutBalance, 2, '.', '') }}" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Cash">Cash</option>
                                        <option value="POS">POS / Card</option>
                                        <option value="Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Complimentary">Complimentary</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="payment_reference" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Options --}}
                    <div class="bg-white border rounded p-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-cog me-2"></i>Checkout Options</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="email_receipt" id="emailReceipt" value="1" checked>
                            <label class="form-check-label" for="emailReceipt">
                                <i class="fas fa-envelope me-1 text-muted"></i>
                                Email receipt to {{ $registration->email ?? $registration->guest?->email ?? 'guest' }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="print_receipt" id="printReceipt" value="1">
                            <label class="form-check-label" for="printReceipt">
                                <i class="fas fa-print me-1 text-muted"></i>
                                Open receipt for printing after checkout
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold btn-lg">
                        <i class="fas fa-check-circle me-1"></i> Confirm Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Upgrade Room Modal --}}
<div class="modal fade" id="upgradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-arrow-up me-2"></i>Upgrade Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4" id="upgrade-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Checking available upgrades...</p>
                </div>
                <div id="upgrade-content" style="display: none;">
                    <p class="text-muted mb-3">
                        Current room: <strong>{{ $registration->roomUnit?->room_number ?? 'N/A' }}</strong>
                        (₦{{ number_format($registration->room_rate ?? 0) }}/night)
                    </p>
                    <div id="upgrade-options"></div>
                    <div id="upgrade-none" class="text-center py-4" style="display: none;">
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No upgrade options available at this time.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

    </div>
@endsection

@push('page-scripts')
<script>
    $('#upgradeModal').on('show.bs.modal', function () {
        var modal = $(this);
        $('#upgrade-loading').show();
        $('#upgrade-content').hide();
        $('#upgrade-options').empty();
        $('#upgrade-none').hide();

        $.get('{{ route("frontdesk.registrations.upgrade-options", $registration) }}', function (res) {
            $('#upgrade-loading').hide();
            $('#upgrade-content').show();

            if (!res.upgrades || res.upgrades.length === 0) {
                $('#upgrade-none').show();
                return;
            }

            var html = '';
            res.upgrades.forEach(function (opt) {
                var unitsHtml = '';
                opt.available_units.forEach(function (u) {
                    unitsHtml += '<button class="btn btn-sm btn-outline-primary upgrade-unit" ' +
                        'data-unit-id="' + u.id + '" ' +
                        'data-room-number="' + u.room_number + '" ' +
                        'data-type-name="' + opt.room_type.name + '" ' +
                        'data-price-diff="' + opt.price_difference + '" ' +
                        'data-price-diff-night="' + opt.price_difference_per_night + '">' +
                        'Room ' + u.room_number + (u.floor ? ' (Floor ' + u.floor + ')' : '') +
                        '</button>';
                });

                html += '<div class="card border-0 shadow-sm mb-3">' +
                    '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h6 class="fw-bold mb-1">' + opt.room_type.name + '</h6>' +
                    (opt.room_type.description ? '<small class="text-muted d-block">' + opt.room_type.description + '</small>' : '') +
                    (opt.room_type.size ? '<small class="text-muted d-block">' + opt.room_type.size + ' m²</small>' : '') +
                    (opt.room_type.bed_type ? '<small class="text-muted">' + opt.room_type.bed_type + '</small>' : '') +
                    '</div>' +
                    '<div class="text-end">' +
                    '<span class="fw-bold text-primary">₦' + opt.room_type.price.toLocaleString() + '<small class="text-muted fw-normal">/night</small></span>' +
                    '<br><span class="text-success small fw-bold">+₦' + opt.price_difference_per_night.toLocaleString() + '/night</span>' +
                    '<br><span class="text-primary small fw-bold">Total: +₦' + opt.price_difference.toLocaleString() + '</span>' +
                    '</div>' +
                    '</div>' +
                    '<hr class="my-2">' +
                    '<div class="d-flex gap-2 flex-wrap">' + unitsHtml + '</div>' +
                    '</div>' +
                    '</div>';
            });

            $('#upgrade-options').html(html);
        }).fail(function () {
            $('#upgrade-loading').hide();
            $('#upgrade-content').show();
            $('#upgrade-options').html('<div class="alert alert-danger">Failed to load upgrade options.</div>');
        });
    });

    $(document).on('click', '.upgrade-unit', function () {
        var btn = $(this);
        var unitId = btn.data('unit-id');
        var roomNumber = btn.data('room-number');
        var typeName = btn.data('type-name');
        var priceDiff = btn.data('price-diff');

        if (!confirm('Upgrade to ' + typeName + ' - Room ' + roomNumber + '?\n\nAdditional charge: ₦' + priceDiff.toLocaleString() + '\n\nThis will be posted to the folio.')) return;

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '{{ route("frontdesk.registrations.upgrade", $registration) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name=\'csrf-token\']').attr('content') },
            data: { room_unit_id: unitId },
            success: function () {
                location.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.error || xhr.responseJSON?.message || 'Upgrade failed.');
                btn.prop('disabled', false).text('Room ' + roomNumber);
            }
        });
    });

    // Bill to Account toggle
    $('#billingToAccount').on('change', function () {
        if ($(this).is(':checked')) {
            $('#checkoutPaymentFields').slideUp();
        } else {
            $('#checkoutPaymentFields').slideDown();
        }
    });
</script>
@if(session('printInvoice'))
<script>
    window.open('{{ session('printInvoice') }}', '_blank');
</script>
@endif
@endpush
