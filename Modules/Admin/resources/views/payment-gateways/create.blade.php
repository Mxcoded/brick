@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('page-content')
    <div class="mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-plus me-2"></i> Add Payment Gateway</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.payment-gateways.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Gateway</label>
                    <select name="code" id="code" class="form-select @error('code') is-invalid @enderror" required>
                        <option value="">Select gateway…</option>
                        @foreach ($codes as $code)
                            <option value="{{ $code }}" {{ old('code') === $code ? 'selected' : '' }}>
                                {{ ucfirst($code) }}
                            </option>
                        @endforeach
                    </select>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Display Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default"
                           {{ old('is_default') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Set as default gateway</label>
                </div>

                <hr>
                <h6 class="text-muted">Credentials</h6>

                @foreach ($codes as $code)
                    <div class="credential-fields d-none" data-code="{{ $code }}">
                        @foreach ($credentialFields[$code] as $field => $label)
                            <div class="mb-3">
                                <label class="form-label">{{ $label }}</label>
                                <input type="text" name="credentials[{{ $field }}]"
                                       class="form-control @error("credentials.$field") is-invalid @enderror">
                                @error("credentials.$field") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Save Gateway</button>
                <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const codeSelect = document.getElementById('code');
    function toggleFields() {
        document.querySelectorAll('.credential-fields').forEach(el => el.classList.add('d-none'));
        const selected = codeSelect.value;
        if (selected) {
            const block = document.querySelector('.credential-fields[data-code="'+selected+'"]');
            if (block) block.classList.remove('d-none');
        }
    }
    codeSelect.addEventListener('change', toggleFields);
    toggleFields();
</script>
@endpush
