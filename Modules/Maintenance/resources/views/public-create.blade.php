@extends('layouts.base')

@section('styles')
<style>
    .report-header { background: linear-gradient(135deg, #333 0%, #555 100%); padding: 40px 0; margin-bottom: 40px; }
    .report-header h1 { color: #C8A165; font-weight: 800; }
    .report-header p { color: #ccc; }
    .form-card { border: none; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); margin-bottom: 40px; }
    .form-card .card-body { padding: 2.5rem; }
    .form-label { font-weight: 600; color: #555; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #e0e0e0; padding: 12px 16px; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .brand-mark { font-family: 'Proxima Nova', Arial, sans-serif; font-weight: 800; font-size: 1.6rem; color: #C8A165; letter-spacing: -0.5px; }
    .footer-text { color: #999; font-size: 0.85rem; }
</style>
@endsection

@section('content')
<div class="report-header text-center">
    <div class="container">
        <span class="brand-mark">BRICKSPOINT<sup>&trade;</sup></span>
        <h1 class="mt-3"><i class="fas fa-tools me-2"></i>Report a Maintenance Issue</h1>
        <p class="mb-0">Use this form to report any facility or IT issue. Our team will follow up.</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card form-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('maintenance.public.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="lodged_by" class="form-control" value="{{ old('lodged_by') }}" required maxlength="100" placeholder="e.g. John Doe">
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}" required maxlength="100" placeholder="e.g. Room 204, Lobby, Kitchen">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department" class="form-select" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach (\Modules\Maintenance\Models\MaintenanceLog::DEPARTMENTS as $key => $label)
                                        <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description of Issue <span class="text-danger">*</span></label>
                            <textarea name="nature_of_complaint" class="form-control" rows="5" required placeholder="Please describe the issue in detail...">{{ old('nature_of_complaint') }}</textarea>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-lg px-5" style="background-color: #C8A165; color: #fff; border-radius: 10px; font-weight: 600;">
                                <i class="fas fa-paper-plane me-2"></i> Submit Report
                            </button>
                            <a href="{{ url('/') }}" class="btn btn-lg btn-outline-secondary px-4" style="border-radius: 10px;">Back to Home</a>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center footer-text mb-5">
                <i class="fas fa-shield-alt me-1"></i> Your report will be sent directly to the maintenance team.
            </p>
        </div>
    </div>
</div>
@endsection
