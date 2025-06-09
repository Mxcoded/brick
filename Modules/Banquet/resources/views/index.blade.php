@extends('staff::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Event Orders Tracker</li>
@endsection

@section('page-content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold display-5 text-primary">
                <i class="fas fa-utensils me-3"></i>Event Orders
            </h1>
            <div>
                <a href="{{ route('banquet.orders.report.form') }}" class="btn btn-primary me-2">
                    <i class="fas fa-file-pdf me-2"></i>Generate Report
                </a>
                <a href="{{ route('banquet.orders.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>Create New Order
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text" id="customSearch" class="form-control" placeholder="Search orders...">
                        </div>
                        <select id="statusFilter" class="form-select" style="width: 150px;">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-muted" id="resultCount"></div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-hover" id="ordersTable">
                        <thead class="bg-light">
                            <tr>
                                <th>S/N</th>
                                <th>Order ID</th>
                                <th>Client</th>
                                <th>Organization</th> <!-- New column -->
                                <th>Event Dates</th>
                                <th>Total Guests</th>
                                <th>Expenses (₦)</th>
                                <th>Revenue (₦)</th>
                                <th>Profit Margin</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    #ordersTable th,
    #ordersTable td {
        text-transform: uppercase;
        /* Optional for consistency */
        white-space: nowrap;
    }
</style>
@section('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('banquet.orders.datatable') }}",
                    type: "GET",
                    error: function(xhr, error, thrown) {
                        console.log('AJAX Error:', error, thrown);
                        alert('Failed to load data. Check console.');
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false
                    },
                    {
                        data: 'order_id',
                        name: 'order_id'
                    },
                    {
                        data: 'customer',
                        name: 'customer',
                        render: function(data) {
                            return data ? (data.name || data.contact_person_name || 'N/A') : 'N/A';
                        }
                    },
                    {
                        data: 'organization',
                        name: 'organization',
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: 'event_dates',
                        name: 'event_dates',
                        render: function(data) {
                            return data ? `<span class="badge bg-dark">${data}</span>` : 'N/A';
                        }
                    },
                    {
                        data: 'total_guests',
                        name: 'total_guests'
                    },
                    {
                        data: 'expenses',
                        name: 'expenses',
                        render: function(data) {
                            return data !== null ?
                                `₦${Number(data).toLocaleString('en-US', {minimumFractionDigits: 2})}` :
                                '₦0.00';
                        }
                    },
                    {
                        data: 'total_revenue',
                        name: 'total_revenue',
                        render: function(data) {
                            return data !== null ?
                                `₦${Number(data).toLocaleString('en-US', {minimumFractionDigits: 2})}` :
                                '₦0.00';
                        }
                    },
                    {
                        data: 'profit_margin',
                        name: 'profit_margin',
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            const statusColors = {
                                'Pending': 'warning',
                                'Confirmed': 'primary',
                                'Completed': 'success',
                                'Cancelled': 'danger'
                            };
                            return data ?
                                `<span class="badge bg-${statusColors[data] || 'secondary'}">${data}</span>` :
                                'N/A';
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        render: function(data) {
                            return `
                        <div class="d-flex gap-2">
                            <a href="${data.view}" class="btn btn-sm btn-outline-primary" title="Show Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('manage-banquet')
                            <a href="${data.edit}" class="btn btn-sm btn-outline-warning" title="Update Data">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="${data.pdf}" class="btn btn-sm btn-outline-success" title="Generate Function Sheet PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger delete-order" data-order-id="${data.order_id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                        </div>
                    `;
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-2"></i>Export',
                    className: 'btn btn-success'
                }],
                language: {
                    search: "",
                    searchPlaceholder: "Search orders...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                },
                initComplete: function() {
                    $('#customSearch').on('keyup', function() {
                        table.search(this.value).draw();
                    });
                    $('#statusFilter').on('change', function() {
                        table.column(8).search(this.value).draw(); // Update column index
                    });
                    this.api().on('draw', function() {
                        const count = table.page.info().recordsDisplay;
                        $('#resultCount').html(
                            `Found ${count} ${count === 1 ? 'result' : 'results'}`);
                    });
                }
            });

            // Delete functionality
            $(document).on('click', '.delete-order', function() {
                const orderId = $(this).data('order-id');
                if (confirm(`Are you sure you want to delete order ${orderId}?`)) {
                    $.ajax({
                        url: `/banquet-orders/${orderId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                alert('Order deleted successfully.');
                            } else {
                                alert('Failed to delete order: ' + (response.message ||
                                    'Unknown error'));
                            }
                        },
                        error: function(xhr) {
                            alert('Failed to delete order. Check console for details.');
                            console.log('Error:', xhr.responseText);
                        }
                    });
                }
            });

            // Debugging
            table.on('xhr', function() {
                console.log('AJAX Response:', table.ajax.json());
            });
            table.on('draw', function() {
                console.log('Table Drawn with', table.data().count(), 'rows');
            });
        });
    </script>
@endsection
