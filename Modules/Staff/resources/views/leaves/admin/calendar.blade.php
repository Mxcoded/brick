@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Leave Calendar</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Leave Calendar</h1>
            <p class="text-muted mb-0">{{ $firstOfMonth->format('F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.leaves.admin.history') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Leave History
            </a>
            <a href="{{ route('staff.leaves.report') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-alt me-1"></i> Reports
            </a>
        </div>
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

    {{-- Navigation & Filters --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="d-flex gap-2">
            <a href="{{ route('staff.leaves.calendar', ['month' => $month - 1, 'year' => $month == 1 ? $year - 1 : $year, 'department' => $department]) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <a href="{{ route('staff.leaves.calendar', ['month' => $month + 1, 'year' => $month == 12 ? $year + 1 : $year, 'department' => $department]) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('staff.leaves.calendar') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-calendar-day me-1"></i> Today
            </a>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <select name="department" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Legend --}}
    <div class="d-flex gap-3 mb-3 small">
        <span><span class="badge bg-annual" style="background:#0d6efd;">&nbsp;&nbsp;&nbsp;</span> Annual</span>
        <span><span class="badge bg-sick" style="background:#dc3545;">&nbsp;&nbsp;&nbsp;</span> Sick</span>
        <span><span class="badge bg-casual" style="background:#198754;">&nbsp;&nbsp;&nbsp;</span> Casual</span>
        <span><span class="badge bg-compassionate" style="background:#6f42c1;">&nbsp;&nbsp;&nbsp;</span> Compassionate</span>
        <span><span class="badge bg-maternity" style="background:#fd7e14;">&nbsp;&nbsp;&nbsp;</span> Maternity</span>
        <span><span class="badge bg-paternity" style="background:#20c997;">&nbsp;&nbsp;&nbsp;</span> Paternity</span>
        <span><span class="badge bg-other" style="background:#6c757d;">&nbsp;&nbsp;&nbsp;</span> Other</span>
    </div>

    {{-- Calendar Grid --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 calendar-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center py-2" style="width:14.28%;">Sun</th>
                        <th class="text-center py-2" style="width:14.28%;">Mon</th>
                        <th class="text-center py-2" style="width:14.28%;">Tue</th>
                        <th class="text-center py-2" style="width:14.28%;">Wed</th>
                        <th class="text-center py-2" style="width:14.28%;">Thu</th>
                        <th class="text-center py-2" style="width:14.28%;">Fri</th>
                        <th class="text-center py-2" style="width:14.28%;">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $today = now()->format('Y-m-d');
                    @endphp
                    @foreach (collect($startOfCalendar->copy()->toPeriod($endOfCalendar))->chunk(7) as $week)
                        <tr>
                            @foreach ($week as $day)
                                @php
                                    $key = $day->format('Y-m-d');
                                    $dayLeaves = $dateMap[$key] ?? [];
                                    $inMonth = $day->month === $month;
                                    $isToday = $key === $today;
                                    $dayClass = '';
                                    if (!$inMonth) $dayClass .= ' text-muted bg-light';
                                    if ($isToday) $dayClass .= ' today-cell';
                                    $count = count($dayLeaves);
                                @endphp
                                <td class="calendar-day {{ $dayClass }} position-relative"
                                    style="height:100px; vertical-align:top; {{ $isToday ? 'background:#fff8e1;' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start p-1">
                                        <span class="fw-bold {{ $isToday ? 'badge bg-warning text-dark rounded-circle px-2' : '' }}">
                                            {{ $day->day }}
                                        </span>
                                        @if ($count > 0)
                                            <span class="badge bg-secondary rounded-pill" style="font-size:10px;">{{ $count }}</span>
                                        @endif
                                    </div>
                                    @if ($count > 0)
                                        <div style="font-size:10px; line-height:1.3;" class="px-1">
                                            @foreach ($dayLeaves->take(3) as $leave)
                                                @php
                                                    $typeColor = match($leave->leave_type) {
                                                        'Annual' => '#0d6efd',
                                                        'Sick' => '#dc3545',
                                                        'Casual' => '#198754',
                                                        'Compassionate' => '#6f42c1',
                                                        'Maternity' => '#fd7e14',
                                                        'Paternity' => '#20c997',
                                                        default => '#6c757d',
                                                    };
                                                @endphp
                                                <div class="mb-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:{{ $typeColor }};margin-right:3px;"></span>
                                                    {{ $leave->employee->name }}
                                                </div>
                                            @endforeach
                                            @if ($count > 3)
                                                <div class="text-primary fw-semibold" style="cursor:pointer;" data-bs-toggle="modal"
                                                     data-bs-target="#dayModal{{ str_replace('-', '', $key) }}">
                                                    +{{ $count - 3 }} more
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Day Detail Modals --}}
@foreach ($startOfCalendar->copy()->toPeriod($endOfCalendar) as $day)
    @php
        $key = $day->format('Y-m-d');
        $dayLeaves = $dateMap[$key] ?? [];
    @endphp
    @if (count($dayLeaves) > 3)
        <div class="modal fade" id="dayModal{{ str_replace('-', '', $key) }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $day->format('l, F d, Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dayLeaves as $leave)
                                    <tr>
                                        <td>{{ $leave->employee->name }}</td>
                                        <td>{{ $leave->employee->department ?? '—' }}</td>
                                        <td>
                                            @php
                                                $badgeColor = match($leave->leave_type) {
                                                    'Annual' => 'bg-primary',
                                                    'Sick' => 'bg-danger',
                                                    'Casual' => 'bg-success',
                                                    'Compassionate' => 'bg-purple',
                                                    'Maternity' => 'bg-orange',
                                                    'Paternity' => 'bg-teal',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeColor }}">{{ $leave->leave_type }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<style>
.calendar-table td { transition: background 0.15s; }
.calendar-table td:hover { background: #f8f9fa; }
.today-cell { background: #fff8e1 !important; }
.bg-purple { background: #6f42c1; }
.bg-orange { background: #fd7e14; }
.bg-teal { background: #20c997; }
</style>
@endsection
