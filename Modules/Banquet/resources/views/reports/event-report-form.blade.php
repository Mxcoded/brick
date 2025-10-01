@extends('staff::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Report</li>
@endsection

@section('page-content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold display-5 text-primary">
            <i class="fas fa-file-pdf me-3"></i>Generate Banquet Report
        </h1>
        <!-- Back to Index Button -->
        <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <!-- Report Generation Form -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('banquet.orders.report.generate') }}" method="POST">
                @csrf
                <!-- Start Date Input -->
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- End Date Input -->
                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Generate Report
                </button>
            </form>
        </div>
    </div>
</div>
@endsection