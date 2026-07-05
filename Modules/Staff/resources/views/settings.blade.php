@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Settings</h1>
            <p class="text-muted">Configure HR module settings</p>
        </div>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SMS Card --}}
    <form method="POST" action="{{ route('staff.settings.update') }}" class="card shadow mb-4">
        @csrf
        <div class="card-header py-3 d-flex align-items-center gap-2">
            <i class="fas fa-sms text-primary"></i>
            <h6 class="m-0 font-weight-bold text-primary">Birthday SMS</h6>
        </div>
        <div class="card-body">
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

            <div class="card bg-light border-0">
                <div class="card-body" id="preview">
                    <small class="text-muted d-block mb-1">Preview:</small>
                    <p class="mb-0">
                        {{ str_replace(['{name}', '{position}'], ['John Doe', 'Front Desk Officer'], old('birthday_sms_message', $message)) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save SMS Settings
            </button>
        </div>
    </form>

    {{-- Hikvision Card --}}
    <form method="POST" action="{{ route('staff.settings.update') }}" class="card shadow mb-4">
        @csrf
        <div class="card-header py-3 d-flex align-items-center gap-2">
            <i class="fas fa-fingerprint text-info"></i>
            <h6 class="m-0 font-weight-bold text-info">Hikvision Time Attendance Machine</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="hikvision_ip" class="form-label">Machine IP Address</label>
                    <input type="text" class="form-control @error('hikvision_ip') is-invalid @enderror"
                           id="hikvision_ip" name="hikvision_ip"
                           value="{{ old('hikvision_ip', $hikvisionIp) }}"
                           placeholder="e.g. 192.168.1.100">
                    @error('hikvision_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">The network IP of your Hikvision attendance machine.</div>
                </div>
                <div class="col-md-3">
                    <label for="hikvision_username" class="form-label">Username</label>
                    <input type="text" class="form-control @error('hikvision_username') is-invalid @enderror"
                           id="hikvision_username" name="hikvision_username"
                           value="{{ old('hikvision_username', $hikvisionUsername) }}">
                    @error('hikvision_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Default is <code>admin</code>.</div>
                </div>
                <div class="col-md-3">
                    <label for="hikvision_password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('hikvision_password') is-invalid @enderror"
                           id="hikvision_password" name="hikvision_password"
                           value="{{ old('hikvision_password', $hikvisionPassword) }}">
                    @error('hikvision_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-2">
                    <label for="hikvision_port" class="form-label">Port</label>
                    <input type="number" class="form-control @error('hikvision_port') is-invalid @enderror"
                           id="hikvision_port" name="hikvision_port"
                           value="{{ old('hikvision_port', $hikvisionPort) }}"
                           min="1" max="65535">
                    @error('hikvision_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Default <code>80</code>. Use <code>8443</code> for HTTPS.</div>
                </div>
                <div class="col-md-2">
                    <label for="hikvision_timeout" class="form-label">Timeout (sec)</label>
                    <input type="number" class="form-control @error('hikvision_timeout') is-invalid @enderror"
                           id="hikvision_timeout" name="hikvision_timeout"
                           value="{{ old('hikvision_timeout', $hikvisionTimeout) }}"
                           min="5" max="120">
                    @error('hikvision_timeout')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <span id="hikvisionTestResult" class="small"></span>
            </div>
            <hr>
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                After saving, run <code>php artisan attendance:import-hikvision</code> to pull records.
                Schedule it with <code>* * * * * php /path/to/artisan attendance:import-hikvision &gt;&gt; /dev/null 2&gt;&amp;1</code>
                for automatic polling every minute.
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-info" id="testHikvisionBtn" onclick="testHikvisionConnection()">
                <i class="fas fa-plug me-1"></i> Test Connection
            </button>
            <button type="submit" class="btn btn-info">
                <i class="fas fa-save me-1"></i> Save Device Settings
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function updateCounter(el) {
    const count = el.value.length;
    document.getElementById('charCount').textContent = count;
    const preview = el.value
        .replace(/\{name\}/g, 'John Doe')
        .replace(/\{position\}/g, 'Front Desk Officer');
    document.querySelector('#preview p').textContent = preview || '—';
}

function testHikvisionConnection() {
    const btn = document.getElementById('testHikvisionBtn');
    const result = document.getElementById('hikvisionTestResult');
    btn.disabled = true;
    result.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    result.className = 'ms-2 small text-muted';

    fetch('{{ route("staff.attendance.hikvision-test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ip: document.getElementById('hikvision_ip').value,
            username: document.getElementById('hikvision_username').value,
            password: document.getElementById('hikvision_password').value,
            port: document.getElementById('hikvision_port').value,
            timeout: document.getElementById('hikvision_timeout').value,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<i class="fas fa-check-circle text-success"></i> ' + data.message;
            result.className = 'ms-2 small text-success';
        } else {
            result.innerHTML = '<i class="fas fa-times-circle text-danger"></i> ' + data.message;
            result.className = 'ms-2 small text-danger';
        }
    })
    .catch(err => {
        console.error('Hikvision test failed:', err);
        result.innerHTML = '<i class="fas fa-times-circle text-danger"></i> ' + err.message;
        result.className = 'ms-2 small text-danger';
    })
    .finally(() => {
        btn.disabled = false;
    });
}
</script>
@endsection
