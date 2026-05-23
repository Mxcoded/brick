@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">User Login History</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-history me-2 text-primary"></i>User Login History</h1>
            <p class="text-muted mb-0">Track and monitor user login activities</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.login-logs.active-sessions') }}" class="btn btn-success">
                <i class="fas fa-users me-1"></i> Active Sessions
            </a>
            <button type="button" class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">Today's Logins</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_logins_today']) }}</h3>
                        </div>
                        <i class="fas fa-sign-in-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">Unique Users Today</h6>
                            <h3 class="mb-0">{{ number_format($stats['unique_users_today']) }}</h3>
                        </div>
                        <i class="fas fa-user-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">Active Sessions</h6>
                            <h3 class="mb-0">{{ number_format($stats['active_sessions']) }}</h3>
                        </div>
                        <i class="fas fa-wifi fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">Failed Today</h6>
                            <h3 class="mb-0">{{ number_format($stats['failed_logins_today']) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">This Week</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_logins_week']) }}</h3>
                        </div>
                        <i class="fas fa-calendar-week fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1 text-white-50">This Month</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_logins_month']) }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select class="form-select" id="filterUser">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Device Type</label>
                    <select class="form-select" id="filterDevice">
                        <option value="">All</option>
                        <option value="desktop">Desktop</option>
                        <option value="mobile">Mobile</option>
                        <option value="tablet">Tablet</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" id="filterDateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" id="filterDateTo">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Login Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Login Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="loginLogsTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Device / Browser</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTable will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#loginLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.login-logs.datatable") }}',
            data: function(d) {
                d.user_id = $('#filterUser').val();
                d.status = $('#filterStatus').val();
                d.device_type = $('#filterDevice').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
            }
        },
        columns: [
            { 
                data: 'user_name',
                render: function(data, type, row) {
                    return '<div><strong>' + data + '</strong><br><small class="text-muted">' + row.user_email + '</small></div>';
                }
            },
            { data: 'ip_address' },
            { data: 'device_info' },
            { data: 'logged_in_at_formatted' },
            { data: 'logged_out_at_formatted' },
            { data: 'session_duration' },
            { data: 'status_badge' }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'No login records found',
            zeroRecords: 'No matching records'
        }
    });

    // Filter handlers
    $('#filterUser, #filterStatus, #filterDevice, #filterDateFrom, #filterDateTo').on('change', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterUser, #filterStatus, #filterDevice').val('');
        $('#filterDateFrom, #filterDateTo').val('');
        table.ajax.reload();
    });

    // Export button
    $('#exportBtn').on('click', function() {
        var params = new URLSearchParams({
            user_id: $('#filterUser').val(),
            status: $('#filterStatus').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val()
        });
        window.location.href = '{{ route("admin.login-logs.export") }}?' + params.toString();
    });
});
</script>
@endsection
