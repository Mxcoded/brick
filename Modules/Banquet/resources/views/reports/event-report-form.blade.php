@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('page-content')
<div class="container-fluid px-4 banquet-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold display-5 text-charcoal">
            <i class="fas fa-file-pdf me-3 text-gold"></i>Generate Banquet Report
        </h1>
        <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-gold text-white py-3">
            <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Report Criteria</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('banquet.reports.generate') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label fw-bold text-charcoal">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-gold"></i></span>
                            <input type="date" class="form-control border-start-0 ps-0 @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" required>
                        </div>
                        @error('start_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-bold text-charcoal">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-gold"></i></span>
                            <input type="date" class="form-control border-start-0 ps-0 @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" required>
                        </div>
                        @error('end_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-gold px-4 py-2 shadow-sm">
                        <i class="fas fa-file-export me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: white; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: white; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
    .form-control:focus { border-color: #C8A165; box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25); }
</style>
@endsection