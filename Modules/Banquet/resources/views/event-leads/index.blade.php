@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active">Event Leads</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-users me-2 text-gold"></i>Event Leads
            </h1>
            <p class="text-muted mb-0">Contacts captured from public event interest forms</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banquet.lead-events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-calendar-alt me-1"></i> Manage Events
            </a>
            <a href="{{ route('banquet.event-leads.export', request()->query()) }}" class="btn btn-outline-gold">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cleanDuplicatesModal">
                <i class="fas fa-broom me-1"></i> Clean Duplicates
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gold text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Leads</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">New</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['new']) }}</h3>
                        </div>
                        <i class="fas fa-star fa-2x opacity-50"></i>
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
                        <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Search leads...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select id="eventFilter" class="form-select form-select-sm">
                        <option value="">All Events</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev->id }}" {{ request('event_id') == $ev->id ? 'selected' : '' }}>
                                {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="New">New</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Converted">Converted</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <small class="text-muted" id="resultCount">Loading records...</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="leadsTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Contact</th>
                            <th>Event</th>
                            <th>Company</th>
                            <th>Source</th>
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

<div class="modal fade" id="cleanDuplicatesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('banquet.event-leads.clean-duplicates') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-broom text-danger me-2"></i>Clean Duplicate Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>This will remove duplicate leads where the <strong>same email</strong> was submitted for the <strong>same event</strong>, keeping only the earliest submission.</p>
                    <p class="mb-0 text-muted small"><i class="fas fa-info-circle me-1"></i>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-broom me-1"></i>Clean Duplicates</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.banquet-theme .bg-gold { background-color: #C8A165 !important; }
.banquet-theme .table > tbody > tr:hover { background-color: #FFFBF5; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $(document).on('click', '.delete-lead', function () {
        const url = $(this).data('url');
        if (confirm('Delete this lead?')) {
            $('<form>', { method: 'POST', action: url }).append(
                '@csrf', '@method("DELETE")'
            ).appendTo('body').submit();
        }
    });

    const eventId = '{{ request('event_id') }}';
    const table = $('#leadsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('banquet.event-leads.datatable') }}",
            data: function (d) {
                d.event_id = $('#eventFilter').val() || eventId;
            }
        },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false },
            { data: 'name', name: 'name',
              render: (data, type, row) => `<div class="fw-bold">${data}</div><small class="text-muted">${row.email}</small>` },
            { data: 'event_name', name: 'event_name' },
            { data: 'company', name: 'company',
              render: data => data ? data : '<span class="text-muted">—</span>' },
            { data: 'source', name: 'source',
              render: data => data ? data : '<span class="text-muted">—</span>' },
            { data: 'status_badge', name: 'status' },
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        dom: 'tp',
        language: { emptyTable: "No leads yet" },
        initComplete: function () {
            $('#customSearch').on('keyup', function () { table.search(this.value).draw(); });
            $('#eventFilter').on('change', function () {
                table.ajax.reload();
            });
            $('#statusFilter').on('change', function () { table.column(5).search(this.value).draw(); });
            this.api().on('draw', function () {
                $('#resultCount').text(`Showing ${table.page.info().recordsDisplay} records`);
            });
        }
    });
});
</script>
@endsection
