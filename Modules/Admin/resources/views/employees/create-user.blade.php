@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Create User Account</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-user-plus me-2" style="color: #C8A165;"></i>Create User Account</h2>
                <p class="text-muted mb-0">Link an employee to a staff portal account with role-based access</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.employees.store-user') }}">
            @csrf

            <div class="row g-4">
                {{-- Left Column: Employee Selection --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                            <h5 class="fw-bold mb-0"><i class="fas fa-user-tie me-2" style="color: #C8A165;"></i>Select Employee</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="fas fa-search position-absolute text-muted" style="left: 14px; top: 13px; z-index: 5; font-size: 0.85rem;"></i>
                                    <input type="text" class="form-control ps-5" id="employeeSearch" placeholder="Type to search employees..." autocomplete="off">
                                </div>
                                <select name="employee_id" id="employee_id" class="form-select mt-2" required size="6">
                                    <option value="" disabled selected>— Select Employee —</option>
                                    @foreach ($employees as $employee)
                                        @php $code = $employee->staff_code ? ' ('.$employee->staff_code.')' : ''; $label = $employee->name ? $employee->name.$code : ($employee->email ? $employee->email.$code : 'Staff #'.$employee->id); @endphp
                                        <option value="{{ $employee->id }}"
                                            data-search="{{ strtolower($label.' '.$employee->email.' '.($employee->position ?? '').' '.($employee->department ?? '').' '.($employee->staff_code ?? '')) }}"
                                            data-email="{{ $employee->email }}"
                                            data-position="{{ $employee->position ?? 'N/A' }}"
                                            data-department="{{ $employee->department ?? 'N/A' }}"
                                            data-phone="{{ $employee->phone_number ?? 'N/A' }}"
                                            {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-1">Only employees without existing user accounts are shown.</div>
                            </div>

                            {{-- Employee Preview Card --}}
                            <div id="employeePreview" class="d-none">
                                <div class="bg-light rounded-3 p-3 mt-2">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="rounded-circle bg-gold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                            <i class="fas fa-user text-white" style="font-size: 1.2rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0" id="previewName">—</h6>
                                            <span class="small text-muted" id="previewPosition">—</span>
                                        </div>
                                    </div>
                                    <div class="small mt-2 pt-2 border-top border-secondary">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Department</span>
                                            <span class="fw-medium" id="previewDepartment">—</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Email</span>
                                            <span class="fw-medium" id="previewEmail">—</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Phone</span>
                                            <span class="fw-medium" id="previewPhone">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Account Details --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                            <h5 class="fw-bold mb-0"><i class="fas fa-shield-alt me-2" style="color: #C8A165;"></i>Account Settings</h5>
                        </div>
                        <div class="card-body">
                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="employee@brickspoint.com">
                                </div>
                                <div class="form-text d-flex align-items-center gap-1" id="emailHelp">
                                    <i class="fas fa-sync-alt text-gold" style="font-size: 0.7rem;"></i>
                                    Auto-filled from selected employee. You can edit if needed.
                                </div>
                            </div>

                            {{-- Password & Confirm --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password" id="password" class="form-control" required minlength="8" placeholder="Min. 8 characters">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide password">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                        <button class="btn btn-outline-gold" type="button" id="generatePassword" title="Generate random password">
                                            <i class="fas fa-dice"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2" id="passwordStrength" style="height: 4px; border-radius: 2px; background: #e0e0e0; overflow: hidden;">
                                        <div id="strengthBar" style="height: 100%; width: 0%; border-radius: 2px; transition: all 0.25s;"></div>
                                    </div>
                                    <div class="form-text" id="strengthText">Enter a password</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-check-circle text-muted"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Re-enter password">
                                    </div>
                                    <div class="form-text" id="confirmMsg"></div>
                                </div>
                            </div>

                            {{-- Role --}}
                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold">Assign Role <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user-tag text-muted"></i></span>
                                    <select name="role" id="role" class="form-select" required>
                                        <option value="">— Select Role —</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-text">Determines what the user can access in the system.</div>
                            </div>

                            {{-- Submit --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-gold btn-lg px-5 flex-grow-1" id="submitBtn">
                                    <i class="fas fa-user-plus me-2" id="submitIcon"></i>
                                    <span id="submitText">Create Account</span>
                                    <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
<style>
    .card { border-radius: 12px; }
    .card-header { padding-left: 1.5rem; padding-right: 1.5rem; }
    .card-body { padding: 1.5rem; }
    .form-control, .form-select, .input-group-text { border-color: #e0e0e0; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .input-group-text { border-right: none; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    .input-group .form-control:focus, .input-group .form-select:focus { box-shadow: none; border-color: #C8A165; }
    .input-group:focus-within .input-group-text { border-color: #C8A165; }
    select option { padding: 6px 10px; }
    #employee_id optgroup, #employee_id option { padding: 6px 10px; }
    #employeeSearch:focus { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .bg-gold { background-color: #C8A165; }
</style>
@endsection

@section('page-scripts')
<script>
(function () {
    // --- Employee search filter ---
    var searchInput = document.getElementById('employeeSearch');
    var select = document.getElementById('employee_id');

    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        Array.from(select.options).forEach(function (opt) {
            if (opt.value === '') return;
            opt.hidden = (opt.dataset.search || opt.text.toLowerCase()).indexOf(q) === -1;
        });
    });

    // --- Employee preview ---
    var preview = document.getElementById('employeePreview');

    function updatePreview(opt) {
        if (!opt || !opt.value) {
            preview.classList.add('d-none');
            return;
        }
        document.getElementById('previewName').textContent = opt.text;
        document.getElementById('previewPosition').textContent = opt.dataset.position || '—';
        document.getElementById('previewDepartment').textContent = opt.dataset.department || '—';
        document.getElementById('previewEmail').textContent = opt.dataset.email || '—';
        document.getElementById('previewPhone').textContent = opt.dataset.phone || '—';
        preview.classList.remove('d-none');
    }

    select.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        updatePreview(opt);
        var emailInput = document.getElementById('email');
        if (opt && opt.dataset.email) {
            emailInput.value = opt.dataset.email;
        }
    });

    // Show preview if preselected (validation error)
    if (select.value) {
        updatePreview(select.options[select.selectedIndex]);
    }

    // --- Password generator ---
    function generatePassword(length) {
        var upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var lower = 'abcdefghijklmnopqrstuvwxyz';
        var digits = '0123456789';
        var special = '!@#$%^&*()';
        var all = upper + lower + digits + special;
        var pass = '';
        pass += upper[Math.floor(Math.random() * upper.length)];
        pass += lower[Math.floor(Math.random() * lower.length)];
        pass += digits[Math.floor(Math.random() * digits.length)];
        pass += special[Math.floor(Math.random() * special.length)];
        for (var i = pass.length; i < length; i++) {
            pass += all[Math.floor(Math.random() * all.length)];
        }
        return pass.split('').sort(function () { return 0.5 - Math.random(); }).join('');
    }

    document.getElementById('generatePassword').addEventListener('click', function () {
        var pwd = generatePassword(16);
        var pwdInput = document.getElementById('password');
        var confInput = document.getElementById('password_confirmation');
        pwdInput.value = pwd;
        confInput.value = pwd;
        checkStrength(pwd);
        checkMatch();
    });

    // --- Toggle password visibility ---
    document.getElementById('togglePassword').addEventListener('click', function () {
        var input = document.getElementById('password');
        var icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // --- Password strength ---
    var strengthBar = document.getElementById('strengthBar');
    var strengthText = document.getElementById('strengthText');

    function checkStrength(pwd) {
        var score = 0;
        if (pwd.length >= 8) score += 25;
        if (pwd.length >= 12) score += 10;
        if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 20;
        if (/\d/.test(pwd)) score += 20;
        if (/[^a-zA-Z0-9]/.test(pwd)) score += 25;

        var colors = ['#dc3545', '#fd7e14', '#ffc107', '#198754', '#198754'];
        var labels = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
        var idx = score >= 100 ? 4 : score >= 75 ? 3 : score >= 50 ? 2 : score >= 25 ? 1 : 0;
        strengthBar.style.width = Math.min(score, 100) + '%';
        strengthBar.style.backgroundColor = colors[idx];
        strengthText.textContent = pwd.length ? labels[idx] : 'Enter a password';
        strengthText.className = 'form-text' + (score >= 50 ? ' text-success' : score >= 25 ? '' : ' text-danger');
    }

    document.getElementById('password').addEventListener('input', function () {
        checkStrength(this.value);
        checkMatch();
    });

    // --- Confirm match ---
    var confirmMsg = document.getElementById('confirmMsg');

    function checkMatch() {
        var pwd = document.getElementById('password').value;
        var conf = document.getElementById('password_confirmation').value;
        if (!conf) {
            confirmMsg.textContent = '';
            confirmMsg.className = 'form-text';
            return;
        }
        if (pwd === conf) {
            confirmMsg.textContent = '✓ Passwords match';
            confirmMsg.className = 'form-text text-success';
        } else {
            confirmMsg.textContent = '✗ Passwords do not match';
            confirmMsg.className = 'form-text text-danger';
        }
    }

    document.getElementById('password_confirmation').addEventListener('input', checkMatch);

    // --- Loading state on submit ---
    document.querySelector('form').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        document.getElementById('submitIcon').classList.add('d-none');
        document.getElementById('submitText').textContent = 'Creating Account...';
        document.getElementById('submitSpinner').classList.remove('d-none');
    });
})();
</script>
@endsection
