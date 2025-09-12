@extends('layouts.master')

@section('page-content')
    <div class="container-fluid my-4">
        <h1 class="mb-4">Manage Leave Requests</h1>
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Pending Leave Requests</h5>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card-body">
                @if ($leaveRequests->isEmpty())
                    <p class="text-muted">No pending leave requests.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaveRequests as $request)
                                    <tr>
                                        <td>{{ $request->employee->name }}</td>
                                        <td>{{ $request->leave_type }}</td>
                                        <td>{{ $request->start_date }}</td>
                                        <td>{{ $request->end_date }}</td>
                                        <td>{{ $request->days_count }}</td>
                                        <td>{{ $request->reason ?? 'N/A' }}</td>
                                        <td>
                                            <form action="{{ route('staff.leaves.approve', $request->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('staff.leaves.reject', $request->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <input type="text" name="admin_note" placeholder="Rejection note"
                                                    class="form-control d-inline-block w-auto">
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
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
