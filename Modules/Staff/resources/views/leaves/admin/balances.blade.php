@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Employee Leave Balances ({{ $currentYear }})</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <form method="GET" action="{{ route('staff.leaves.admin.balances') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or staff code..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Staff Code</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->staff_code }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#balanceModal-{{ $employee->id }}">
                                    Manage Balances
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>

@foreach ($employees as $employee)
<div class="modal fade" id="balanceModal-{{ $employee->id }}" tabindex="-1" aria-labelledby="balanceModalLabel-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balanceModalLabel-{{ $employee->id }}">Balances for {{ $employee->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Existing Balances for {{ $currentYear }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Total</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->leaveBalances as $balance)
                            <tr>
                                <td>{{ $balance->leave_type }}</td>
                                <td>{{ $balance->total_days }}</td>
                                <td>{{ $balance->used_days }}</td>
                                <td>{{ $balance->remaining_days }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('staff.leaves.admin.balances.reset', $balance->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reset used days to 0 for this leave type?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Reset Used Days"><i class="fas fa-sync-alt"></i></button>
                                        </form>
                                        <form action="{{ route('staff.leaves.admin.balances.delete', $balance->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this leave type?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Leave Type"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">No balances set for this year.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <hr>

                <h6>Update or Create Balance</h6>
                <form action="{{ route('staff.leaves.admin.balances.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="year" value="{{ $currentYear }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="leave_type-{{ $employee->id }}" class="form-label">Leave Type</label>
                            <input class="form-control" list="leaveTypeOptions" id="leave_type-{{ $employee->id }}" name="leave_type" placeholder="e.g., Annual or Study Leave" required>
                            <datalist id="leaveTypeOptions">
                                <option value="Annual">
                                <option value="Sick">
                                <option value="Casual">
                                <option value="Maternity">
                                <option value="Paternity">
                                <option value="Compassionate">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="total_days-{{ $employee->id }}" class="form-label">Set Total Days</label>
                            <input type="number" name="total_days" id="total_days-{{ $employee->id }}" class="form-control" required min="0" placeholder="e.g., 21">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Balance</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection