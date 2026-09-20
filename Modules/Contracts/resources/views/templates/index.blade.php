@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item active" aria-current="page">Templates</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">

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

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-0">Agreement Templates</h4>
            <span class="text-muted small">Reusable clause sets. New agreements can start from a template.</span>
        </div>
        <a href="{{ route('contracts.templates.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i>New Template</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="input-group mb-3" style="max-width: 320px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="templateSearch" class="form-control" placeholder="Search templates...">
            </div>
            <div class="table-responsive">
                <table id="templatesTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Clauses</th>
                            <th>Status</th>
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
@parent
<style>
    .contracts-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
</style>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    const table = $('#templatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('contracts.templates.datatable') }}",
        columns: [
            { data: 'name', render: (d, t, r) => `<a href="{{ route('contracts.templates.index') }}/${r.id}" class="fw-semibold text-decoration-none">${d}</a>` },
            { data: 'type_label', searchable: false },
            { data: 'description', render: d => d ?? '—' },
            { data: 'clause_count', searchable: false, className: 'text-center' },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        dom: 'tp',
        pageLength: 25,
        order: [[0, 'asc']],
        language: { emptyTable: "No templates yet — create one to speed up agreement drafting." },
        initComplete: function () {
            $('#templateSearch').on('keyup', function () { table.search(this.value).draw(); });
        }
    });
});
</script>
@endsection