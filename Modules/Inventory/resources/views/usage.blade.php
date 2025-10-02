@extends('layouts.master')

@section('title', 'Record Item Usage')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Record Item Usage</h1>
        <p class="lead text-muted">Log items used for maintenance or other tasks.</p>

        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <form id="usageForm" action="{{ route('inventory.usage.store') }}" method="POST">
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
                            <label for="store_id" class="form-label">Store</label>
                            <select class="form-select" id="store_id" name="store_id" required>
                                <option value="">Select a store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <small id="availableStock" class="form-text text-muted"></small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select a department</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity_used" class="form-label">Quantity Used</label>
                            <input type="number" class="form-control" id="quantity_used" name="quantity_used" min="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="technician_name" class="form-label">Technician Name (Optional)</label>
                            <input type="text" class="form-control" id="technician_name" name="technician_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="used_for" class="form-label">Used For</label>
                        <textarea class="form-control" id="used_for" name="used_for" rows="3" required></textarea>
                        <small class="form-text text-muted">Describe the task or location where the item was used.</small>
                    </div>

                    <div id="alertContainer" class="mt-3"></div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success me-2 shadow-sm">
                            <i class="fas fa-tools me-2"></i>Record Usage
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
            // Function to fetch departments for a given store
            function fetchDepartments(storeId) {
                const departmentDropdown = $('#department_id');
                departmentDropdown.empty().append('<option value="">Loading departments...</option>');

                $.ajax({
                    url: `/inventory/api/stores/${storeId}/departments`, // We will create this API route
                    method: 'GET',
                    success: function(response) {
                        departmentDropdown.empty().append('<option value="">Select a department</option>');
                        if (response.length > 0) {
                            response.forEach(department => {
                                departmentDropdown.append(`<option value="${department.id}">${department.name}</option>`);
                            });
                        } else {
                            departmentDropdown.empty().append('<option value="">No departments found</option>');
                        }
                    },
                    error: function() {
                        departmentDropdown.empty().append('<option value="">Error loading departments</option>');
                    }
                });
            }

            // Trigger stock check on item and store change
            $('#item_id, #store_id').on('change', function() {
                const itemId = $('#item_id').val();
                const storeId = $('#store_id').val();
                if (storeId) {
                    fetchDepartments(storeId);
                } else {
                    $('#department_id').empty().append('<option value="">Select a department</option>');
                }

                if (itemId && storeId) {
                    $.ajax({
                        url: `/inventory/api/stores/${storeId}/items`,
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
                            $('#quantity_used').attr('max', totalStock);
                        },
                        error: function() {
                            $('#availableStock').text('Could not retrieve stock.');
                            $('#quantity_used').removeAttr('max');
                        }
                    });
                } else {
                    $('#availableStock').text('');
                }
            });

            $('#usageForm').on('submit', function(e) {
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