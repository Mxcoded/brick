@extends('staff::layouts.master')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Leave Dashboard</h1>
        <a href="{{ route('staff.leaves.request') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill me-2"></i>Request New Leave
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($upcomingLeave)
        <div class="alert alert-info shadow-sm" role="alert">
            <h4 class="alert-heading">🗓️ Upcoming Leave!</h4>
            <p>
                Your next approved leave is for <strong>{{ $upcomingLeave->days_count }} day(s)</strong> ({{ $upcomingLeave->leave_type }}).
            </p>
            <hr>
            <p class="mb-0">
                It starts on <strong>{{ \Carbon\Carbon::parse($upcomingLeave->start_date)->format('l, F jS, Y') }}</strong>, which is {{ \Carbon\Carbon::parse($upcomingLeave->start_date)->diffForHumans() }}.
            </p>
        </div>
    @endif
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Leave Balance ({{ date('Y') }})</h5>
        </div>
        <div class="card-body">
            @if($leaveBalances->isNotEmpty())
                <div class="row">
                    @foreach ($leaveBalances as $balance)
                        @php
                            $percentageUsed = ($balance->total_days > 0) ? ($balance->used_days / $balance->total_days) * 100 : 0;
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <strong>{{ $balance->leave_type }}</strong>
                            <div class="progress" style="height: 20px;" title="{{ $balance->used_days }} of {{ $balance->total_days }} days used">
                                <div class="progress-bar" role="progressbar" style="width: {{ $percentageUsed }}%;" aria-valuenow="{{ $balance->used_days }}" aria-valuemin="0" aria-valuemax="{{ $balance->total_days }}">
                                    {{ $balance->used_days }} Used
                                </div>
                            </div>
                            <small class="text-muted">{{ $balance->remaining_days }} days remaining of {{ $balance->total_days }} total</small>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No leave balances have been configured for you this year.</p>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">My Leave Requests</h5>
        </div>
        <div class="card-body">
            @if ($leaveRequests->isEmpty())
                <p class="text-muted">You haven't made any leave requests yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Reason / Note</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaveRequests as $request)
                                <tr>
                                    <td>{{ $request->leave_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->end_date)->format('d M, Y') }}</td>
                                    <td>{{ $request->days_count ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($request->status == 'approved') bg-success
                                            @elseif($request->status == 'rejected') bg-danger
                                            @elseif($request->status == 'cancelled') bg-secondary
                                            @else bg-warning text-dark @endif">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td><small>{{ $request->status === 'rejected' ? $request->admin_note : $request->reason }}</small></td>
                                    <td>
                                        @if($request->status == 'pending')
                                            <form action="{{ route('staff.leaves.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection