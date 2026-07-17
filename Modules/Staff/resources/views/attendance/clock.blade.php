@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Clock In / Out</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Clock In / Out</h1>
            <p class="text-muted mb-0">{{ $employee->name }} &middot; {{ $employee->department ?? 'No Department' }}</p>
        </div>
        <a href="{{ route('staff.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> Attendance Overview
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

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        @if ($employee->profile_image)
                            <img src="{{ Storage::url($employee->profile_image) }}" alt=""
                                 class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-3x text-primary"></i>
                            </div>
                        @endif
                    </div>
                    <h4>{{ $employee->name }}</h4>
                    <p class="text-muted mb-1">{{ $employee->position ?? $employee->department ?? '' }}</p>
                    @if ($assignment && $assignment->shift)
                        <span class="badge bg-info mb-3">
                            {{ $assignment->shift->name }}
                            ({{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('h:i A') }})
                        </span>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-center gap-4 mt-3">
                        @if (!$attendance || !$attendance->clock_in)
                            <form method="POST" action="{{ route('staff.attendance.clock-in') }}">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="note" class="form-control form-control-sm @error('note') is-invalid @enderror"
                                           placeholder="Optional note..." maxlength="255">
                                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-sign-in-alt me-2"></i> Clock In
                                </button>
                            </form>
                        @elseif (!$attendance->clock_out)
                            <div class="text-center mb-3">
                                <p class="text-muted small mb-0">Clocked in at</p>
                                <h5 class="text-success">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}</h5>
                                @if ($attendance->status === 'late')
                                    <span class="badge bg-warning text-dark">
                                        Late by {{ $attendance->late_minutes }} min
                                    </span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('staff.attendance.clock-out') }}">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="note" class="form-control form-control-sm @error('note') is-invalid @enderror"
                                           placeholder="Optional note..." maxlength="255">
                                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-danger btn-lg px-5">
                                    <i class="fas fa-sign-out-alt me-2"></i> Clock Out
                                </button>
                            </form>
                        @else
                            <div class="text-center">
                                <div class="mb-3">
                                    <p class="text-muted small mb-0">Clocked in</p>
                                    <h5 class="text-success">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}</h5>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted small mb-0">Clocked out</p>
                                    <h5 class="text-danger">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') }}</h5>
                                </div>
                                @php
                                    $mins = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out));
                                    $h = intdiv($mins, 60);
                                    $m = $mins % 60;
                                @endphp
                                <span class="badge bg-secondary">Duration: {{ $h }}h {{ $m }}m</span>
                                <div class="mt-3">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                    <p class="text-muted mt-2 mb-0">All done for today.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <i class="fas fa-history me-2 text-gold"></i>
                    <span class="fw-bold">This Week</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $weekStart = now()->startOfWeek();
                                    $weekLogs = \Modules\Staff\Models\AttendanceLog::where('employee_id', $employee->id)
                                        ->whereBetween('date', [$weekStart, now()->endOfWeek()])
                                        ->with('shiftAssignment.shift')
                                        ->orderBy('date', 'desc')
                                        ->get();
                                @endphp
                                @forelse ($weekLogs as $log)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($log->date)->format('D, M d') }}</td>
                                        <td>{{ $log->shiftAssignment?->shift?->name ?? '—' }}</td>
                                        <td>{{ $log->clock_in ? \Carbon\Carbon::parse($log->clock_in)->format('h:i A') : '—' }}</td>
                                        <td>{{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('h:i A') : '—' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($log->status) {
                                                    'present' => 'bg-success',
                                                    'late' => 'bg-warning text-dark',
                                                    'absent' => 'bg-danger',
                                                    'on_leave' => 'bg-info',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No records this week.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
