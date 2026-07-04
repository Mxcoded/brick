@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance Report</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Attendance Report</h1>
            <p class="text-muted mb-0">{{ Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</p>
        </div>
        <a href="{{ route('staff.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-calendar-day me-1"></i> Daily View
        </a>
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
            <select name="month" class="form-select" onchange="this.form.submit()">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="year" class="form-select" onchange="this.form.submit()">
                @foreach (range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total_employees }}</h4>
                <small>Employees</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total_present }}</h4>
                <small>Present Days</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total_late }}</h4>
                <small>Late Days</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total_absent }}</h4>
                <small>Absent Days</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->avg_attendance_rate }}%</h4>
                <small>Avg Attendance</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ intdiv($summary->total_overtime_minutes, 60) }}h {{ $summary->total_overtime_minutes % 60 }}m</h4>
                <small>Total Overtime</small>
            </div>
        </div>
    </div>

    {{-- Per-Employee Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>On Leave</th>
                            <th>Late Min</th>
                            <th>Overtime</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->employee->name }}</strong>
                                </td>
                                <td>{{ $row->employee->department ?? '—' }}</td>
                                <td><span class="badge bg-success">{{ $row->present }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $row->late }}</span></td>
                                <td><span class="badge bg-danger">{{ $row->absent }}</span></td>
                                <td><span class="badge bg-info">{{ $row->on_leave }}</span></td>
                                <td>{{ $row->late_minutes > 0 ? $row->late_minutes . ' min' : '—' }}</td>
                                <td>{{ $row->overtime_minutes > 0 ? intdiv($row->overtime_minutes, 60) . 'h ' . ($row->overtime_minutes % 60) . 'm' : '—' }}</td>
                                <td>
                                    @php
                                        $rateColor = $row->attendance_rate >= 90 ? 'success' : ($row->attendance_rate >= 75 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $rateColor }}">{{ $row->attendance_rate }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No data for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
