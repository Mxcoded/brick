@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">HR Dashboard</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">HR Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Staff
            </a>
            <a href="{{ route('staff.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i> View All Staff
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Row 1: Quick Stats Cards --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-white-50 text-uppercase">Total Active</span>
                        <h3 class="mb-0 mt-1">{{ $totalEmployees }}</h3>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-white-50 text-uppercase">At Work</span>
                        <h3 class="mb-0 mt-1">{{ $activeAtWork }}</h3>
                    </div>
                    <i class="fas fa-user-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <button type="button" class="card-link w-100 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#onLeaveModal">
                <div class="card bg-warning text-dark shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted text-uppercase">On Leave</span>
                            <h3 class="mb-0 mt-1">{{ $onLeaveCount }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card bg-info text-white shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-white-50 text-uppercase">Approvals</span>
                        <h3 class="mb-0 mt-1">{{ $pendingApprovals }}</h3>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-white-50 text-uppercase">Exited</span>
                        <h3 class="mb-0 mt-1">{{ $exitedEmployees }}</h3>
                    </div>
                    <i class="fas fa-user-minus fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card bg-purple text-white shadow-sm h-100" style="background-color: #6f42c1 !important;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-white-50 text-uppercase">New This Month</span>
                        <h3 class="mb-0 mt-1">{{ $newHiresThisMonth }}</h3>
                    </div>
                    <i class="fas fa-user-plus fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 2: Department / Branch / Gender --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-building me-2 text-gold"></i>
                    <h5 class="mb-0">Department Distribution</h5>
                </div>
                <div class="card-body">
                    @if($departmentStats->count())
                        @foreach($departmentStats as $dept)
                            @php
                                $pct = $totalEmployees > 0 ? round(($dept->count / $totalEmployees) * 100) : 0;
                                $colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'];
                                $color = $colors[$loop->index % count($colors)];
                                $barWidth = $pct;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium">{{ $dept->department }}</span>
                                    <span class="text-muted">{{ $dept->count }} ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $barWidth }}%; background-color: {{ $color }};"
                                        aria-valuenow="{{ $barWidth }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No department data available.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-map-marker-alt me-2 text-gold"></i>
                    <h5 class="mb-0">Branch Breakdown</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                                <i class="fas fa-building fa-2x text-primary mb-2"></i>
                                <h3 class="mb-0">{{ $asokoroCount }}</h3>
                                <small class="text-muted">Asokoro</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10">
                                <i class="fas fa-building fa-2x text-success mb-2"></i>
                                <h3 class="mb-0">{{ $wuseCount }}</h3>
                                <small class="text-muted">Wuse</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-secondary bg-opacity-10">
                                <i class="fas fa-building fa-2x text-muted mb-2"></i>
                                <h3 class="mb-0">{{ $otherBranchCount }}</h3>
                                <small class="text-muted">Other</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-venus-mars me-2 text-gold"></i>
                    <h5 class="mb-0">Gender Diversity</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @php
                        $totalGender = $maleCount + $femaleCount + $otherGenderCount;
                        $malePct = $totalGender > 0 ? round(($maleCount / $totalGender) * 100) : 0;
                        $femalePct = $totalGender > 0 ? round(($femaleCount / $totalGender) * 100) : 0;
                        $otherPct = $totalGender > 0 ? round(($otherGenderCount / $totalGender) * 100) : 0;
                    @endphp
                    <div class="d-flex justify-content-center gap-4 mb-3">
                        <div class="text-center">
                            <div class="mb-1">
                                <i class="fas fa-mars fa-3x text-primary"></i>
                            </div>
                            <h3 class="mb-0">{{ $maleCount }}</h3>
                            <small class="text-muted">Male ({{ $malePct }}%)</small>
                        </div>
                        <div class="text-center">
                            <div class="mb-1">
                                <i class="fas fa-venus fa-3x text-danger"></i>
                            </div>
                            <h3 class="mb-0">{{ $femaleCount }}</h3>
                            <small class="text-muted">Female ({{ $femalePct }}%)</small>
                        </div>
                        <div class="text-center">
                            <div class="mb-1">
                                <i class="fas fa-genderless fa-3x text-muted"></i>
                            </div>
                            <h3 class="mb-0">{{ $otherGenderCount }}</h3>
                            <small class="text-muted">Other ({{ $otherPct }}%)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 3: Attendance Overview --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-clipboard-check me-2 text-gold"></i>
                        <span class="fw-bold">Today's Attendance</span>
                        <span class="text-muted small ms-2">{{ now()->format('l, F d, Y') }}</span>
                    </div>
                    <a href="{{ route('staff.attendance.index') }}" class="btn btn-sm btn-outline-primary">
                        Full View <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $today = now()->today();
                        $presentToday = \Modules\Staff\Models\AttendanceLog::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count();
                        $lateToday = \Modules\Staff\Models\AttendanceLog::whereDate('date', $today)->where('status', 'late')->count();
                        $absentToday = \Modules\Staff\Models\AttendanceLog::whereDate('date', $today)->where('status', 'absent')->count();
                        $onLeaveToday = \Modules\Staff\Models\AttendanceLog::whereDate('date', $today)->where('status', 'on_leave')->count();
                        $totalActive = $totalEmployees - $onLeaveCount;
                        $noRecordToday = max(0, $totalActive - $presentToday - $absentToday - $onLeaveToday);
                    @endphp
                    <div class="row text-center g-3">
                        <div class="col">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $presentToday }}</h4>
                                <small class="text-muted">Present</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-warning bg-opacity-10">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ $lateToday }}</h4>
                                <small class="text-muted">Late</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-danger bg-opacity-10">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0">{{ $absentToday }}</h4>
                                <small class="text-muted">Absent</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-info bg-opacity-10">
                                <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $onLeaveToday }}</h4>
                                <small class="text-muted">On Leave</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-secondary bg-opacity-10">
                                <i class="fas fa-question-circle fa-2x text-secondary mb-2"></i>
                                <h4 class="mb-0">{{ $noRecordToday }}</h4>
                                <small class="text-muted">No Record</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Leave / Birthdays / Recent Hires --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-umbrella-beach me-2 text-gold"></i>
                    <h5 class="mb-0">Leave Overview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="fas fa-hourglass-half text-warning fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small">Pending Requests</span>
                            <h4 class="mb-0">{{ $pendingLeaves }}</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="fas fa-users text-info fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small">Currently Away</span>
                            <h4 class="mb-0">{{ $onLeaveCount }}</h4>
                        </div>
                    </div>
                    @if($onLeaveCount > 0)
                        <hr>
                        <small class="text-muted">Staff on leave today:</small>
                        <ul class="list-unstyled mt-1 mb-0 small">
                            @foreach($staffOnLeave->take(5) as $leave)
                                <li class="mb-1">
                                    <i class="fas fa-user text-muted me-1"></i>
                                    {{ $leave->employee->name ?? 'N/A' }}
                                    <span class="text-muted">({{ $leave->employee->department ?? 'N/A' }})</span>
                                </li>
                            @endforeach
                            @if($staffOnLeave->count() > 5)
                                <li class="text-primary" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#onLeaveModal">
                                    <i class="fas fa-plus-circle me-1"></i>See all {{ $staffOnLeave->count() }}
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-birthday-cake me-2 text-gold"></i>
                    <h5 class="mb-0">Birthdays This Month</h5>
                </div>
                <div class="card-body">
                    @if($upcomingBirthdays->count())
                        <ul class="list-unstyled mb-0">
                            @foreach($upcomingBirthdays as $employee)
                                <li class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                    <div class="rounded-circle bg-gold bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-cake-candles text-gold"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $employee->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($employee->date_of_birth)->format('d M') }}
                                            @if($employee->department)
                                                &middot; {{ $employee->department }}
                                            @endif
                                        </small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-day fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No birthdays this month.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-user-plus me-2 text-gold"></i>
                    <h5 class="mb-0">Recent Hires</h5>
                </div>
                <div class="card-body">
                    @if($recentHires->count())
                        <ul class="list-unstyled mb-0">
                            @foreach($recentHires as $employee)
                                <li class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                    <div class="flex-shrink-0 me-3">
                                        @if($employee->profile_image)
                                            <img src="{{ Storage::url($employee->profile_image) }}" alt=""
                                                class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $employee->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $employee->position ?? $employee->department ?? 'Staff' }}
                                            &middot; Joined {{ $employee->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('staff.index') }}" class="btn btn-sm btn-outline-primary w-100">
                            View All Staff <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No approved staff records yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

{{-- On Leave Modal --}}
<div class="modal fade" id="onLeaveModal" tabindex="-1" aria-labelledby="onLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="onLeaveModalLabel">
                    <i class="fas fa-calendar-alt me-2 text-warning"></i>Staff Currently On Leave
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($staffOnLeave->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Branch</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffOnLeave as $leave)
                                    <tr>
                                        <td>
                                            <strong>{{ $leave->employee->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $leave->employee->department ?? 'N/A' }}</td>
                                        <td>{{ $leave->employee->branch_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ $leave->leave_type ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0 text-center py-3">
                        <i class="fas fa-check-circle text-success me-1"></i>No staff are currently on leave.
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<style>
    .bg-purple { background-color: #6f42c1 !important; }
    .card-link { cursor: pointer; }
    .opacity-50 { opacity: 0.5; }
    .text-white-50 { color: rgba(255,255,255,0.7) !important; }
</style>
@endsection
