@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('page-content')
    <div class="mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-edit me-2"></i> Edit Payment Gateway</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.payment-gateways.update', $gateway) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Gateway</label>
                    <input type="text" class="form-control" value="{{ ucfirst($gateway->code) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Display Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $gateway->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $gateway->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default"
                           {{ old('is_default', $gateway->is_default) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Set as default gateway</label>
                </div>

                <hr>
                <h6 class="text-muted">Credentials</h6>
                <p class="text-muted small">Leave a field blank to keep its current value.</p>

                @foreach ($credentialFields[$gateway->code] as $field => $label)
                    <div class="mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <input type="text" name="credentials[{{ $field }}]"
                               class="form-control @error("credentials.$field") is-invalid @enderror"
                               value="{{ old("credentials.$field", $gateway->credentials[$field] ?? '') }}">
                        @error("credentials.$field") <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
