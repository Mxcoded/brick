@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tag me-2 text-warning"></i> Role:
            <span class="text-primary">{{ $role->name }}</span>
            <small class="text-muted fs-6 ms-2">({{ $role->permissions->count() }} permissions assigned)</small>
        </h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left: Role Info --}}
            <div class="col-lg-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 fw-bold">Role Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small">Assigned Users: <strong>{{ $role->users->count() }}</strong></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="m-0 fw-bold">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-sm btn-outline-success w-100 mb-2" id="selectAllPermissions">
                            <i class="fas fa-check-double me-1"></i> Grant All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 mb-2" id="deselectAllPermissions">
                            <i class="fas fa-times me-1"></i> Revoke All
                        </button>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted">Scope:</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info w-100 mb-1" id="selectDashboardOnly">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard Access Only
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right: Permissions Matrix --}}
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fas fa-shield-alt me-2 text-gold"></i> Permissions Matrix
                        </h6>
                        <small class="text-muted">{{ $permissions->count() }} total permissions</small>
                    </div>
                    <div class="card-body">
                        @php
                            $checkedCount = 0;
                            $totalCount = 0;
                            foreach($permissionGroups as $groupPerms) {
                                foreach($groupPerms as $p) {
                                    $totalCount++;
                                    if ($role->hasPermissionTo($p->name)) $checkedCount++;
                                }
                            }
                        @endphp
                        <div class="progress mb-4" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $totalCount > 0 ? ($checkedCount / $totalCount) * 100 : 0 }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <small class="text-muted">{{ $checkedCount }} / {{ $totalCount }} permissions selected</small>
                        </div>

                        <div class="accordion" id="permAccordion">
                            @foreach($permissionGroups as $groupName => $groupPerms)
                                <div class="accordion-item border-0 mb-2">
                                    <h2 class="accordion-header" id="heading_{{ Str::slug($groupName) }}">
                                        <button class="accordion-button bg-light {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse_{{ Str::slug($groupName) }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <span class="fw-bold small text-uppercase">{{ $groupName }}</span>
                                                <span class="badge bg-secondary rounded-pill ms-2">{{ count($groupPerms) }}</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse_{{ Str::slug($groupName) }}"
                                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                        data-bs-parent="#permAccordion">
                                        <div class="accordion-body pt-3">
                                            <div class="d-flex gap-2 mb-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 select-group" style="font-size: 0.7rem;">
                                                    <i class="fas fa-check me-1"></i>Select All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 deselect-group" style="font-size: 0.7rem;">
                                                    <i class="fas fa-times me-1"></i>Deselect All
                                                </button>
                                            </div>
                                            <div class="row">
                                                @foreach($groupPerms as $permission)
                                                    @php
                                                        $parts = str_contains($permission->name, '.') ? explode('.', $permission->name) : explode('_', $permission->name);
                                                        $action = end($parts);
                                                        $label = ucfirst(str_replace('_', ' ', $action));
                                                        $isChecked = $role->hasPermissionTo($permission->name);
                                                    @endphp
                                                    <div class="col-lg-4 col-md-6 mb-1">
                                                        <div class="form-check">
                                                            <input class="form-check-input perm-check" type="checkbox"
                                                                name="permissions[]" value="{{ $permission->name }}"
                                                                id="perm_{{ $permission->id }}"
                                                                {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                                                {{ $label }}
                                                                <br><small class="text-muted" style="font-size: 0.6rem;">{{ $permission->name }}</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Per-group Select All / Deselect
    document.querySelectorAll('.select-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.accordion-body').querySelectorAll('.perm-check').forEach(c => c.checked = true);
            updateProgress();
        });
    });
    document.querySelectorAll('.deselect-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.accordion-body').querySelectorAll('.perm-check').forEach(c => c.checked = false);
            updateProgress();
        });
    });

    // All checkboxes update progress
    document.querySelectorAll('.perm-check').forEach(c => {
        c.addEventListener('change', updateProgress);
    });

    // Quick actions
    document.getElementById('selectAllPermissions')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = true);
        updateProgress();
    });
    document.getElementById('deselectAllPermissions')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
        updateProgress();
    });
    document.getElementById('selectDashboardOnly')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-check').forEach(c => {
            c.checked = c.value.startsWith('access_') && c.value.endsWith('_dashboard');
        });
        updateProgress();
    });

    function updateProgress() {
        const all = document.querySelectorAll('.perm-check');
        const checked = document.querySelectorAll('.perm-check:checked');
        const total = all.length;
        const count = checked.length;
        const bar = document.querySelector('.progress-bar');
        if (bar) {
            bar.style.width = total > 0 ? (count / total * 100) + '%' : '0%';
        }
        const info = document.querySelector('.text-muted small');
        // Update the text
        const progressText = document.querySelector('.d-flex.justify-content-between.mb-3 small');
        if (progressText) {
            progressText.textContent = count + ' / ' + total + ' permissions selected';
        }
    }
});
</script>
@endsection
