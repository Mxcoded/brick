@extends('layouts.master')

@section('title', 'Issue Items')

@section('page-content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-dark mb-0">Issue Items</h1>
                <p class="text-muted mb-0">Issue items from store inventory to departments — for sale, complimentary, or operational use.</p>
            </div>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-pen me-2"></i>Issue Item</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="usageForm" action="{{ route('inventory.usage.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold">Item <span class="text-danger">*</span></label>
                                    <select class="form-select" id="item_id" name="item_id" required>
                                        <option value="">Search for an item...</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}" data-uom="{{ $item->unit_of_measurement }}" data-price="{{ $item->price }}">
                                                {{ $item->description }}@if($item->category) ({{ $item->category }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Store <span class="text-danger">*</span></label>
                                    <select class="form-select" id="store_id" name="store_id" required>
                                        <option value="">Select store...</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="stockPreview" class="row g-3 mt-1 d-none">
                                <div class="col-12">
                                    <div class="card border bg-light">
                                        <div class="card-body py-3">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-3 text-center">
                                                    <div class="text-muted small text-uppercase" style="letter-spacing: 0.05em;">Available</div>
                                                    <div class="h3 mb-0 fw-bold" id="stockQty">0</div>
                                                    <small class="text-muted" id="stockUom"></small>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <div class="text-muted small text-uppercase" style="letter-spacing: 0.05em;">Unit Price</div>
                                                    <div class="h5 mb-0 fw-bold" id="unitPriceDisplay">&#x20A6;0.00</div>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <div class="text-muted small text-uppercase" style="letter-spacing: 0.05em;">Usage Cost</div>
                                                    <div class="h5 mb-0 fw-bold text-warning" id="usageCostDisplay">&#x20A6;0.00</div>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <div class="text-muted small text-uppercase" style="letter-spacing: 0.05em;">After Issue</div>
                                                    <div class="h5 mb-0 fw-bold" id="afterStock">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id" required>
                                        <option value="">Select store first...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quantity_used" name="quantity_used" min="1" required disabled>
                                        <span class="input-group-text" id="uomBadge">UoM</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date Used</label>
                                    <input type="date" class="form-control" id="date_used" name="date_used" value="{{ now()->toDateString() }}">
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Reference / Req. # <i class="fas fa-magic text-muted small" title="Auto-generated from department" data-bs-toggle="tooltip"></i></label>
                                    <input type="text" class="form-control" id="reference" name="reference" placeholder="Auto-generated from department">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Used For <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="used_for" name="used_for" required placeholder="e.g., Cafe restock, VIP amenities, Kitchen prep">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Received By</label>
                                    <input type="text" class="form-control" id="technician_name" name="technician_name" placeholder="Who collected the items?">
                                </div>
                            </div>

                            <div id="alertContainer" class="mt-3"></div>

                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary me-2 shadow-sm">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary shadow-sm px-4" id="submitBtn" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Issue Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($recentUsage->isNotEmpty())
                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-clock me-2"></i>Recent Usage</h5>
                        <span class="badge bg-secondary">{{ $recentUsage->count() }} records</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Store</th>
                                        <th>Dept</th>
                                        <th class="text-center">Qty</th>
                                        <th>Used For</th>
                                        <th>Ref</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsage as $log)
                                    <tr>
                                        <td>{{ $log->item?->description ?? 'N/A' }}</td>
                                        <td>{{ $log->store?->name ?? 'N/A' }}</td>
                                        <td>{{ $log->department?->name ?? '-' }}</td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $log->quantity_used }}</span></td>
                                        <td>{{ Str::limit($log->used_for, 25) }}</td>
                                        <td><code>{{ $log->reference ?? '-' }}</code></td>
                                        <td>{{ optional($log->date_used ?? $log->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card shadow border-0 bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-box-open fa-3x mb-3 opacity-75"></i>
                        <h6 class="fw-bold text-uppercase small" style="letter-spacing: 0.05em;">Quick Tips</h6>
                        <p class="mb-0 small opacity-75">Departments are the end users. Items are deducted via FEFO (First-Expiry-First-Out) from the selected store. Cost is calculated at the item's unit price.</p>
                    </div>
                </div>

                <div class="card shadow border-0 mt-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Issue Summary</h6>
                    </div>
                    <div class="card-body">
                        <div id="summaryPlaceholder" class="text-center text-muted py-3">
                            <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                            <p class="mb-0 small">Select an item and store to see the summary.</p>
                        </div>
                        <div id="summaryContent" class="d-none">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0">Item:</td>
                                    <td class="fw-bold text-end pe-0" id="summaryItem">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Store:</td>
                                    <td class="fw-bold text-end pe-0" id="summaryStore">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Department:</td>
                                    <td class="fw-bold text-end pe-0" id="summaryDept">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Quantity:</td>
                                    <td class="fw-bold text-end pe-0" id="summaryQty">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Unit Price:</td>
                                    <td class="fw-bold text-end pe-0" id="summaryUnitPrice">-</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted ps-0 pt-2">Total Cost:</td>
                                    <td class="fw-bold text-end pe-0 pt-2 text-warning" id="summaryTotalCost">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#item_id').select2({
                placeholder: 'Search for an item...',
                allowClear: true,
                width: '100%'
            });

            function fetchDepartments(storeId) {
                const departmentDropdown = $('#department_id');
                departmentDropdown.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: `/inventory/api/stores/${storeId}/departments`,
                    method: 'GET',
                    success: function(response) {
                        departmentDropdown.empty().append('<option value="">Select a department...</option>');
                        if (response.length > 0) {
                            response.forEach(department => {
                                departmentDropdown.append(`<option value="${department.id}">${department.name}</option>`);
                            });
                        } else {
                            departmentDropdown.empty().append('<option value="">No departments found</option>');
                        }
                        toggleSubmit();
                    },
                    error: function() {
                        departmentDropdown.empty().append('<option value="">Error loading departments</option>');
                    }
                });
            }

            function fetchReference(deptId) {
                if (!deptId) {
                    $('#reference').val('');
                    return;
                }
                $.ajax({
                    url: `/inventory/api/generate-reference/${deptId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#reference').val(response.reference);
                    }
                });
            }

            function updateStockPreview() {
                const itemId = $('#item_id').val();
                const storeId = $('#store_id').val();
                const qty = parseInt($('#quantity_used').val()) || 0;

                if (itemId && storeId) {
                    $('#stockPreview').removeClass('d-none');
                    $('#stockQty').html('<i class="fas fa-spinner fa-spin"></i>');
                    $('#usageCostDisplay').html('&#x20A6;0.00');
                    $('#afterStock').html('-');

                    $.ajax({
                        url: `/inventory/api/stores/${storeId}/items?item_id=${itemId}`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            let totalStock = 0;
                            response.forEach(stockItem => { totalStock += stockItem.quantity; });

                            const selectedOption = $('#item_id').find('option:selected');
                            const uom = selectedOption.data('uom') || 'units';
                            const price = parseFloat(selectedOption.data('price')) || 0;

                            $('#stockUom').text(uom);
                            $('#stockQty').text(totalStock);
                            $('#unitPriceDisplay').html('&#x20A6;' + price.toFixed(2));

                            const usageCost = qty * price;
                            $('#usageCostDisplay').html('&#x20A6;' + usageCost.toFixed(2));
                            $('#afterStock').text(Math.max(0, totalStock - qty));

                            if (totalStock === 0) {
                                $('#stockPreview .card').addClass('border-danger');
                            } else {
                                $('#stockPreview .card').removeClass('border-danger');
                            }

                            $('#quantity_used').prop('disabled', false).attr('max', totalStock);
                            updateSummary(itemId, storeId, qty, totalStock, price);
                            toggleSubmit();
                        },
                        error: function() {
                            $('#stockQty').text('?');
                            $('#stockPreview .card').addClass('border-danger');
                        }
                    });
                } else {
                    $('#stockPreview').addClass('d-none');
                    $('#quantity_used').prop('disabled', true);
                    $('#summaryPlaceholder').removeClass('d-none');
                    $('#summaryContent').addClass('d-none');
                    toggleSubmit();
                }
            }

            function updateSummary(itemId, storeId, qty, stock, price) {
                const itemText = $('#item_id').find('option:selected').text() || '-';
                const storeText = $('#store_id').find('option:selected').text() || '-';
                const deptText = $('#department_id').find('option:selected').text() || 'Not selected';

                $('#summaryItem').text(itemText);
                $('#summaryStore').text(storeText);
                $('#summaryDept').text(deptText);
                $('#summaryQty').text(qty > 0 ? qty + ' (of ' + stock + ')' : '0');
                $('#summaryUnitPrice').html('&#x20A6;' + (price || 0).toFixed(2));
                $('#summaryTotalCost').html('&#x20A6;' + (qty * (price || 0)).toFixed(2));
                $('#summaryPlaceholder').addClass('d-none');
                $('#summaryContent').removeClass('d-none');
            }

            function toggleSubmit() {
                const itemId = $('#item_id').val();
                const storeId = $('#store_id').val();
                const deptId = $('#department_id').val();
                const qty = parseInt($('#quantity_used').val()) || 0;
                const usedFor = $('#used_for').val().trim();
                $('#submitBtn').prop('disabled', !(itemId && storeId && deptId && qty > 0 && usedFor));
            }

            $('#item_id, #store_id').on('change', function() {
                updateStockPreview();
                const storeId = $('#store_id').val();
                if (storeId) {
                    fetchDepartments(storeId);
                } else {
                    $('#department_id').empty().append('<option value="">Select store first...</option>');
                }
                $('#reference').val('');
            });

            $('#department_id').on('change', function() {
                fetchReference($(this).val());
                toggleSubmit();
            });

            $('#quantity_used, #used_for').on('change keyup', toggleSubmit);

            $('#quantity_used').on('input', function() {
                updateStockPreview();
            });

            $('#usageForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const alertContainer = $('#alertContainer');
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Issuing...');

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        const cost = response.cost || 0;
                        alertContainer.html(`
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2 fs-5"></i>
                                <div>
                                    <strong>Done!</strong> ${response.message}
                                    ${cost > 0 ? `<br><small class="mb-0">Cost impact: &#x20A6;${cost.toFixed(2)}</small>` : ''}
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                        form.trigger('reset');
                        $('#item_id').val('').trigger('change');
                        $('#stockPreview').addClass('d-none');
                        $('#department_id').empty().append('<option value="">Select store first...</option>');
                        $('#quantity_used').prop('disabled', true);
                        $('#uomBadge').text('UoM');
                        $('#summaryPlaceholder').removeClass('d-none');
                        $('#summaryContent').addClass('d-none');
                        $('#reference').val('');
                        toggleSubmit();
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors || xhr.responseJSON;
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle me-2"></i><strong>Error.</strong><ul class="mb-0 mt-1">';
                        if (errors && typeof errors === 'object') {
                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + (Array.isArray(value) ? value[0] : value) + '</li>';
                            });
                        } else {
                            errorHtml += '<li>' + (xhr.responseJSON?.message || 'An error occurred.') + '</li>';
                        }
                        errorHtml += '</ul></div>';
                        alertContainer.html(errorHtml);
                        toggleSubmit();
                    }
                });
            });
        });
    </script>
@endsection
