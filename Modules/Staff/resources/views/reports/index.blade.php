@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Advanced Reports</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Advanced Reports</h1>
            <p class="text-muted mb-0">Year {{ $year }}</p>
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
            <select name="year" class="form-select" onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="headcount-tab" data-bs-toggle="tab" data-bs-target="#headcount" type="button">
                <i class="fas fa-chart-line me-1"></i> Headcount Trend
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="turnover-tab" data-bs-toggle="tab" data-bs-target="#turnover" type="button">
                <i class="fas fa-people-arrows me-1"></i> Turnover
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="absenteeism-tab" data-bs-toggle="tab" data-bs-target="#absenteeism" type="button">
                <i class="fas fa-user-slash me-1"></i> Absenteeism
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button">
                <i class="fas fa-umbrella-beach me-1"></i> Leave Utilization
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- 1. Headcount Trend --}}
        <div class="tab-pane fade show active" id="headcount" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Headcount ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    @if (count($headcountTrend))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        @foreach ($headcountTrend as $row)
                                            <th class="text-center">{{ $row->month }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Active Employees</strong></td>
                                        @foreach ($headcountTrend as $row)
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">{{ $row->count }}</span>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex align-items-end gap-2" style="height: 160px;">
                                @php $maxCount = max(array_column($headcountTrend, 'count')) ?: 1; @endphp
                                @foreach ($headcountTrend as $row)
                                    <div class="flex-fill text-center">
                                        <div class="small text-muted mb-1">{{ $row->count }}</div>
                                        <div class="bg-primary rounded-top mx-auto"
                                             style="height: {{ max(4, ($row->count / $maxCount) * 120) }}px; width: 70%;"></div>
                                        <div class="small text-muted mt-1">{{ $row->month }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No employee data available.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. Turnover --}}
        <div class="tab-pane fade" id="turnover" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Turnover ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    @if (count($turnoverData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-center">New Hires</th>
                                        <th class="text-center">Departures</th>
                                        <th class="text-center">Avg Headcount</th>
                                        <th class="text-center">Turnover Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($turnoverData as $row)
                                        <tr>
                                            <td><strong>{{ $row->month }}</strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $row->hires }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $row->departures }}</span>
                                            </td>
                                            <td class="text-center">{{ $row->avg_headcount }}</td>
                                            <td class="text-center">
                                                @php
                                                    $rateColor = $row->turnover_rate <= 5 ? 'success' : ($row->turnover_rate <= 15 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge bg-{{ $rateColor }}">{{ $row->turnover_rate }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            @php
                                $yearAvgTurnover = count($turnoverData) > 0
                                    ? round(array_sum(array_column($turnoverData, 'turnover_rate')) / count($turnoverData), 1)
                                    : 0;
                            @endphp
                            <div class="alert alert-info d-inline-block mb-0">
                                <strong>Year Average Turnover Rate:</strong> {{ $yearAvgTurnover }}%
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No turnover data available.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. Absenteeism by Department --}}
        <div class="tab-pane fade" id="absenteeism" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Absenteeism by Department ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    @if (count($absenteeismByDept))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-center">Employees</th>
                                        <th class="text-center">Total Logs</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Late</th>
                                        <th class="text-center">Absenteeism Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($absenteeismByDept as $row)
                                        <tr>
                                            <td><strong>{{ $row->department }}</strong></td>
                                            <td class="text-center">{{ $row->employee_count }}</td>
                                            <td class="text-center">{{ $row->total_logs }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $row->absent }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning text-dark">{{ $row->late }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $abColor = $row->absenteeism_rate <= 5 ? 'success' : ($row->absenteeism_rate <= 15 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge bg-{{ $abColor }}">{{ $row->absenteeism_rate }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <p class="text-muted mb-0">No absenteeism data available. Attendance logging may not be active yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. Leave Utilization --}}
        <div class="tab-pane fade" id="leave" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">By Leave Type ({{ $year }})</h5>
                        </div>
                        <div class="card-body">
                            @if ($leaveUtilization->byType->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Leave Type</th>
                                                <th class="text-center">Requests</th>
                                                <th class="text-center">Total Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($leaveUtilization->byType as $row)
                                                <tr>
                                                    <td><strong>{{ $row->leave_type }}</strong></td>
                                                    <td class="text-center">{{ $row->request_count }}</td>
                                                    <td class="text-center">{{ $row->total_days }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No approved leave requests for {{ $year }}.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">By Department ({{ $year }})</h5>
                        </div>
                        <div class="card-body">
                            @if ($leaveUtilization->byDepartment->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Department</th>
                                                <th class="text-center">Requests</th>
                                                <th class="text-center">Total Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($leaveUtilization->byDepartment as $row)
                                                <tr>
                                                    <td><strong>{{ $row->department }}</strong></td>
                                                    <td class="text-center">{{ $row->request_count }}</td>
                                                    <td class="text-center">{{ $row->total_days }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No approved leave requests for {{ $year }}.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-12">
                    <div class="alert alert-info d-inline-block mb-0">
                        <strong>Total:</strong>
                        {{ $leaveUtilization->totals->total_requests }} requests,
                        {{ $leaveUtilization->totals->total_days }} days approved in {{ $year }}.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
