@extends('staff::layouts.master')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Leave Report ({{ $year }})</h1>
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="year" class="form-label">Select Year</label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ $year }}" min="2000" max="{{ date('Y') }}">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Total Days</th>
                                <th>Used Days</th>
                                <th>Remaining Days</th>
                                <th>Approved Requests</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                @foreach ($employee->leaveBalances as $balance)
                                    <tr>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $balance->leave_type }}</td>
                                        <td>{{ $balance->total_days }}</td>
                                        <td>{{ $balance->used_days }}</td>
                                        <td>{{ $balance->remaining_days }}</td>
                                        <td>{{ $employee->leaveRequests->where('status', 'approved')->where('leave_type', $balance->leave_type)->count() }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection