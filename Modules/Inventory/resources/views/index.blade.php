@extends('layouts.master')

@section('title', 'Inventory Dashboard')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Inventory Dashboard</h1>
        <p class="lead text-muted">Manage your stock, track transfers, and monitor usage across all stores.</p>

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
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Supplier</th>
                                <th scope="col">Total Quantity</th>
                                <th scope="col">Total Cost</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Price</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->category ?? 'N/A' }}</td>
                                    <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $item->storeItems->sum('quantity') }}</td>
                                    <td>₦{{ number_format($item->storeItems->sum('total_cost'), 2) }}</td>
                                    <td>{{ $item->unit_of_measurement ?? 'N/A' }}</td>
                                    <td>₦{{ number_format($item->price, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success me-2 restock-btn" data-bs-toggle="modal" data-bs-target="#restockModal" data-item-id="{{ $item->id }}" data-item-description="{{ $item->description }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('inventory.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.');"><i class="fas fa-trash"></i></button>
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

    <div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restockModalLabel">Restock Item: <span id="restockItemName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="restockForm" method="POST" action="{{ route('inventory.items.restock') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="restockItemId">
                        <div class="mb-3">
                            <label for="restock_store_id" class="form-label">Store</label>
                            <select class="form-select" id="restock_store_id" name="store_id" required>
                                <option value="">Select a store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="restock_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="restock_quantity" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="restock_lot_number" class="form-label">Lot Number (Optional)</label>
                            <input type="text" class="form-control" id="restock_lot_number" name="lot_number">
                        </div>
                        <div class="mb-3">
                            <label for="restock_expiry_date" class="form-label">Expiry Date (Optional)</label>
                            <input type="date" class="form-control" id="restock_expiry_date" name="expiry_date">
                        </div>
                        <div id="restockAlertContainer" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Restock</button>
                    </div>
                </form>
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