@extends('layouts.master')

@section('title', 'Inventory Dashboard')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Invoice</h1>
        <p class="lead text-muted">Lists of all the invoices across all stores.</p>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('inventory.items.create') }}" class="btn btn-primary me-2 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Add New Item
            </a>
            <a href="{{ route('inventory.transfers.index') }}" class="btn btn-info shadow-sm">
                <i class="fas fa-exchange-alt me-2"></i>Transfer Items
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Current Stock Overview</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                     <table class="table table-striped table-hover" id="inventoryTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">order_number</th>
                                <th scope="col">Module</th>
                                <th scope="col">Module Order ID</th>
                                <th scope="col">Tax</th>
                                <th scope="col">Discount</th>
                                <th scope="col">Sub Total</th>
                                <th scope="col">Total</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
\                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->module }}</td>
                                    <td>{{ $order->module_order_id }}</td>
                                    <td>₦{{ number_format($order->tax, 2) }}</td>
                                    <td>₦{{ number_format($order->discount, 2) }}</td>
                                    <td>₦{{ number_format($order->sub_total, 2) }}</td>
                                    <td>₦{{ number_format($order->total, 2) }}</td>
                                    <td>{{ $order->status }}</td>
                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success me-2 restock-btn" data-bs-toggle="modal" data-bs-target="#restockModal" data-item-id="{{ $order->id }}" data-item-description="{{ $order->order_number }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <a href="{{ route('account.order.show', $order->id) }}" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-eye"></i></a>
                                        <form action="{{ route('account.order.destroy', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                "pageLength": 10
            });

            // Handle the opening of the restock modal
            $('#inventoryTable').on('click', '.restock-btn', function() {
                const itemId = $(this).data('item-id');
                const itemDescription = $(this).data('item-description');

                $('#restockItemId').val(itemId);
                $('#restockItemName').text(itemDescription);
                $('#restockForm')[0].reset();
                $('#restockAlertContainer').html('');
            });

            // Handle restock form submission
            $('#restockForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const alertContainer = $('#restockAlertContainer');

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        alertContainer.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        form.trigger('reset');
                        // Optional: Reload the page or update the table via AJAX
                        setTimeout(() => {
                             window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Oops! There were some errors.</strong><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        alertContainer.html(errorHtml);
                    }
                });
            });
        });
    </script>
@endsection