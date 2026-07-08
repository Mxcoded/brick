@extends('layouts.master')

@section('title', 'Import Items from Excel')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Import Items from Excel</h1>
            <p class="text-muted mb-0">Bulk-create inventory items from an Excel or CSV spreadsheet.</p>
        </div>
        <a href="{{ route('inventory.import.template') }}" class="btn btn-outline-success">
            <i class="fas fa-download me-2"></i>Download Template
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">Upload Spreadsheet</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('inventory.import.process') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">File (<code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code>)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Default Supplier <small class="text-muted">(used when row has no supplier_id)</small></label>
                            <select name="default_supplier_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Default Store <small class="text-muted">(used when row has no store_id)</small></label>
                            <select name="default_store_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-upload me-2"></i>Import Items
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Required Columns</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm small mb-0">
                        <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
                        <tbody>
                            <tr><td><code>description</code></td><td><span class="badge bg-danger">Yes</span></td><td>Item name</td></tr>
                            <tr><td><code>sku</code></td><td><span class="badge bg-secondary">No</span></td><td>Auto-generated if blank</td></tr>
                            <tr><td><code>category</code></td><td><span class="badge bg-secondary">No</span></td><td>e.g. Food &amp; Beverage</td></tr>
                            <tr><td><code>supplier_id</code></td><td><span class="badge bg-secondary">No</span></td><td>Uses default if blank</td></tr>
                            <tr><td><code>price</code></td><td><span class="badge bg-secondary">No</span></td><td>Unit price</td></tr>
                            <tr><td><code>unit_of_measurement</code></td><td><span class="badge bg-secondary">No</span></td><td>e.g. kg, pcs, liters</td></tr>
                            <tr><td><code>unit_value</code></td><td><span class="badge bg-secondary">No</span></td><td>Value per unit</td></tr>
                            <tr><td><code>min_stock</code></td><td><span class="badge bg-secondary">No</span></td><td>Reorder point</td></tr>
                            <tr><td><code>max_stock</code></td><td><span class="badge bg-secondary">No</span></td><td>Max stock level</td></tr>
                            <tr><td><code>store_id</code></td><td><span class="badge bg-secondary">No</span></td><td>Uses default if blank</td></tr>
                            <tr><td><code>quantity</code></td><td><span class="badge bg-secondary">No</span></td><td>Initial stock qty</td></tr>
                            <tr><td><code>lot_number</code></td><td><span class="badge bg-secondary">No</span></td><td>Lot/batch number</td></tr>
                            <tr><td><code>expiry_date</code></td><td><span class="badge bg-secondary">No</span></td><td>YYYY-MM-DD or Excel date</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
