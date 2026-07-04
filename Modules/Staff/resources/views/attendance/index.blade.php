@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Attendance</h1>
            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.attendance.report') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-1"></i> Monthly Report
            </a>
            <a href="{{ route('staff.attendance.clock') }}" class="btn btn-success">
                <i class="fas fa-clock me-1"></i> Clock In / Out
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}
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

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                   onchange="this.form.submit()">
        </div>
        <div class="col-auto">
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card bg-success text-white shadow-sm text-center py-2">
                <h5 class="mb-0">{{ $todayStats['present'] }}</h5>
                <small>Present</small>
            </div>
        </div>
        <div class="col">
            <div class="card bg-warning text-dark shadow-sm text-center py-2">
                <h5 class="mb-0">{{ $todayStats['late'] }}</h5>
                <small>Late</small>
            </div>
        </div>
        <div class="col">
            <div class="card bg-danger text-white shadow-sm text-center py-2">
                <h5 class="mb-0">{{ $todayStats['absent'] }}</h5>
                <small>Absent</small>
            </div>
        </div>
        <div class="col">
            <div class="card bg-info text-white shadow-sm text-center py-2">
                <h5 class="mb-0">{{ $todayStats['on_leave'] }}</h5>
                <small>On Leave</small>
            </div>
        </div>
        <div class="col">
            <div class="card bg-secondary text-white shadow-sm text-center py-2">
                <h5 class="mb-0">{{ $todayStats['no_record'] }}</h5>
                <small>No Record</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Shift</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php
                                $attendance = $employee->attendanceLogs->first();
                                $assignment = $employee->shiftAssignments->first();
                                $status = $attendance?->status ?? 'no_record';
                                $badgeClass = match($status) {
                                    'present' => 'bg-success',
                                    'late' => 'bg-warning text-dark',
                                    'absent' => 'bg-danger',
                                    'on_leave' => 'bg-info',
                                    default => 'bg-secondary',
                                };
                                $statusLabel = match($status) {
                                    'no_record' => 'No Record',
                                    default => ucfirst($status),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($employee->profile_image)
                                            <img src="{{ Storage::url($employee->profile_image) }}" alt=""
                                                 class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-user fa-sm text-primary"></i>
                                            </div>
                                        @endif
                                        <strong>{{ $employee->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $employee->department ?? '—' }}</td>
                                <td>{{ $assignment?->shift?->name ?? '—' }}</td>
                                <td>{{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '—' }}</td>
                                <td>{{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '—' }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                                <td>
                                    @if ($attendance && $attendance->late_minutes > 0)
                                        <span class="text-warning">{{ $attendance->late_minutes }} min</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
