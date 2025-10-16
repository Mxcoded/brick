@extends('layouts.master')

@section('title', 'Registration Details')

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Registration #{{ $registration->id }} - {{ $registration->full_name }} (Group Lead)</h3>
        <div>
            <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            <a href="{{ route('frontdesk.registrations.print', $registration) }}" class="btn btn-info" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Booking & Group Details --}}
        <div class="col-lg-8">
            {{-- Group Lead's Booking Summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Lead Guest Summary</h5>
                    @if($registration->stay_status === 'checked_in')
                        <span class="badge bg-info fs-6">Checked-In</span>
                    @else
                        <span class="badge bg-success fs-6">{{ ucfirst(str_replace('_', ' ', $registration->stay_status)) }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"><h6>Stay Details</h6><p><strong>Room:</strong> {{ $registration->room_allocation }}</p></div>
                        <div class="col-md-6"><h6>Financials</h6><p><strong>Total Bill (Personal):</strong> &#8358;{{ number_format($registration->total_amount, 2) }}</p></div>
                    </div>
                     @if($registration->stay_status === 'checked_in')
                        <hr>
                        <form action="{{ route('frontdesk.registrations.checkout', $registration) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to check out the GROUP LEAD? Members will remain checked in.');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-door-open me-1"></i> Check Out Group Lead
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Group Members Management Table --}}
            @if($registration->is_group_lead && $groupMembers->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Group Members ({{ $groupMembers->count() }})</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover">
                                <thead><tr><th>Name</th><th>Room</th><th>Status</th><th>Bill</th><th class="text-end">Actions</th></tr></thead>
                                <tbody>
                                    @foreach($groupMembers as $member)
                                        <tr>
                                            <td>{{ $member->full_name }}</td>
                                            <td>{{ $member->room_allocation ?? 'N/A' }}</td>
                                            <td>
                                                @if($member->stay_status === 'checked_in')
                                                    <span class="badge bg-info">Checked-In</span>
                                                @else
                                                    <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $member->stay_status)) }}</span>
                                                @endif
                                            </td>
                                            <td>&#8358;{{ number_format($member->total_amount, 2) }}</td>
                                            <td class="text-end">
                                                @if($member->stay_status === 'checked_in')
                                                    <form action="{{ route('frontdesk.registrations.checkout', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to check out {{ $member->full_name }}?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">Check Out</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">Checked Out</span>
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

        {{-- Right Column: Guest Profile & Group Financials --}}
        <div class="col-lg-4">
            {{-- Group Financial Summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Group Financial Summary</h5></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Lead's Bill: <span>&#8358;{{ number_format($groupFinancialSummary['lead_bill'], 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Active Members' Bill: <span>&#8358;{{ number_format($groupFinancialSummary['members_bill'], 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                            Total Outstanding Bill: <span>&#8358;{{ number_format($groupFinancialSummary['total_outstanding'], 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Guest Profile Card (Unchanged) --}}
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Guest Profile</h5></div>
                <div class="card-body">
                    <h6 class="card-title">{{ $registration->guest->full_name ?? $registration->full_name }}</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Email:</strong> {{ $registration->guest->email ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Contact:</strong> {{ $registration->guest->contact_number ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Visit Count:</strong> <span class="badge bg-primary">{{ $registration->guest->visit_count ?? '1' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

