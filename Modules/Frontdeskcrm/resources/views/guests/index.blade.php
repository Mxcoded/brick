@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item active">Guest Directory</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fas fa-address-book me-2 text-primary"></i>Guest Directory
            </h1>
            <p class="text-muted mb-0">Manage hotel guest profiles</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.guests.import') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-import me-2"></i>Import Guests
            </a>
            <a href="{{ route('frontdesk.guests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Guest
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Guests</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_guests']) }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Recent Visitors</h6>
                            <h3 class="fw-bold mb-0 text-info">{{ number_format($stats['recent_visitors']) }}</h3>
                            <small class="text-muted">Last 30 days</small>
                        </div>
                        <i class="fas fa-calendar-check fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Returning Guests</h6>
                            <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['returning_guests']) }}</h3>
                        </div>
                        <i class="fas fa-redo fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">New This Month</h6>
                            <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['new_this_month']) }}</h3>
                        </div>
                        <i class="fas fa-user-plus fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Guest List --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Guest List</h5>
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="customSearch" class="form-control border-start-0 ps-0" placeholder="Search guests...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="guestsTable" class="table table-hover align-middle" width="100%">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Nationality</th>
                            <th class="text-center">Visits</th>
                            <th>Last Visit</th>
                            <th>Registered</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    const table = $('#guestsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('frontdesk.guests.datatable') }}",
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false },
            { data: 'name_display' },
            { data: 'contact_display' },
            { data: 'nationality', defaultContent: '<span class="text-muted">-</span>' },
            { data: 'visit_count_display', className: 'text-center' },
            { data: 'last_visit_formatted' },
            { data: 'created_at_formatted' },
            { data: 'actions', orderable: false, className: 'text-end' }
        ],
        dom: 'tp',
        pageLength: 25,
        language: { emptyTable: "No guests found" },
        initComplete: function() {
            $('#customSearch').on('keyup', function() { 
                table.search(this.value).draw(); 
            });
        }
    });
});
</script>
@endsection
