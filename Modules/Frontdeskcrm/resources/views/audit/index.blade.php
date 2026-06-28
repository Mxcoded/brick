@extends('layouts.master')

@section('title', 'Night Audit History')

@push('page-styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-moon me-2"></i>Night Audit</h4>
            <p class="text-muted mb-0">End-of-day procedure, revenue closing, and auto-posting of daily room charges</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.audit.create') }}" class="btn btn-primary fw-bold">
                <i class="fas fa-play-circle me-1"></i> Run Night Audit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="auditTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Checked In</th>
                            <th>Occupancy</th>
                            <th>Revenue</th>
                            <th>Posted</th>
                            <th>Run By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $('#auditTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('frontdesk.audit.index') }}',
        order: [[0, 'desc']],
        columns: [
            { data: 'date', name: 'audit_date' },
            { data: 'status_badge', name: 'status' },
            { data: 'checked_in_count', name: 'checked_in_count' },
            { data: 'occupancy', name: 'occupancy' },
            { data: 'revenue', name: 'revenue' },
            { data: 'charges_posted', name: 'charges_posted' },
            { data: 'starter.name', name: 'starter.name', defaultContent: '—' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
    });
</script>
@endpush
