@extends('layouts.master')

@section('title', 'Transfer Inventory')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Transfer Inventory</h1>
        <p class="lead text-muted">Move items between stores.</p>

        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <form id="transferForm" action="{{ route('inventory.transfer') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_id" class="form-label">Item</label>
                            <select class="form-select" id="item_id" name="item_id" required>
                                <option value="">Select an item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity to Transfer</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="from_store_id" class="form-label">From Store</label>
                            <select class="form-select" id="from_store_id" name="from_store_id" required>
                                <option value="">Select a store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <small id="availableStock" class="form-text text-muted"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="to_store_id" class="form-label">To Store</label>
                            <select class="form-select" id="to_store_id" name="to_store_id" required>
                                <option value="">Select a store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div id="alertContainer" class="mt-3"></div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success me-2 shadow-sm">
                            <i class="fas fa-truck me-2"></i>Initiate Transfer
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
            // Function to check available stock
            function checkStock() {
                const itemId = $('#item_id').val();
                const fromStoreId = $('#from_store_id').val();

                if (itemId && fromStoreId) {
                    $.ajax({
                        url: `/inventory/api/stores/${fromStoreId}/items`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            let totalStock = 0;
                            response.forEach(stockItem => {
                                if (stockItem.item_id == itemId) {
                                    totalStock += stockItem.quantity;
                                }
                            });
                            $('#availableStock').text(`Available stock: ${totalStock}`);
                            $('#quantity').attr('max', totalStock);
                        },
                        error: function() {
                            $('#availableStock').text('Could not retrieve stock.');
                            $('#quantity').removeAttr('max');
                        }
                    });
                } else {
                    $('#availableStock').text('');
                }
            }

            // Trigger stock check on item and store change
            $('#item_id, #from_store_id').on('change', checkStock);

            $('#transferForm').on('submit', function(e) {
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
                        $('#availableStock').text('');
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