@extends('layouts.master')

@section('title', 'View Registration')

@section('page-content')
    <div class="container-fluid my-4">

        @php
            // ### CALCULATIONS FOR ENHANCED UX ###
            $isGroupLead = $registration->is_group_lead;

            // --- Stay Progress Calculation ---
            $today = \Carbon\Carbon::today();
            $checkIn = $registration->check_in;
            $checkOut = $registration->check_out;
            $totalNights = $registration->no_of_nights > 0 ? $registration->no_of_nights : 1;

            $daysPassed = $checkIn->isPast() ? $today->diffInDays($checkIn) : 0;
            $daysRemaining = $today->diffInDays($checkOut, false);
            $progress = round(($daysPassed / $totalNights) * 100);
            if ($progress < 0) {
                $progress = 0;
            }
            if ($progress > 100) {
                $progress = 100;
            }

            // --- Dynamic Status Badge Logic ---
            $statusBadgeClass = 'bg-secondary';
            $statusText = ucfirst(str_replace('_', ' ', $registration->stay_status));

            if ($registration->stay_status === 'checked_in') {
                if ($checkOut->isToday()) {
                    $statusBadgeClass = 'bg-warning text-dark';
                    $statusText = 'Departing Today';
                } elseif ($checkOut->isPast()) {
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
            $guestAge =
                $registration->guest && $registration->guest->birthday
                    ? \Carbon\Carbon::parse($registration->guest->birthday)->age
                    : null;

            // --- Confirmation Message Logic ---
            $checkoutConfirmMsg = $isGroupLead
                ? 'Are you sure you want to check out the GROUP LEAD?'
                : 'Are you sure you want to check out ' . e($registration->full_name) . '?';
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>
                Registration #{{ $registration->id }} - {{ $registration->full_name }}
                @if ($isGroupLead)
                    (Group Lead)
                @endif
            </h3>
            <div>
                <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-secondary"><i
                        class="fas fa-arrow-left me-1"></i> Back to List</a>
                <a href="{{ route('frontdesk.registrations.print', $registration) }}" class="btn btn-info" target="_blank"><i
                        class="fas fa-print me-1"></i> Print</a>
            </div>
        </div>

        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas {{ $isGroupLead ? 'fa-user-tie' : 'fa-user' }} me-2"></i>
                            {{ $isGroupLead ? 'Lead Guest Summary' : 'Guest Booking Summary' }}
                        </h5>
                        <span class="badge fs-6 {{ $statusBadgeClass }}">{{ $statusText }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong><i class="fas fa-bed text-muted me-2"></i>Room:</strong>
                                    {{ $registration->room_allocation }}</p>
                                <p class="mb-0"><strong><i class="fas fa-calendar-alt text-muted me-2"></i>Stay:</strong>
                                    {{ $checkIn->format('M d') }} → {{ $checkOut->format('M d, Y') }}
                                    <span class="text-primary fw-bold">({{ $totalNights }}
                                        {{ Str::plural('Night', $totalNights) }})</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong><i
                                            class="fas fa-hourglass-half text-muted me-2"></i>Duration:</strong>
                                    {{ $daysPassed }} {{ Str::plural('Day', $daysPassed) }} Passed,
                                    {{ $daysRemaining >= 0 ? $daysRemaining : 0 }}
                                    {{ Str::plural('Day', $daysRemaining) }} Remaining
                                </p>
                                <p class="mb-0"><strong><i class="fas fa-wallet text-muted me-2"></i>Bill:</strong>
                                    <span
                                        class="fw-bold fs-5">&#8358;{{ number_format($registration->total_amount, 2) }}</span>
                                </p>
                            </div>
                        </div>

                        @if ($registration->stay_status === 'checked_in')
                            <div class="progress mt-3" style="height: 8px;" title="{{ $progress }}% of stay completed">
                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;"
                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#adjustStayModal-{{ $registration->id }}">
                                    <i class="fas fa-calendar-alt me-1"></i> Adjust Stay
                                </button>
                                <form action="{{ route('frontdesk.registrations.checkout', $registration) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('{{ $checkoutConfirmMsg }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-door-open me-1"></i> Check Out
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($isGroupLead && $groupMembers->count() > 0)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Group Members
                                ({{ $groupMembers->count() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Guest Name</th>
                                            <th>Room</th>
                                            <th>Rate</th>
                                            <th>Bill (Est.)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupMembers as $member)
                                            <tr>
                                                <td>{{ $member->full_name }}</td>
                                                <td>{{ $member->room_allocation ?? 'N/A' }}</td>
                                                <td>{{ $member->room_rate ? number_format($member->room_rate, 2) : 'N/A' }}
                                                </td>
                                                <td>{{ $member->total_amount ? number_format($member->total_amount, 2) : 'N/A' }}
                                                </td>
                                                <td>
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
                                                <td>
                                                    {{-- THE "NO-SHOW" FIX --}}
                                                    @if ($member->stay_status == 'checked_in')
                                                        <form
                                                            action="{{ route('frontdesk.registrations.checkout', $member) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to check out this guest?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger">Check-out</button>
                                                        </form>

                                                        {{-- ADDED THIS 'elseif' BLOCK --}}
                                                    @elseif($member->stay_status == 'no_show' || $member->stay_status == 'checked_out')
                                                        <form
                                                            action="{{ route('frontdesk.registrations.reopen', $member) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('This will re-open the *entire group* to be finalized again. Are you sure?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-warning">Re-Open</button>
                                                        </form>
                                                    @else
                                                        {{-- No actions for 'draft' or 'checked_out' --}}
                                                    @endif
                                                    {{-- ====================================================== --}}
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

            {{-- Right Column --}}
            <div class="col-lg-4">
                @if ($isGroupLead)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Group Financial Summary</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Lead's
                                    Personal Bill:
                                    <span>&#8358;{{ number_format($groupFinancialSummary['lead_personal_bill'], 2) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Active
                                    Members' Bill:
                                    <span>&#8358;{{ number_format($groupFinancialSummary['members_bill'], 2) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">Total
                                    Outstanding Bill:
                                    <span>&#8358;{{ number_format($groupFinancialSummary['total_outstanding'], 2) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Guest Profile</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">{{ $registration->guest->full_name ?? $registration->full_name }}</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Email:</strong> {{ $registration->guest->email ?? 'N/A' }}
                            </li>
                            <li class="list-group-item"><strong>Contact:</strong>
                                {{ $registration->guest->contact_number ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Address:</strong>
                                {{ $registration->guest->home_address ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Gender:</strong>
                                {{ $registration->guest->gender ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Nationality:</strong>
                                {{ $registration->guest->nationality ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Company:</strong>
                                {{ $registration->guest->company_name ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Birthdate:</strong>
                                {{ $registration->guest->birthday ? \Carbon\Carbon::parse($registration->guest->birthday)->format('M d, Y') : 'N/A' }}
                                @if ($guestAge)
                                    <span class="text-muted">(Age: {{ $guestAge }})</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Guest Status:</strong>
                                <span
                                    class="badge {{ ($registration->guest->visit_count ?? 1) > 1 ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($registration->guest->visit_count ?? 1) > 1 ? 'Returning Guest' : 'New Guest' }}
                                </span>
                                (Visits: {{ $registration->guest->visit_count ?? 1 }})
                            </li>
                        </ul>
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
