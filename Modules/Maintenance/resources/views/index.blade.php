@extends('maintenance::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Maintenance Tracker</li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold text-gradient">🏗️ Maintenance Tracker</h1>
            <a href="{{ route('maintenance.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> New Log
            </a>
        </div>

        @if (session('success'))
            <div class="toast-alert position-fixed top-20 end-0 p-3">
                <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-transparent py-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                            <input type="search" id="customSearch" class="form-control border-0 bg-light"
                                placeholder="Search logs...">
                        </div>
                    </div>
                    <div class="col-md-8 d-flex justify-content-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-light filter-btn active" data-status="all">All</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="New">New</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="In Progress">In
                                Progress</button>
                            <button type="button" class="btn btn-light filter-btn"
                                data-status="Completed">Completed</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="maintenanceTable" class="table table-hover align-middle mb-0">
                        <thead class="bg-light-100">
                            <tr>
                                <th class="ps-4">Location</th>
                                <th>Complaint Date</th>
                                <th>Nature of Complaint</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr class="hover-scale">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                            {{ $log->location }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-calendar-day me-2"></i>
                                            {{ $log->complaint_datetime->format('M d, Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        {{ $log->nature_of_complaint }}
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'new' => ['color' => 'primary', 'icon' => 'clock'],
                                                'in_progress' => ['color' => 'warning', 'icon' => 'tools'],
                                                'completed' => ['color' => 'success', 'icon' => 'check-circle'],
                                            ][$log->status];
                                        @endphp
                                        <span
                                            class="badge rounded-pill bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}">
                                            <i class="fas fa-{{ $statusConfig['icon'] }} me-2"></i>
                                            {{ ucwords(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $log->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm shadow-sm">
                                            <a href="{{ route('maintenance.show', $log->id) }}"
                                                class="btn btn-light border" data-bs-toggle="tooltip" title="View">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="{{ route('maintenance.edit', $log->id) }}"
                                                class="btn btn-light border" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                            <form action="{{ route('maintenance.destroy', $log->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light border" data-bs-toggle="tooltip"
                                                    title="Delete" onclick="return confirmAction('delete')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <h4 class="fw-bold">No maintenance logs found</h4>
                                            <p class="text-muted">Start by creating a new maintenance log</p>
                                        </div>
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

@section('scripts')
    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#maintenanceTable').DataTable({
                dom: '<"top"f>rt<"bottom"ip><"clear">',
                language: {
                    search: '',
                    searchPlaceholder: "Search logs..."
                },
                columnDefs: [{
                    orderable: false,
                    targets: [5]
                }],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                }
            });

            // Custom search input
            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Status filtering
            $('.filter-btn').click(function() {
                const status = $(this).data('status');
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                table.column(3).search(status === 'all' ? '' : status).draw();
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Auto-hide success message
            setTimeout(function() {
                $('.toast-alert').fadeOut('slow');
            }, 3000);
        });

        function confirmAction(type) {
            return confirm(`Are you sure you want to ${type} this log?`);
        }
    </script>
@endsection
