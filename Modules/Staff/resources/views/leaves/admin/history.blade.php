@extends('staff::layouts.master')

@section('content')
    <div class="container-fluid my-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel-fill"></i> Filter Leave History</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('staff.leaves.admin.history') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">All Employees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="leave_type" class="form-label">Leave Type</label>
                        <select name="leave_type" id="leave_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="Annual" {{ request('leave_type') == 'Annual' ? 'selected' : '' }}>Annual</option>
                            <option value="Sick" {{ request('leave_type') == 'Sick' ? 'selected' : '' }}>Sick</option>
                            <option value="Casual" {{ request('leave_type') == 'Casual' ? 'selected' : '' }}>Casual</option>
                            <option value="Maternity" {{ request('leave_type') == 'Maternity' ? 'selected' : '' }}>Maternity
                            </option>
                            <option value="Paternity" {{ request('leave_type') == 'Paternity' ? 'selected' : '' }}>Paternity
                            </option>
                            <option value="Compassionate" {{ request('leave_type') == 'Compassionate' ? 'selected' : '' }}>
                                Compassionate</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-6"><label for="start_date" class="form-label">From</label><input type="date"
                                    name="start_date" id="start_date" class="form-control"
                                    value="{{ request('start_date') }}"></div>
                            <div class="col-6"><label for="end_date" class="form-label">To</label><input type="date"
                                    name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('staff.leaves.admin.history') }}" class="btn btn-secondary ms-2">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">All Leave Requests</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Employee Name</th>
                                <th>Staff Code</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Reason / Admin Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveHistory as $request)
                                <tr>
                                    <td>{{ $request->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $request->staff_code }}</td>
                                    <td>{{ $request->leave_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->end_date)->format('d M, Y') }}</td>
                                    <td>{{ $request->days_count }}</td>
                                    <td>
                                        <span
                                            class="badge 
                                    @if ($request->status == 'approved') bg-success
                                    @elseif($request->status == 'rejected') bg-danger
                                    @else bg-warning text-dark @endif">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <small>
                                            @if ($request->status === 'rejected')
                                                <strong>Admin:</strong> {{ $request->admin_note ?? 'N/A' }}
                                            @else
                                                {{ $request->reason ?? 'N/A' }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if (!in_array($request->status, ['cancelled', 'rejected']))
                                            <form action="{{ route('staff.leaves.admin.cancel', $request->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No leave requests found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $leaveHistory->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
