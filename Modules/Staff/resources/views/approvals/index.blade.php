@extends('layouts.master')

@section('page-content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employee Approvals</h1>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.approvals.index') }}" class="d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- Employee Approvals Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                <a href="{{ route('staff.approvals.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Name
                                    @if(request('sort') == 'name')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->name }}</td>
                                <td>
                                    <span class="badge 
                                        @if($employee->status == 'approved') badge-success
                                        @elseif($employee->status == 'rejected') badge-danger
                                        @elseif($employee->status == 'draft') badge-warning
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($employee->status == 'draft')
                                            <form action="{{ route('staff.approve', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $employee->id }}">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Rejection Modal -->
                                    <div class="modal fade" id="rejectModal{{ $employee->id }}" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel">Reject Employee</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('staff.reject', $employee->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to reject <strong>{{ $employee->name }}</strong>?</p>
                                                        <div class="mb-3">
                                                            <label for="rejection_reason" class="form-label">Rejection Reason (optional)</label>
                                                            <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection