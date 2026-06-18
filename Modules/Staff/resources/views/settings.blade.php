@extends('layouts.master')

@section('page-content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-800">SMS Settings</h1>
                <p class="text-muted">Configure the birthday SMS message sent to employees</p>
            </div>
            <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Birthday SMS Message</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('staff.settings.update') }}">
                @csrf

                <div class="mb-3">
                    <label for="birthday_sms_message" class="form-label">SMS Message</label>
                    <textarea class="form-control @error('birthday_sms_message') is-invalid @enderror"
                              id="birthday_sms_message"
                              name="birthday_sms_message"
                              rows="4"
                              maxlength="160"
                              oninput="updateCounter(this)">{{ old('birthday_sms_message', $message) }}</textarea>
                    @error('birthday_sms_message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-1 text-muted small">
                        <span id="charCount">{{ strlen(old('birthday_sms_message', $message)) }}</span> / 160 characters
                        <span class="ms-2">(1 SMS page)</span>
                    </div>
                    <div class="mt-2 text-muted small">
                        <strong>Placeholders:</strong>
                        <code>{name}</code> = employee name,
                        <code>{position}</code> = employee position
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Message
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
        </div>
        <div class="card-body" id="preview">
            <p class="mb-0 text-muted">
                {{ str_replace(['{name}', '{position}'], ['John Doe', 'Front Desk Officer'], old('birthday_sms_message', $message)) }}
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCounter(el) {
    const count = el.value.length;
    document.getElementById('charCount').textContent = count;
    const preview = el.value
        .replace(/\{name\}/g, 'John Doe')
        .replace(/\{position\}/g, 'Front Desk Officer');
    document.querySelector('#preview p').textContent = preview || '—';
}
</script>
@endpush
