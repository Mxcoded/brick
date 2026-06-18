@extends('layouts.master')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-tools me-2" style="color: var(--luxury-gold);"></i>Maintenance Logs</h2>
            <p class="text-muted mb-0">Track and manage all maintenance issues across departments</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
            <a href="{{ route('maintenance.report') }}" class="btn btn-outline-success">
                <i class="fas fa-chart-bar me-1"></i> Reports
            </a>
            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#quickReportModal">
                <i class="fas fa-bolt me-1"></i> Quick Report
            </button>
            @can('access_maintenance_dashboard')
                <a href="{{ route('maintenance.create') }}" class="btn btn-outline-gold">
                    <i class="fas fa-plus me-1"></i> New Log
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $stats = [
            'total' => $logs->count(),
            'open' => $logs->whereIn('status', ['new', 'in_progress'])->count(),
            'completed' => $logs->where('status', 'completed')->count(),
            'cancelled' => $logs->where('status', 'cancelled')->count(),
        ];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                    <div class="small text-muted text-uppercase">Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['open'] }}</div>
                    <div class="small text-muted text-uppercase">Open</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['completed'] }}</div>
                    <div class="small text-muted text-uppercase">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-secondary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-secondary">{{ $stats['cancelled'] }}</div>
                    <div class="small text-muted text-uppercase">Cancelled</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 py-3">
            <span class="fw-semibold"><i class="fas fa-list me-2" style="color: var(--luxury-gold);"></i>All Logs</span>
            <div class="d-flex flex-wrap gap-1" id="statusFilters">
                <button class="btn btn-sm status-filter active" data-status="">All</button>
                <button class="btn btn-sm status-filter" data-status="new">New</button>
                <button class="btn btn-sm status-filter" data-status="in_progress">Doing</button>
                <button class="btn btn-sm status-filter" data-status="completed">Done</button>
                <button class="btn btn-sm status-filter" data-status="cancelled">Cancel</button>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($logs->count())
                <div class="table-responsive">
                    <table id="logsTable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Location</th>
                                <th style="width: 100px;">Department</th>
                                <th style="width: 70px;">Priority</th>
                                <th>Complaint</th>
                                <th style="width: 130px;">Lodged By</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 90px;">Date</th>
                                <th style="width: 100px;">Cost (NGN)</th>
                                <th style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                @php
                                    $statusBorder = ['new' => '#ffc107', 'in_progress' => '#0d6efd', 'completed' => '#198754', 'cancelled' => '#6c757d'];
                                    $departmentColors = ['IT' => '#0d6efd', 'Maintenance' => '#198754', 'Housekeeping' => '#dc3545', 'Electrical' => '#ffc107', 'Plumbing' => '#0dcaf0', 'HVAC' => '#6f42c1', 'Security' => '#fd7e14', 'Other' => '#6c757d'];
                                    $deptColor = $departmentColors[$log->department] ?? '#6c757d';
                                @endphp
                                <tr data-status="{{ $log->status }}" style="border-left: 4px solid {{ $statusBorder[$log->status] }};">
                                    <td class="text-muted">{{ $log->id }}</td>
                                    <td class="fw-medium">{{ $log->location }}</td>
                                    <td><span class="badge" style="background-color: {{ $deptColor }};">{{ $log->department }}</span></td>
                                    <td>
                                        @php
                                            $priorityColors = ['low' => '#6c757d', 'medium' => '#ffc107', 'high' => '#fd7e14', 'critical' => '#dc3545'];
                                            $pColor = $priorityColors[$log->priority] ?? '#6c757d';
                                        @endphp
                                        <span class="badge rounded-pill" style="background-color: {{ $pColor }}; color: {{ $log->priority === 'medium' ? '#212529' : '#fff' }};">
                                            {{ ucfirst($log->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('maintenance.show', $log->id) }}" class="text-decoration-none text-dark fw-medium">
                                            {{ Str::limit($log->nature_of_complaint, 50) }}
                                        </a>
                                    </td>
                                    <td class="small">{{ $log->lodged_by }}</td>
                                    <td>
                                        @php
                                            $statusColors = ['new' => '#ffc107', 'in_progress' => '#0d6efd', 'completed' => '#198754', 'cancelled' => '#6c757d'];
                                            $statusIcons = ['new' => 'fa-exclamation-circle', 'in_progress' => 'fa-sync-alt', 'completed' => 'fa-check-circle', 'cancelled' => 'fa-times-circle'];
                                            $statusLabels = ['new' => 'New', 'in_progress' => 'Doing', 'completed' => 'Done', 'cancelled' => 'Cancel'];
                                        @endphp
                                        <div class="dropdown status-dropdown">
                                            <button class="btn btn-sm rounded-pill dropdown-toggle status-badge" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                style="background-color: {{ $statusColors[$log->status] }}; color: {{ $log->status === 'new' ? '#212529' : '#fff' }}; border: none;">
                                                <i class="fas {{ $statusIcons[$log->status] }} me-1"></i>
                                                {{ $statusLabels[$log->status] }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                @foreach (['new' => ['New', '#ffc107'], 'in_progress' => ['Doing', '#0d6efd'], 'completed' => ['Done', '#198754'], 'cancelled' => ['Cancel', '#6c757d']] as $st => $info)
                                                    <li>
                                                        <form action="{{ route('maintenance.toggle-status', $log->id) }}" method="POST" class="status-toggle-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $st }}">
                                                            <button type="submit" class="dropdown-item small {{ $log->status === $st ? 'active' : '' }}"
                                                                style="{{ $log->status === $st ? 'background-color: ' . $info[1] . '; color: ' . ($st === 'new' ? '#212529' : '#fff') : '' }};"
                                                                {{ $log->status === $st ? 'disabled' : '' }}>
                                                                <i class="fas {{ $statusIcons[$st] }} me-2" style="color: {{ $info[1] }};"></i>
                                                                {{ $info[0] }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $log->complaint_datetime->format('M d, Y') }}</td>
                                    <td class="text-nowrap text-end font-monospace">{{ $log->cost_of_fixing ? number_format($log->cost_of_fixing, 2) : '--' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('maintenance.show', $log->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('access_maintenance_dashboard')
                                                <a href="{{ route('maintenance.edit', $log->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $log->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Maintenance Logs Yet</h5>
                    <p class="text-muted">Create the first maintenance log to get started.</p>
                    <a href="{{ route('maintenance.create') }}" class="btn btn-gold">
                        <i class="fas fa-plus me-1"></i> Create First Log
                    </a>
                </div>
            @endif
        </div>
    </div>
{{-- Quick Report Modal --}}
<div class="modal fade" id="quickReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background: #C8A165; color: #fff;">
                <h5 class="modal-title fw-bold"><i class="fas fa-bolt me-2"></i>Quick Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('maintenance.quick-store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Name <span class="text-danger">*</span></label>
                        <input type="text" name="lodged_by" class="form-control"
                               value="{{ old('lodged_by', Auth::user()->name) }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Room 204, Lobby" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select</option>
                                @foreach (\Modules\Maintenance\Models\MaintenanceLog::DEPARTMENTS as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'Maintenance' ? 'selected' : '' }}>{{ $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <div class="d-flex gap-2 priority-radio">
                            @foreach (\Modules\Maintenance\Models\MaintenanceLog::PRIORITIES as $key => $label)
                                <label class="priority-option {{ $key === 'medium' ? 'selected' : '' }}">
                                    <input type="radio" name="priority" value="{{ $key }}" {{ $key === 'medium' ? 'checked' : '' }}>
                                    <span class="badge priority-badge p-2" data-priority="{{ $key }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="nature_of_complaint" class="form-control" rows="3"
                                  placeholder="Briefly describe the issue..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Photo <span class="text-muted small fw-normal">(optional)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" capture="environment" data-compress="1200">
                    </div>
                    <input type="hidden" name="complaint_datetime" value="{{ now()->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="received_by" value="{{ Auth::user()->name }}">
                    <input type="hidden" name="status" value="new">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-gold w-100" id="quickReportSubmit">
                        <i class="fas fa-paper-plane me-1"></i> Submit Report
                    </button>
                </div>
                <div class="modal-loading d-none" id="quickReportOverlay">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-light mb-2" style="width: 3rem; height: 3rem;" role="status"></div>
                            <p class="text-light mb-0 fw-semibold">Submitting report...</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endSection

@section('page-scripts')
<script>
    $(document).ready(function () {
        $('.status-toggle-form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var dropdown = form.closest('.dropdown-menu');
            var btn = dropdown.closest('.status-dropdown').find('.dropdown-toggle');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        var colors = {'new': '#ffc107', 'in_progress': '#0d6efd', 'completed': '#198754', 'cancelled': '#6c757d'};
                        var icons = {'new': 'fa-exclamation-circle', 'in_progress': 'fa-sync-alt', 'completed': 'fa-check-circle', 'cancelled': 'fa-times-circle'};
                        var labels = {'new': 'New', 'in_progress': 'Doing', 'completed': 'Done', 'cancelled': 'Cancel'};
                        var s = res.status;
                        btn.css('background-color', colors[s]);
                        btn.css('color', s === 'new' ? '#212529' : '#fff');
                        btn.html('<i class="fas ' + icons[s] + ' me-1"></i> ' + labels[s]);
                        form.closest('tr').css('border-left-color', colors[s]).attr('data-status', s);
                        dropdown.find('.dropdown-item').each(function () {
                            var st = $(this).closest('form').find('input[name="status"]').val();
                            $(this).removeClass('active').prop('disabled', false).css({'background-color': '', 'color': ''});
                            if (st === s) {
                                $(this).addClass('active').prop('disabled', true).css({'background-color': colors[s], 'color': s === 'new' ? '#212529' : '#fff'});
                            }
                        });
                    }
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });

        var table = $('#logsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            order: [[0, 'desc']],
            language: { search: "Search logs:" },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            columnDefs: [
                { targets: [9], orderable: false }
            ]
        });

        $('#statusFilters .status-filter').click(function () {
            $('#statusFilters .status-filter').removeClass('active');
            $(this).addClass('active');
            var status = $(this).data('status');
            $.fn.dataTable.ext.search.pop();
            if (status !== '') {
                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).data('status') === status;
                });
            }
            table.draw();
        });
    });
