@extends('layouts.master')

@section('title', 'Stock Transfer')

@section('page-content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-dark mb-0">Stock Transfer</h1>
                <p class="text-muted mb-0">Move inventory between stores and warehouses.</p>
            </div>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                <form id="transferForm" action="{{ route('inventory.transfer') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Select Item</label>
                            <select class="form-select form-select-lg" id="item_id" name="item_id" required>
                                <option value="">Choose an item to transfer...</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->description }} @if($item->category) ({{ $item->category }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row align-items-center mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-danger">
                                <i class="fas fa-arrow-right-from-bracket me-1"></i> From Store
                            </label>
                            <select class="form-select" id="from_store_id" name="from_store_id" required>
                                <option value="">Select source store...</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <div id="availableStock" class="mt-2"></div>
                        </div>

                        <div class="col-md-2 text-center py-3">
                            <div class="transfer-arrow">
                                <i class="fas fa-arrow-right fa-3x text-muted"></i>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-arrow-right-to-bracket me-1"></i> To Store
                            </label>
                            <select class="form-select" id="to_store_id" name="to_store_id" required>
                                <option value="">Select destination store...</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity to Transfer</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Add a reason or reference for this transfer..."></textarea>
                    </div>

                    <div id="alertContainer" class="mt-3"></div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary me-2 shadow-sm">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success shadow-sm">
                            <i class="fas fa-truck me-2"></i>Initiate Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .transfer-arrow {
            background: #f8f9fc;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .stock-badge {
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50rem;
        }
    </style>
    <script>
        $(document).ready(function() {
            function checkStock() {
                const itemId = $('#item_id').val();
                const fromStoreId = $('#from_store_id').val();

                if (itemId && fromStoreId) {
                    $('#availableStock').html('<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Checking stock...</span>');
                    $.ajax({
                        url: `/inventory/api/stores/${fromStoreId}/items?item_id=${itemId}`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            let totalStock = 0;
                            response.forEach(stockItem => {
                                totalStock += stockItem.quantity;
                            });
                            if (totalStock > 0) {
                                $('#availableStock').html(`<span class="badge bg-success stock-badge"><i class="fas fa-check-circle me-1"></i>Available: ${totalStock} units</span>`);
                            } else {
                                $('#availableStock').html(`<span class="badge bg-danger stock-badge"><i class="fas fa-times-circle me-1"></i>Out of stock</span>`);
                            }
                            $('#quantity').attr('max', totalStock || 0);
                        },
                        error: function() {
                            $('#availableStock').html(`<span class="badge bg-warning text-dark stock-badge"><i class="fas fa-exclamation-triangle me-1"></i>Could not retrieve stock</span>`);
                            $('#quantity').removeAttr('max');
                        }
                    });
                } else {
                    $('#availableStock').html('');
                }
            }

            $('#item_id, #from_store_id').on('change', checkStock);

            $('#transferForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const alertContainer = $('#alertContainer');
                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        alertContainer.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                        form.trigger('reset');
                        $('#availableStock').html('');
                        submitBtn.prop('disabled', false).html('<i class="fas fa-truck me-2"></i>Initiate Transfer');
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors || xhr.responseJSON;
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Transfer failed.</strong><ul class="mb-0 mt-1">';
                        if (errors && typeof errors === 'object') {
                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + (Array.isArray(value) ? value[0] : value) + '</li>';
                            });
                        } else {
                            errorHtml += '<li>' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</li>';
                        }
                        errorHtml += '</ul></div>';
                        alertContainer.html(errorHtml);
                        submitBtn.prop('disabled', false).html('<i class="fas fa-truck me-2"></i>Initiate Transfer');
                    }
                });
            });
        });
    </script>
@endsection
