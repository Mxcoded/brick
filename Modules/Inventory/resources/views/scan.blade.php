@extends('layouts.master')

@section('title', 'Barcode Scan')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Barcode Scan</h1>
            <p class="text-muted mb-0">Scan or type a barcode to look up an item instantly</p>
        </div>
        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-camera fa-4x text-muted"></i>
                    </div>
                    <h4 class="mb-3">Scan a Barcode</h4>
                    <div class="input-group input-group-lg mb-3">
                        <input type="text" id="barcodeInput" class="form-control" placeholder="Scan or type SKU..." autofocus>
                        <button class="btn btn-primary" id="lookupBtn"><i class="fas fa-search me-1"></i>Lookup</button>
                    </div>
                    <p class="text-muted small">Point your barcode scanner at the item, or type the SKU manually.</p>
                </div>
            </div>

            <div id="resultContainer" class="mt-4 d-none">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3 d-flex align-items-center">
                        <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-0" id="resultTitle">Item Found</h5>
                            <small class="text-muted" id="resultSku"></small>
                        </div>
                        <a href="#" id="resultEditLink" class="btn btn-sm btn-outline-primary ms-auto"><i class="fas fa-edit me-1"></i>Edit</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="mb-0">
                                    <dt>Description</dt>
                                    <dd id="resultDesc" class="fw-bold"></dd>
                                    <dt>Category</dt>
                                    <dd id="resultCategory"></dd>
                                    <dt>Supplier</dt>
                                    <dd id="resultSupplier"></dd>
                                    <dt>Price</dt>
                                    <dd id="resultPrice"></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Stock by Store</h6>
                                <ul id="resultStock" class="list-unstyled mb-0"></ul>
                                <hr>
                                <p class="mb-0"><strong>Total:</strong> <span id="resultTotal" class="fw-bold text-primary"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="notFoundContainer" class="mt-4 d-none">
                <div class="card shadow border-0 bg-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                        <h5>Item Not Found</h5>
                        <p class="text-muted mb-0" id="notFoundMessage">No item matches that SKU.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function lookup() {
        const sku = $('#barcodeInput').val().trim();
        if (!sku) return;

        $('#resultContainer, #notFoundContainer').addClass('d-none');
        $('#resultContainer').removeClass('d-none');
        $('#resultTitle').html('<i class="fas fa-spinner fa-spin me-1"></i> Searching...');

        $.ajax({
            url: '{{ route("inventory.lookup-barcode") }}',
            data: { sku: sku },
            dataType: 'json',
            success: function(response) {
                if (response.found) {
                    const item = response.item;
                    $('#resultTitle').text(item.description);
                    $('#resultSku').text('SKU: ' + item.sku);
                    $('#resultDesc').text(item.description);
                    $('#resultCategory').text(item.category || 'N/A');
                    $('#resultSupplier').text(item.supplier);
                    $('#resultPrice').text('₦' + parseFloat(item.price).toFixed(2));
                    $('#resultEditLink').attr('href', '{{ url("inventory/items") }}/' + item.id + '/edit');

                    const stockList = $('#resultStock');
                    stockList.empty();
                    if (item.stock && item.stock.length) {
                        item.stock.forEach(function(s) {
                            stockList.append('<li><span class="badge bg-secondary me-1">' + s.store + '</span> ' + s.quantity + ' units</li>');
                        });
                    } else {
                        stockList.append('<li class="text-muted">No stock records</li>');
                    }
                    $('#resultTotal').text(item.total_quantity + ' units');
                    $('#resultContainer').removeClass('d-none');
                    $('#notFoundContainer').addClass('d-none');
                } else {
                    $('#resultContainer').addClass('d-none');
                    $('#notFoundMessage').text(response.message);
                    $('#notFoundContainer').removeClass('d-none');
                }
            },
            error: function() {
                $('#resultTitle').text('Error looking up barcode.');
            }
        });
    }

    $('#lookupBtn').on('click', lookup);
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which == 13) { lookup(); }
    });
});
</script>
@endsection