</script>
@endsection

@section('styles')
<style>
    .card { border-radius: 10px; }
    .card-header { border-bottom: 2px solid #f0f0f0; }
    table.dataTable thead th { border-bottom: 2px solid #f0f0f0 !important; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
    table.dataTable tbody tr { transition: background-color 0.15s ease, box-shadow 0.15s ease; }
    table.dataTable tbody tr:hover { background-color: #f8f9fa !important; }
    .dataTables_wrapper .dataTables_info { padding-left: 0; font-size: 0.85rem; }
    .dataTables_wrapper .dataTables_paginate { padding-right: 0; }
    .dataTables_filter input { border: 1px solid #ddd; border-radius: 8px; padding: 5px 12px; font-size: 0.9rem; }
    .dataTables_filter input:focus { border-color: #C8A165; outline: none; box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.15); }
    .dataTables_length select { border: 1px solid #ddd; border-radius: 8px; padding: 4px 28px 4px 10px; appearance: auto; }
    #statusFilters .status-filter { border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; font-weight: 500; border: 1px solid #dee2e6; background: #fff; color: #666; transition: all 0.15s; }
    #statusFilters .status-filter:hover { border-color: #C8A165; color: #C8A165; }
    #statusFilters .status-filter.active { background: #C8A165; border-color: #C8A165; color: #fff; }
    .status-dropdown .dropdown-toggle { font-size: 0.78rem; padding: 3px 12px; }
    .status-dropdown .dropdown-menu { min-width: 140px; border-radius: 10px; padding: 6px; }
    .status-dropdown .dropdown-item { border-radius: 6px; padding: 6px 10px; font-size: 0.82rem; }
    .status-dropdown .dropdown-item:hover { background-color: #f5f5f5; }
    .priority-radio { gap: 0.35rem !important; }
    .priority-option { cursor: pointer; }
    .priority-option input { display: none; }
    .priority-badge { transition: all 0.15s; border: 2px solid transparent; font-weight: 500; }
    .priority-option input:checked + .priority-badge { border-color: #212529; box-shadow: 0 0 0 2px rgba(200,161,101,0.4); }
    .priority-option:hover .priority-badge { filter: brightness(0.92); }
    .priority-badge[data-priority="low"] { background-color: #6c757d; color: #fff; }
    .priority-badge[data-priority="medium"] { background-color: #ffc107; color: #212529; }
    .priority-badge[data-priority="high"] { background-color: #fd7e14; color: #fff; }
    .priority-badge[data-priority="critical"] { background-color: #dc3545; color: #fff; }
    .modal-loading { position: absolute; inset: 0; background: rgba(0,0,0,0.6); border-radius: inherit; z-index: 10; }
</style>
@endsection
