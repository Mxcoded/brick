@extends('staff::layouts.master')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">My Leave Dashboard</h1>

        <!-- Leave Balance -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Leave Balance ({{ date('Y') }})</h5>
                {{-- <a href="{{ route('staff.leaves.balance') }}" class="btn btn-light btn-sm">Leave Balance</a> --}}
            </div>
            <div class="card-body">
                @forelse ($leaveBalances as $balance)
                    <p><strong>{{ $balance->leave_type }}:</strong> {{ $balance->remaining_days }} / {{ $balance->total_days }} days remaining</p>
                @empty
                    <p class="text-muted">No leave balance available.</p>
                @endforelse
            </div>
        </div>

        <!-- Leave Requests -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Leave Requests</h5>
                <a href="{{ route('staff.leaves.request') }}" class="btn btn-light btn-sm">Request Leave</a>
            </div>
            <div class="card-body">
                @if ($leaveRequests->isEmpty())
                    <p class="text-muted">No leave requests yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaveRequests as $request)
                                    <tr>
                                        <td>{{ $request->leave_type }}</td>
                                        <td>{{ $request->start_date }}</td>
                                        <td>{{ $request->end_date }}</td>
                                        <td>{{ $request->days_count }}</td>
                                        <td>
                                            <span class="badge {{ $request->status == 'approved' ? 'bg-success' : ($request->status == 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>@if ($request->status === 'rejected')
                                            {{ $request->admin_note ?? 'N/A' }}
                                        @else
                                            {{ $request->reason ?? 'N/A' }}
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