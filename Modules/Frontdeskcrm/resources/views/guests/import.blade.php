@extends('layouts.master')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-file-import me-2 text-primary"></i>Import Guests</h1>
            <p class="text-muted mb-0">Bulk import guest profiles from an Excel or CSV file</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.guests.import.template') }}" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Download Excel Guide</a>
            <a href="{{ route('frontdesk.guests.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Directory</a>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        New to bulk import? Download the <strong>Excel Guide</strong>, fill in the template, then upload it below.
        Duplicate guests (matched on <code>full_name</code> or <code>email</code>) are skipped automatically.
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('frontdesk.guests.import.process') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Accepted formats: .xlsx, .xls, .csv (max 5MB)</small>
                            <div class="mt-2">
                                <a href="{{ route('frontdesk.guests.import.template') }}" class="small">
                                    <i class="fas fa-download me-1"></i>Download the Excel guide &amp; template
                                </a>
                            </div>
                        </div>
                        @error('file')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Import</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">File Format Guide</div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Your file should have a header row. Supported columns:</p>
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0">
                            <thead>
                                <tr>
                                    <th>Column Name</th>
                                    <th>Required</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>full_name</code></td><td><span class="text-danger">Yes</span></td><td>Guest's full name</td></tr>
                                <tr><td><code>email</code></td><td>No</td><td>Used for duplicate check</td></tr>
                                <tr><td><code>contact_number</code> / <code>phone</code></td><td>No</td><td>Phone number</td></tr>
                                <tr><td><code>nationality</code></td><td>No</td><td></td></tr>
                                <tr><td><code>gender</code></td><td>No</td><td>Male, Female, Other</td></tr>
                                <tr><td><code>birthday</code> / <code>date_of_birth</code></td><td>No</td><td>YYYY-MM-DD format</td></tr>
                                <tr><td><code>occupation</code></td><td>No</td><td></td></tr>
                                <tr><td><code>company</code> / <code>company_name</code></td><td>No</td><td></td></tr>
                                <tr><td><code>address</code> / <code>home_address</code></td><td>No</td><td></td></tr>
                                <tr><td><code>city</code></td><td>No</td><td></td></tr>
                                <tr><td><code>state</code></td><td>No</td><td></td></tr>
                                <tr><td><code>zip_code</code> / <code>zip</code></td><td>No</td><td></td></tr>
                                <tr><td><code>title</code></td><td>No</td><td>Mr, Mrs, Ms, Dr</td></tr>
                                <tr><td><code>identification_type</code> / <code>id_type</code></td><td>No</td><td></td></tr>
                                <tr><td><code>identification_number</code> / <code>id_number</code></td><td>No</td><td></td></tr>
                                <tr><td><code>emergency_name</code></td><td>No</td><td></td></tr>
                                <tr><td><code>emergency_relationship</code></td><td>No</td><td></td></tr>
                                <tr><td><code>emergency_contact</code> / <code>emergency_phone</code></td><td>No</td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mt-2 mb-0">Duplicates are detected by matching <code>full_name</code> or <code>email</code>. Duplicate rows are skipped.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection