@extends('restaurant::layouts.adminMaster')
@section('title', 'Restaurant Settings')
@section('admin-content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('restaurant.admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-light py-3">
            <h3 class="card-title fw-bold mb-0"><i class="bi bi-gear me-2"></i>Restaurant Settings</h3>
        </div>
        <div class="card-body p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('restaurant.admin.settings.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="vat_rate" class="form-label fw-medium">VAT Rate (%)</label>
                    <input type="number" name="vat_rate" id="vat_rate" class="form-control form-control-lg"
                        value="{{ $settings['vat_rate'] }}" step="0.1" min="0" max="100" required>
                    <div class="form-text">Value Added Tax percentage applied to orders.</div>
                </div>
                <div class="mb-3">
                    <label for="service_charge_rate" class="form-label fw-medium">Service Charge (%)</label>
                    <input type="number" name="service_charge_rate" id="service_charge_rate" class="form-control form-control-lg"
                        value="{{ $settings['service_charge_rate'] }}" step="0.1" min="0" max="100" required>
                    <div class="form-text">Optional service charge percentage.</div>
                </div>
                <div class="mb-4">
                    <label for="discount_limit" class="form-label fw-medium">Discount Limit (₦)</label>
                    <input type="number" name="discount_limit" id="discount_limit" class="form-control form-control-lg"
                        value="{{ $settings['discount_limit'] }}" step="100" min="0" required>
                    <div class="form-text">Maximum discount allowed per order in Naira.</div>
                </div>
                <hr class="my-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock me-2"></i>Shift Hours</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="shift_start_time" class="form-label fw-medium">Shift Start Time</label>
                        <input type="time" name="shift_start_time" id="shift_start_time" class="form-control form-control-lg"
                            value="{{ $settings['shift_start_time'] }}" required>
                        <div class="form-text">Earliest time staff can start a shift.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="shift_end_time" class="form-label fw-medium">Shift End Time</label>
                        <input type="time" name="shift_end_time" id="shift_end_time" class="form-control form-control-lg"
                            value="{{ $settings['shift_end_time'] }}" required>
                        <div class="form-text">Latest time staff can start a shift.</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill">
                    <i class="bi bi-save me-2"></i>Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
