@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.leaves.admin') }}">Leave Management</a></li>
    <li class="breadcrumb-item active">Leave Report</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Leave Report</h1>
            <p class="text-muted mb-0">
                Annual leave utilization &amp; analytics &mdash;
                <strong>{{ $year }}</strong>
                @if ($department)
                    &middot; <span class="badge bg-secondary">{{ $department }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.leaves.calendar') }}" class="btn btn-outline-primary">
                <i class="fas fa-calendar-alt me-1"></i> Calendar
            </a>
            <a href="{{ route('staff.leaves.admin.history') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Leave History
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
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

    {{-- Filters --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="year" class="form-select" onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
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
        <div class="col-auto">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </form>

    {{-- ==================== EXECUTIVE SUMMARY ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center">
                    <div class="text-primary fs-2 mb-1"><i class="fas fa-users"></i></div>
                    <h3 class="mb-0 fw-bold">{{ $totalEmployees }}</h3>
                    <small class="text-muted">Employees</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center">
                    <div class="text-success fs-2 mb-1"><i class="fas fa-check-circle"></i></div>
                    <h3 class="mb-0 fw-bold">{{ $totalApprovedRequests }}</h3>
                    <small class="text-muted">Approved Requests</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body text-center">
                    <div class="text-warning fs-2 mb-1"><i class="fas fa-clock"></i></div>
                    <h3 class="mb-0 fw-bold">{{ $totalPendingRequests }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center">
                    <div class="text-danger fs-2 mb-1"><i class="fas fa-times-circle"></i></div>
                    <h3 class="mb-0 fw-bold">{{ $totalRejectedRequests + $totalCancelledRequests }}</h3>
                    <small class="text-muted">Rejected / Cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body text-center">
                    <div class="text-info fs-2 mb-1"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="mb-0 fw-bold">{{ $totalLeaveDays }}</h3>
                    <small class="text-muted">Days Used</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body text-center">
                    <div class="text-purple fs-2 mb-1"><i class="fas fa-chart-line"></i></div>
                    <h3 class="mb-0 fw-bold {{ $prevDaysUsed > $totalLeaveDays ? 'text-danger' : 'text-success' }}">
                        {{ $totalLeaveDays - $prevDaysUsed > 0 ? '+' : '' }}{{ $totalLeaveDays - $prevDaysUsed }}
                    </h3>
                    <small class="text-muted">
                        vs {{ $prevYear }}
                        @if ($prevDaysUsed > 0)
                            ({{ round(($totalLeaveDays - $prevDaysUsed) / $prevDaysUsed * 100, 1) }}%)
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Averages row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-calculator fa-2x text-primary"></i>
                    <div>
                        <small class="text-muted d-block">Avg Days Per Request</small>
                        <span class="fw-bold fs-5">{{ $avgDaysPerRequest }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-user fa-2x text-success"></i>
                    <div>
                        <small class="text-muted d-block">Avg Days Per Employee</small>
                        <span class="fw-bold fs-5">{{ $avgDaysPerEmployee }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-star fa-2x text-warning"></i>
                    <div>
                        <small class="text-muted d-block">Most Used Leave Type</small>
                        <span class="fw-bold fs-5">{{ $mostUsedType ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== PENDING APPROVALS ALERT ==================== --}}
    @if ($pendingRequests->isNotEmpty())
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start gap-3">
                <i class="fas fa-exclamation-triangle fa-2x mt-1"></i>
                <div class="flex-grow-1">
                    <strong>{{ $pendingRequests->count() }} pending leave request(s)</strong> awaiting your approval.
                    @if ($pendingRequests->count() > 5)
                        <span class="text-muted">(showing latest 5)</span>
                    @endif
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        @foreach ($pendingRequests->take(5) as $pr)
                            <a href="{{ route('staff.leaves.admin') }}"
                               class="badge bg-warning text-dark text-decoration-none px-3 py-2">
                                {{ $pr->employee->name ?? 'N/A' }}
                                &mdash; {{ $pr->leave_type }}
                                <small>({{ $pr->days_count }} day{{ $pr->days_count !== 1 ? 's' : '' }})</small>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('staff.leaves.admin') }}" class="btn btn-warning btn-sm flex-shrink-0">
                    Review <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- ==================== CHARTS ROW ==================== --}}
    <div class="row g-4 mb-4">

        {{-- Leave Type Distribution --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="fas fa-pie-chart me-2 text-primary"></i>Leave Type Distribution</span>
                    <small class="text-muted">{{ $totalApprovedRequests }} approved requests</small>
                </div>
                <div class="card-body">
                    @php
                        $maxLeaveDays = $leaveTypeStats->max('days_used') ?? 1;
                        $typeColors = ['Annual' => 'primary', 'Casual' => 'success', 'Sick' => 'danger', 'Compassionate' => 'secondary', 'Paternity' => 'info', 'Maternity' => 'warning'];
                    @endphp
                    @forelse ($leaveTypes as $lt)
                        @php $stat = $leaveTypeStats->get($lt); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="badge bg-{{ $typeColors[$lt] ?? 'secondary' }} me-1">{{ $lt }}</span>
                                    @if ($lt === $mostUsedType)
                                        <i class="fas fa-crown text-warning small" title="Most used"></i>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $stat ? $stat->days_used . ' days / ' . $stat->approved_count . ' req' : '0 days' }}
                                </small>
                            </div>
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar bg-{{ $typeColors[$lt] ?? 'secondary' }}"
                                     style="width: {{ $stat ? round(($stat->days_used / $maxLeaveDays) * 100) : 0 }}%;"
                                     role="progressbar"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No leave data for {{ $year }}.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="fas fa-chart-bar me-2 text-success"></i>Monthly Leave Days</span>
                    <small class="text-muted">{{ array_sum($monthlyData) }} days total</small>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end gap-2" style="height:160px;">
                        @foreach ($monthlyData as $i => $val)
                            @php
                                $pct = $maxMonthlyDays > 0 ? max(round(($val / $maxMonthlyDays) * 100), 1) : 1;
                                $barColor = $val >= 30 ? 'danger' : ($val >= 15 ? 'warning' : 'success');
                            @endphp
                            <div class="flex-grow-1 d-flex flex-column align-items-center h-100 justify-content-end">
                                <small class="fw-bold text-{{ $barColor }} mb-1">{{ $val }}</small>
                                <div class="w-100 rounded-1 bg-{{ $barColor }}"
                                     style="height: {{ $pct }}%; min-height:2px; transition: height 0.3s;"
                                     title="{{ $monthlyLabels[$i] }}: {{ $val }} days"></div>
                                <small class="text-muted mt-1" style="font-size:9px;">{{ $monthlyLabels[$i] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ==================== DEPARTMENT BREAKDOWN ==================== --}}
    @if ($deptStats->isNotEmpty() && !$department)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-bold"><i class="fas fa-building me-2 text-secondary"></i>Department Breakdown</span>
                <small class="text-muted">{{ $deptStats->count() }} departments</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-center">Employees</th>
                                <th class="text-center">Total Requests</th>
                                <th class="text-center">Approved</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Days Used</th>
                                <th class="text-center">Avg Days / Emp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxDeptDays = $deptStats->max('days_used') ?: 1;
                            @endphp
                            @foreach ($deptStats as $deptName => $ds)
                                <tr>
                                    <td><strong>{{ $deptName }}</strong></td>
                                    <td class="text-center">{{ $ds['employee_count'] }}</td>
                                    <td class="text-center">{{ $ds['total_requests'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $ds['approved_requests'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($ds['pending_requests'] > 0)
                                            <span class="badge bg-warning text-dark">{{ $ds['pending_requests'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                                                <div class="progress-bar bg-info"
                                                     style="width: {{ round(($ds['days_used'] / $maxDeptDays) * 100) }}%;"
                                                     role="progressbar"></div>
                                            </div>
                                            <small class="fw-medium">{{ $ds['days_used'] }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ $ds['employee_count'] > 0 ? round($ds['days_used'] / $ds['employee_count'], 1) : 0 }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== EMPLOYEE DETAIL TABLE ==================== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="fw-bold">
                <i class="fas fa-users me-2 text-gold"></i>Employee Leave Details
            </span>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted">{{ $totalEmployees }} employee(s)</small>
                <input type="text" id="employeeSearch" class="form-control form-control-sm" style="width:200px;"
                       placeholder="Search employee..." onkeyup="filterEmployeeTable()">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="employeeTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th class="text-center">Total Days</th>
                            <th class="text-center">Used</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Utilization</th>
                            <th class="text-center">Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @if ($employee->leaveBalances->isEmpty())
                                <tr class="employee-row">
                                    <td><strong>{{ $employee->name }}</strong></td>
                                    <td>{{ $employee->department ?? '—' }}</td>
                                    <td colspan="6" class="text-muted">No leave balances configured for {{ $year }}.</td>
                                </tr>
                            @else
                                @foreach ($employee->leaveBalances as $balance)
                                    @php
                                        $util = $balance->total_days > 0
                                            ? round(($balance->used_days / $balance->total_days) * 100, 1)
                                            : 0;
                                        $utilColor = $util <= 50 ? 'success' : ($util <= 80 ? 'warning' : 'danger');
                                        $remColor = $balance->remaining_days > 5
                                            ? 'success' : ($balance->remaining_days > 0 ? 'warning' : 'danger');
                                        $typeColor = match ($balance->leave_type) {
                                            'Annual' => 'primary',
                                            'Sick' => 'danger',
                                            'Casual' => 'success',
                                            'Compassionate' => 'secondary',
                                            'Maternity' => 'warning',
                                            'Paternity' => 'info',
                                            default => 'secondary',
                                        };
                                        $approvedCount = $employee->{"approved_" . strtolower($balance->leave_type) . "_count"} ?? 0;
                                    @endphp
                                    <tr class="employee-row">
                                        <td>
                                            <strong>{{ $employee->name }}</strong>
                                            @if ($loop->first && $balance->remaining_days === 0)
                                                <i class="fas fa-exclamation-circle text-danger ms-1 small"
                                                   title="Leave exhausted"></i>
                                            @endif
                                        </td>
                                        <td>{{ $employee->department ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $typeColor }}">{{ $balance->leave_type }}</span>
                                        </td>
                                        <td class="text-center">{{ $balance->total_days }}</td>
                                        <td class="text-center">{{ $balance->used_days }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $remColor }}">{{ $balance->remaining_days }}</span>
                                        </td>
                                        <td class="text-center" style="min-width:110px;">
                                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                                <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                                                    <div class="progress-bar bg-{{ $utilColor }}"
                                                         style="width:{{ $util }}%;"
                                                         role="progressbar"></div>
                                                </div>
                                                <small class="text-{{ $utilColor }} fw-medium">{{ $util }}%</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $approvedCount }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No employees found for {{ $year }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function filterEmployeeTable() {
    const input = document.getElementById('employeeSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.employee-row');
    rows.forEach(row => {
        const name = row.querySelector('td strong')?.textContent?.toLowerCase() || '';
        const dept = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
        const type = row.querySelector('td:nth-child(3) .badge')?.textContent?.toLowerCase() || '';
        row.style.display = name.includes(filter) || dept.includes(filter) || type.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
