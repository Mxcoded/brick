@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active">Enquiries</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-question-circle me-2 text-gold"></i>Meeting Enquiries
            </h1>
            <p class="text-muted mb-0">Manage public quote requests and meeting enquiries</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gold text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Enquiries</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <i class="fas fa-question-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Pending</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['pending']) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Contacted</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['contacted']) }}</h3>
                        </div>
                        <i class="fas fa-phone-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Converted</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['converted']) }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-search text-muted me-2"></i>
                        <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Search enquiries...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Converted">Converted</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-5 text-md-end">
                    <small class="text-muted" id="resultCount">Loading records...</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="enquiriesTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Contact</th>
                            <th>Event Type</th>
                            <th>Event Date</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th style="width:80px" class="text-end">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.banquet-theme .bg-gold { background-color: #C8A165 !important; }
.banquet-theme .table > tbody > tr:hover { background-color: #FFFBF5; }
.banquet-theme .table > tbody > tr { transition: background-color .15s; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $(document).on('click', '.delete-enquiry', function () {
        const url = $(this).data('url');
        if (confirm('Are you sure you want to delete this enquiry?')) {
            $('<form>', { method: 'POST', action: url }).append(
                '@csrf', '@method("DELETE")'
            ).appendTo('body').submit();
        }
    });

    const table = $('#enquiriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('banquet.enquiries.datatable') }}",
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false },
            { data: 'name', name: 'name',
              render: (data, type, row) => `<div class="fw-bold">${data}</div><small class="text-muted">${row.email}</small>` },
            { data: 'event_type', name: 'event_type' },
            { data: 'event_date_formatted', name: 'event_date' },
            { data: 'guest_count', name: 'guest_count',
              render: data => `<span class="badge bg-light text-dark rounded-pill">${data} pax</span>` },
            { data: 'status_badge', name: 'status' },
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        dom: 'tp',
        language: { emptyTable: "No enquiries found" },
        initComplete: function () {
            $('#customSearch').on('keyup', function () { table.search(this.value).draw(); });
            $('#statusFilter').on('change', function () { table.column(5).search(this.value).draw(); });
            this.api().on('draw', function () {
                $('#resultCount').text(`Showing ${table.page.info().recordsDisplay} records`);
            });
        }
    });
});
</script>
@endsection
