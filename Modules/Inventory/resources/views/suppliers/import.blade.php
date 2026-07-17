@extends('layouts.master')

@section('title', 'Import Suppliers')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Import Suppliers</h1>
            <p class="text-muted mb-0">Bulk-import suppliers from an Excel or CSV file.</p>
        </div>
        <a href="{{ route('inventory.suppliers.import.template') }}" class="btn btn-success">
            <i class="fas fa-download me-2"></i>Download Template
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('inventory.suppliers.import.process') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Accepted formats: .xlsx, .xls, .csv</div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Import Suppliers
                </button>
                <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <div class="card shadow border-0 mt-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">Column Reference</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>name</code></td><td><span class="text-danger">Yes</span></td><td>Supplier name (must be unique)</td></tr>
                    <tr><td><code>contact_person</code></td><td><span class="text-muted">No</span></td><td>Contact person name</td></tr>
                    <tr><td><code>email</code></td><td><span class="text-muted">No</span></td><td>Email address</td></tr>
                    <tr><td><code>phone</code></td><td><span class="text-muted">No</span></td><td>Phone number</td></tr>
                    <tr><td><code>address</code></td><td><span class="text-muted">No</span></td><td>Physical address</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
