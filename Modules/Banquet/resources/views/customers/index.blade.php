@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    
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
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-users me-2 text-gold"></i>Customer Directory
            </h1>
            <p class="text-muted mb-0">Manage banquet customers and organizations</p>
        </div>
        <a href="{{ route('banquet.customers.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-2"></i>Add Customer
        </a>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gold text-white stat-card" data-filter="all" role="button" style="cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Customers</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_customers']) }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card" data-filter="organizations" role="button" style="cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Organizations</h6>
                            <h3 class="fw-bold mb-0 text-charcoal">{{ number_format($stats['organizations']) }}</h3>
                        </div>
                        <i class="fas fa-building fa-2x text-gold opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card" data-filter="repeat" role="button" style="cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Repeat Customers</h6>
                            <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['repeat_customers']) }}</h3>
                        </div>
                        <i class="fas fa-redo fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card" data-filter="new_this_month" role="button" style="cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">New This Month</h6>
                            <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['new_this_month']) }}</h3>
                        </div>
                        <i class="fas fa-user-plus fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer List --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold text-gold"><i class="fas fa-list me-2"></i>Customer List</h5>
                    <span id="activeFilter" class="badge bg-secondary d-none"></span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="customSearch" class="form-control border-start-0 ps-0" placeholder="Search customers...">
                    </div>
                    <a href="{{ route('banquet.customers.export') }}" id="exportBtn" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Export
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="customersTable" class="table table-hover align-middle" width="100%">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Organization</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Spent</th>
                            <th>Since</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
    .stat-card.active { outline: 3px solid #C8A165; outline-offset: 2px; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let currentFilter = 'all';
    
    const table = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('banquet.customers.datatable') }}",
            data: function(d) {
                d.filter = currentFilter;
            }
        },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false },
            { data: 'name', render: data => `<span class="fw-bold text-charcoal">${data}</span>` },
            { data: 'email' },
            { data: 'phone' },
            { data: 'organization_display', searchable: false },
            { data: 'total_orders', className: 'text-center', searchable: false },
            { data: 'total_spent', className: 'text-end fw-bold', searchable: false },
            { data: 'created_at_formatted', searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        dom: 'tp',
        pageLength: 25,
        language: { emptyTable: "No customers found" },
        initComplete: function() {
            $('#customSearch').on('keyup', function() { 
                table.search(this.value).draw(); 
            });
        }
    });
    
    // Stat card click handlers
    $('.stat-card').on('click', function() {
        const filter = $(this).data('filter');
        currentFilter = filter;
        
        // Update active state
        $('.stat-card').removeClass('active');
        $(this).addClass('active');
        
        // Update filter badge
        const filterLabels = {
            'all': 'All Customers',
            'organizations': 'With Organization',
            'repeat': 'Repeat Customers',
            'new_this_month': 'New This Month'
        };
        
        if (filter === 'all') {
            $('#activeFilter').addClass('d-none').text('');
        } else {
            $('#activeFilter').removeClass('d-none').html(`<i class="fas fa-filter me-1"></i>${filterLabels[filter]} <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.6rem;" id="clearFilter"></button>`);
        }
        
        // Update export link with filter
        const exportUrl = new URL("{{ route('banquet.customers.export') }}", window.location.origin);
        exportUrl.searchParams.set('filter', filter);
        $('#exportBtn').attr('href', exportUrl.toString());
        
        // Reload table with new filter
        table.ajax.reload();
    });
    
    // Clear filter handler
    $(document).on('click', '#clearFilter', function(e) {
        e.stopPropagation();
        currentFilter = 'all';
        $('.stat-card').removeClass('active');
        $('.stat-card[data-filter="all"]').addClass('active');
        $('#activeFilter').addClass('d-none').text('');
        $('#exportBtn').attr('href', "{{ route('banquet.customers.export') }}");
        table.ajax.reload();
    });
    
    // Set initial active state
    $('.stat-card[data-filter="all"]').addClass('active');
});
</script>
@endsection
