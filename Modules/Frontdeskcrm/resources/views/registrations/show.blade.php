@extends('layouts.master')

@section('title', 'View Registration')

@section('page-content')
    <div class="container-fluid py-4">
       @php
            // ### CALCULATIONS FOR ENHANCED UX ###
            $isGroupLead = $registration->is_group_lead;

            // --- Robust Stay Progress Calculation ---
            $today = \Carbon\Carbon::now()->startOfDay();
            
            // Ensure dates are valid objects and strip time for accurate day-diffs
            $checkIn = $registration->check_in ? $registration->check_in->copy()->startOfDay() : $today;
            $checkOut = $registration->check_out ? $registration->check_out->copy()->startOfDay() : $today->copy()->addDay();

            // 1. Calculate Total Duration (Dynamic)
            $totalNights = $checkIn->diffInDays($checkOut);
            if ($totalNights < 1) $totalNights = 1; // Prevent division by zero

            // 2. Calculate Days Passed
            if ($today->lt($checkIn)) {
                $daysPassed = 0; // Future stay
            } elseif ($today->gte($checkOut)) {
                $daysPassed = $totalNights; // Completed stay
            } else {
                $daysPassed = $checkIn->diffInDays($today); // Ongoing
            }

            // 3. Calculate Days Remaining
            $daysRemaining = $totalNights - $daysPassed;
            if ($daysRemaining < 0) $daysRemaining = 0;

            // 4. Calculate Percentage
            $progress = 0;
            if ($totalNights > 0) {
                $progress = round(($daysPassed / $totalNights) * 100);
            }
            
            // UX FIX: If Checked-In but on Day 1 (0%), show 5% so the bar is visible
            if ($registration->stay_status === 'checked_in' && $progress < 5) {
                $progress = 5;
            }
            if ($progress > 100) $progress = 100;

            // --- Dynamic Status Badge Logic ---
            $statusBadgeClass = 'bg-secondary';
            $statusText = ucfirst(str_replace('_', ' ', $registration->stay_status));

            if ($registration->stay_status === 'checked_in') {
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
            } elseif ($registration->stay_status === 'checked_out') {
                $statusBadgeClass = 'bg-success';
            }

            // --- Guest Profile Enhancements ---
            $guestAge = $registration->guest && $registration->guest->birthday
                    ? \Carbon\Carbon::parse($registration->guest->birthday)->age
                    : null;

            // --- Confirmation Message Logic ---
            $checkoutConfirmMsg = $isGroupLead
                ? 'Are you sure you want to check out the GROUP LEAD?'
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
                        </h3>
                        <p class="text-muted mb-0">Created {{ $registration->created_at->format('M d, Y h:i A') }}</p>
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
                            <h5 class="mb-0 text-dark fw-bold">{{ $isGroupLead ? 'Lead Guest Summary' : 'Guest Booking Summary' }}</h5>
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
                                        <p class="mb-0 fw-bold text-dark">{{ $registration->room_allocation ?? 'Not Assigned' }}</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-calendar-alt text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Stay Duration</p>
                                        <p class="mb-0 fw-bold text-dark">
                                            {{ $checkIn->format('M d, Y') }} → {{ $checkOut->format('M d, Y') }}
                                            <span class="text-gold">({{ $totalNights }} {{ Str::plural('Night', $totalNights) }})</span>
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
                                            {{ $daysPassed }} {{ Str::plural('Day', $daysPassed) }} Passed, 
                                            {{ $daysRemaining >= 0 ? $daysRemaining : 0 }} {{ Str::plural('Day', $daysRemaining) }} Remaining
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-wallet text-gold"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-0">Total Bill</p>
                                        <p class="mb-0 fw-bold fs-4 text-dark">&#8358;{{ number_format($registration->total_amount, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($registration->stay_status === 'checked_in')
                            {{-- Progress Bar --}}
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Stay Progress</span>
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
                                    <i class="fas fa-calendar-alt me-1"></i> Adjust Stay
                                </button>
                                <form action="{{ route('frontdesk.registrations.checkout', $registration) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('{{ $checkoutConfirmMsg }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-door-open me-1"></i> Check Out
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Group Members Section --}}
                @if ($isGroupLead && $groupMembers->count() > 0)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header border-0 bg-white py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 me-3">
                                    <i class="fas fa-users text-gold"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark fw-bold">Group Members</h5>
                                    <p class="text-muted small mb-0">{{ $groupMembers->count() }} members in this group</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Guest Name</th>
                                            <th class="border-0">Room</th>
                                            <th class="border-0">Rate</th>
                                            <th class="border-0">Bill</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupMembers as $member)
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-light p-1 me-2">
                                                            <i class="fas fa-user fa-sm text-gold"></i>
                                                        </div>
                                                        <span class="fw-semibold text-dark">{{ $member->full_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">{{ $member->room_allocation ?? 'N/A' }}</td>
                                                <td class="align-middle">
                                                    {{ $member->room_rate ? '₦' . number_format($member->room_rate, 2) : 'N/A' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $member->total_amount ? '₦' . number_format($member->total_amount, 2) : 'N/A' }}
                                                </td>
                                                <td class="align-middle">
                                                    @if ($member->stay_status == 'checked_in')
                                                        <span class="badge bg-success">Checked In</span>
                                                    @elseif($member->stay_status == 'checked_out')
                                                        <span class="badge bg-secondary">Checked Out</span>
                                                    @elseif($member->stay_status == 'no_show')
                                                        <span class="badge bg-danger">No-Show</span>
                                                    @else
                                                        <span class="badge bg-warning">Draft</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-end">
                                                    @if ($member->stay_status == 'checked_in')
                                                        <form action="{{ route('frontdesk.registrations.checkout', $member) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to check out this guest?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-door-open me-1"></i> Check-out
                                                            </button>
                                                        </form>
                                                    @elseif($member->stay_status == 'no_show' || $member->stay_status == 'checked_out')
                                                        <form action="{{ route('frontdesk.registrations.reopen', $member) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('This will re-open the *entire group* to be finalized again. Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                <i class="fas fa-redo me-1"></i> Re-Open
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
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
                        <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #333333 0%, #444444 100%);">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                    <i class="fas fa-calculator text-white"></i>
                                </div>
                                <h5 class="mb-0 text-white fw-bold">Group Financial Summary</h5>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3">
                                    <div>
                                        <span class="text-muted small">Lead's Personal Bill</span>
                                    </div>
                                    <span class="fw-bold text-dark">₦{{ number_format($groupFinancialSummary['lead_personal_bill'], 2) }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3">
                                    <div>
                                        <span class="text-muted small">Active Members' Bill</span>
                                    </div>
                                    <span class="fw-bold text-dark">₦{{ number_format($groupFinancialSummary['members_bill'], 2) }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3 bg-light rounded-2 mt-2">
                                    <div>
                                        <span class="fw-bold text-dark">Total Outstanding</span>
                                    </div>
                                    <span class="fw-bold fs-5 text-dark">₦{{ number_format($groupFinancialSummary['total_outstanding'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Guest Profile Card --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header border-0 bg-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-2 me-3">
                                <i class="fas fa-id-card text-gold"></i>
                            </div>
                            <h5 class="mb-0 text-dark fw-bold">Guest Profile</h5>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="card-title mb-3 text-dark">{{ $registration->guest->full_name ?? $registration->full_name }}</h6>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex border-0 px-0 py-2">
                                <div class="flex-shrink-0" style="width: 120px;">
                                    <span class="text-muted small">Email:</span>
                                </div>
                                <span class="text-dark">{{ $registration->guest->email ?? 'N/A' }}</span>
                            </div>
                            <div class="list-group-item d-flex border-0 px-0 py-2">
                                <div class="flex-shrink-0" style="width: 120px;">
                                    <span class="text-muted small">Contact:</span>
                                </div>
                                <span class="text-dark">{{ $registration->guest->contact_number ?? 'N/A' }}</span>
                            </div>
                            <div class="list-group-item d-flex border-0 px-0 py-2">
                                <div class="flex-shrink-0" style="width: 120px;">
                                    <span class="text-muted small">Gender:</span>
                                </div>
                                <span class="text-dark">{{ $registration->guest->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="list-group-item d-flex border-0 px-0 py-2">
                                <div class="flex-shrink-0" style="width: 120px;">
                                    <span class="text-muted small">Birthdate:</span>
                                </div>
                                <span class="text-dark">
                                    {{ $registration->guest->birthday ? \Carbon\Carbon::parse($registration->guest->birthday)->format('M d, Y') : 'N/A' }}
                                    @if ($guestAge)
                                        <span class="text-muted">(Age: {{ $guestAge }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="list-group-item d-flex border-0 px-0 py-2">
                                <div class="flex-shrink-0" style="width: 120px;">
                                    <span class="text-muted small">Guest Status:</span>
                                </div>
                                <span class="d-flex align-items-center">
                                    <span class="badge {{ ($registration->guest->visit_count ?? 1) > 1 ? 'bg-success' : 'bg-secondary' }} me-2">
                                        {{ ($registration->guest->visit_count ?? 1) > 1 ? 'Returning' : 'New' }}
                                    </span>
                                    <span class="text-muted small">(Visits: {{ $registration->guest->visit_count ?? 1 }})</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('frontdeskcrm::registrations.partials._adjust_stay_modal', ['guest' => $registration])
        @if ($isGroupLead && $groupMembers->count() > 0)
            @foreach ($groupMembers as $member)
                @include('frontdeskcrm::registrations.partials._adjust_stay_modal', ['guest' => $member])
            @endforeach
        @endif
    </div>
@endsection