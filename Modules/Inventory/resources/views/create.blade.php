@extends('layouts.master')

@section('title', 'Add New Inventory Item')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Add New Item</h1>
        <p class="lead text-muted">Enter the details for a new item and its initial stock.</p>

        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <form id="createItemForm" action="{{ route('inventory.items.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Item Description</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">Select a supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price per Unit</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_of_measurement" class="form-label">Unit of Measurement</label>
                            <input type="text" class="form-control" id="unit_of_measurement" name="unit_of_measurement" placeholder="e.g., kg, pcs, box">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit_value" class="form-label">Unit Value</label>
                            <input type="number" class="form-control" id="unit_value" name="unit_value" step="0.01" min="0" placeholder="e.g., 1 for 1kg, 12 for 12pcs">
                        </div>
                    </div>

                    <h4 class="mt-4 mb-3">Initial Stock Details</h4>
                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="store_id" class="form-label">Initial Store</label>
                            <select class="form-select" id="store_id" name="store_id" required>
                                <option value="">Select a store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="lot_number" class="form-label">Lot Number (Optional)</label>
                            <input type="text" class="form-control" id="lot_number" name="lot_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                    </div>

                    <div id="alertContainer" class="mt-3"></div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success me-2 shadow-sm">
                            <i class="fas fa-save me-2"></i>Save Item
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#createItemForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const alertContainer = $('#alertContainer');
                
                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        alertContainer.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        form.trigger('reset');
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