@extends('maintenance::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Maintenance Tracker</li>
@endsection

@push('styles')
<style>
    /* Mobile responsive styles */
    @media (max-width: 767.98px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        .page-header h1 {
            font-size: 1.5rem;
        }
        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-buttons {
            flex-wrap: wrap;
            justify-content: center !important;
        }
        .filter-buttons .btn-group {
            flex-wrap: wrap;
        }
        .filter-buttons .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.5rem;
        }
        .search-box {
            width: 100%;
        }
        /* Mobile card view */
        .mobile-card-view {
            display: block;
        }
        .desktop-table-view {
            display: none !important;
        }
        .maintenance-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .maintenance-card .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        .maintenance-card .location {
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }
        .maintenance-card .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-top: 1px solid #f0f0f0;
            font-size: 0.875rem;
        }
        .maintenance-card .detail-label {
            color: #6c757d;
        }
        .maintenance-card .detail-value {
            font-weight: 500;
            text-align: right;
            max-width: 60%;
        }
        .maintenance-card .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e9ecef;
        }
        .maintenance-card .card-actions .btn {
            flex: 1;
        }
    }
    @media (min-width: 768px) {
        .mobile-card-view {
            display: none;
        }
        .desktop-table-view {
            display: block;
        }
    }
</style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4 page-header">
            <h1 class="fw-bold text-gradient">🏗️ Maintenance Tracker</h1>
            <div class="header-actions">
                <a href="{{ route('maintenance.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> New Log
                </a>
                @auth
                <a href="{{route('home')}}" class="btn btn-warning rounded-pill shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Back Home
                </a>
                @endauth
            </div>
        </div>

        @if (session('success'))
            <div class="toast-alert position-fixed top-20 end-0 p-3" style="z-index: 1050;">
                <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-transparent py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4 search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                            <input type="search" id="customSearch" class="form-control border-0 bg-light"
                                placeholder="Search logs...">
                        </div>
                    </div>
                    <div class="col-12 col-md-8 d-flex justify-content-md-end filter-buttons">
                        <div class="btn-group flex-wrap" role="group">
                            <button type="button" class="btn btn-light filter-btn active" data-status="all">All</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="New">New</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="In Progress">In Progress</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="Completed">Completed</button>
                            <button type="button" class="btn btn-light filter-btn" data-status="Cancelled">Cancelled</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- Mobile Card View --}}
                <div class="mobile-card-view p-3">
                    @forelse ($logs as $log)
                        @php
                            $statusConfig = [
                                'new' => ['color' => 'primary', 'icon' => 'clock'],
                                'in_progress' => ['color' => 'warning', 'icon' => 'tools'],
                                'completed' => ['color' => 'success', 'icon' => 'check-circle'],
                                'cancelled' => ['color' => 'danger', 'icon' => 'times-circle'],
                            ][$log->status] ?? ['color' => 'secondary', 'icon' => 'question-circle'];
                        @endphp
                        <div class="maintenance-card" data-status="{{ ucwords(str_replace('_', ' ', $log->status)) }}">
                            <div class="card-header-row">
                                <div class="location">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    {{ $log->location }}
                                </div>
                                <span class="badge rounded-pill bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}">
                                    <i class="fas fa-{{ $statusConfig['icon'] }} me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Complaint</span>
                                <span class="detail-value text-truncate">{{ Str::limit($log->nature_of_complaint, 40) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date</span>
                                <span class="detail-value">{{ $log->complaint_datetime->format('M d, Y') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Updated</span>
                                <span class="detail-value">{{ $log->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('maintenance.show', $log->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                                <a href="{{ route('maintenance.edit', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                @can('delete-maintenance-log')
                                <form action="{{ route('maintenance.destroy', $log->id) }}" method="POST" class="d-inline flex-grow-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirmAction('delete')">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h4 class="fw-bold">No maintenance logs found</h4>
                                <p class="text-muted">Start by creating a new maintenance log</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View --}}
                <div class="table-responsive desktop-table-view">
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
                                                'cancelled' => ['color' => 'danger', 'icon' => 'times-circle'],
                                            ][$log->status] ?? ['color' => 'secondary', 'icon' => 'question-circle'];
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
                                                class="btn btn-light border" data-bs-toggle="tooltip" title="Show Detail">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="{{ route('maintenance.edit', $log->id) }}"
                                                class="btn btn-light border" data-bs-toggle="tooltip" title="Update Log">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                            @can('delete-maintenance-log')
                                            <form action="{{ route('maintenance.destroy', $log->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light border" data-bs-toggle="tooltip"
                                                    title="Delete" onclick="return confirmAction('delete')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
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
            // Only initialize DataTables on desktop
            let table = null;
            if (window.innerWidth >= 768) {
                table = $('#maintenanceTable').DataTable({
                    dom: '<"top"f>rt<"bottom"ip><"clear">',
                    order: [[1, 'desc']], // Sort by complaint date descending (newest first)
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
            }

            // Custom search input - works for both views
            $('#customSearch').on('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                
                if (table) {
                    table.search(this.value).draw();
                }
                
                // Also filter mobile cards
                $('.maintenance-card').each(function() {
                    const cardText = $(this).text().toLowerCase();
                    $(this).toggle(cardText.includes(searchTerm));
                });
            });

            // Status filtering - works for both views
            $('.filter-btn').click(function() {
                const status = $(this).data('status');
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                if (table) {
                    table.column(3).search(status === 'all' ? '' : status).draw();
                }
                
                // Also filter mobile cards
                $('.maintenance-card').each(function() {
                    if (status === 'all') {
                        $(this).show();
                    } else {
                        const cardStatus = $(this).data('status');
                        $(this).toggle(cardStatus === status);
                    }
                });
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
