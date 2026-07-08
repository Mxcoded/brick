@extends('layouts.master')

@section('title', 'Pre-Arrival Dashboard')

@section('page-content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-light p-2 me-3">
                    <i class="fas fa-plane-arrival fa-lg text-gold"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-dark fw-bold">Digital Guest Journey</h3>
                    <p class="text-muted mb-0">Manage pre-arrival checklists and guest documents</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2"></i>Pending Pre-Arrivals</h5>
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="customSearch" class="form-control border-start-0 ps-0" placeholder="Search guests...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="preArrivalsTable" class="table table-hover align-middle" width="100%">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>Guest Name</th>
                            <th>Room Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Arrival Time</th>
                            <th class="text-center">Documents</th>
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

@push('page-scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    const table = $('#preArrivalsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('frontdesk.pre-arrivals.datatable') }}",
        columns: [
            { data: 'guest_name' },
            { data: 'room_type' },
            { data: 'check_in' },
            { data: 'check_out' },
            { data: 'arrival_time' },
            { data: 'documents', className: 'text-center' },
            { data: 'status_badge' },
            { data: 'actions', orderable: false, className: 'text-end' }
        ],
        dom: 'tp',
        pageLength: 25,
        language: { emptyTable: "No pre-arrivals found" },
        initComplete: function() {
            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
        }
    });
});
</script>
@endpush
