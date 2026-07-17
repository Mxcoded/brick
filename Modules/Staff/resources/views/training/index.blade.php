@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Training Records</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Training &amp; Certifications</h1>
            <p class="text-muted mb-0">Employee training history and certification tracking</p>
        </div>
        <a href="{{ route('staff.training.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Record
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md">
            <div class="card bg-primary text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total }}</h4>
                <small>Total Records</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-success text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->completed }}</h4>
                <small>Completed</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-warning text-dark shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->in_progress }}</h4>
                <small>In Progress</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-info text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->expiring_soon }}</h4>
                <small>Expiring Soon</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card bg-danger text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->expired }}</h4>
                <small>Expired</small>
            </div>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="internal" {{ $type === 'internal' ? 'selected' : '' }}>Internal</option>
                <option value="external" {{ $type === 'external' ? 'selected' : '' }}>External</option>
                <option value="certification" {{ $type === 'certification' ? 'selected' : '' }}>Certification</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="enrolled" {{ $status === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Course</th>
                            <th>Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Expiry</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td><strong>{{ $record->employee->name }}</strong></td>
                                <td>{{ $record->employee->department ?? '—' }}</td>
                                <td>{{ $record->course_name }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($record->training_type) }}</span></td>
                                <td>{{ $record->start_date->format('M d, Y') }}</td>
                                <td>{{ $record->end_date?->format('M d, Y') ?: '—' }}</td>
                                <td class="text-center">
                                    @php
                                        $statusColor = match($record->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'enrolled' => 'info',
                                            'cancelled' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ str_replace('_', ' ', ucfirst($record->status)) }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($record->expiry_date)
                                        @php
                                            $expColor = $record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInDays(now()) <= 30 ? 'warning' : 'success');
                                        @endphp
                                        <span class="badge bg-{{ $expColor }}">{{ $record->expiry_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('staff.training.edit', $record) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('staff.training.destroy', $record) }}" method="POST"
                                              onsubmit="return confirm('Delete this training record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-graduation-cap fa-2x mb-2 d-block"></i>
                                    No training records yet. <a href="{{ route('staff.training.create') }}">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $records->links() }}
    </div>

</div>
@endsection
