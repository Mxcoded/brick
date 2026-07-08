@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles & Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($role->name) }}</li>
@endsection

@section('page-content')
<div class="container-fluid">

    @php
        $checkedCount = 0;
        $totalCount = 0;
        foreach($permissionGroups as $groupPerms) {
            foreach($groupPerms as $p) {
                $totalCount++;
                if ($role->hasPermissionTo($p->name)) $checkedCount++;
            }
        }
        $percentage = $totalCount > 0 ? round(($checkedCount / $totalCount) * 100) : 0;
    @endphp

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="avatar-circle-lg 
                @if($role->name === 'admin') bg-danger
                @elseif($role->name === 'guest') bg-info
                @else bg-primary
                @endif text-white">
                @if($role->name === 'admin')
                    <i class="fas fa-crown"></i>
                @elseif($role->name === 'guest')
                    <i class="fas fa-user"></i>
                @else
                    {{ strtoupper(substr($role->name, 0, 2)) }}
                @endif
            </span>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 mb-0 fw-bold">{{ ucfirst($role->name) }}</h1>
                    @if(in_array($role->name, ['admin', 'guest']))
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 0.65rem;">SYSTEM</span>
                    @endif
                </div>
                <p class="text-muted mb-0 small">
                    <span><i class="fas fa-key me-1"></i>{{ $checkedCount }}/{{ $totalCount }} permissions</span>
                    <span class="mx-2">|</span>
                    <span><i class="fas fa-users me-1"></i>{{ $role->users->count() }} user{{ $role->users->count() !== 1 ? 's' : '' }} assigned</span>
                </p>
            </div>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-4 border-success mb-4 py-2">{{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-4 border-danger mb-4 py-2">
            <i class="fas fa-exclamation-circle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}" id="roleForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Sidebar --}}
            <div class="col-lg-3">
                {{-- Role Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold small text-uppercase text-secondary mb-3"><i class="fas fa-user-tag me-2"></i>Role Details</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Role Name</label>
                            <input type="text" name="name" class="form-control form-control-lg" 
                                   value="{{ old('name', $role->name) }}" required
                                   {{ $role->name === 'admin' ? 'readonly' : '' }}>
                            @if($role->name === 'admin')
                                <div class="form-text text-warning"><i class="fas fa-lock me-1"></i>System role name cannot be changed</div>
                            @else
                                <div class="form-text">Lowercase, no spaces</div>
                            @endif
                        </div>

                        {{-- Progress Card --}}
                        <div class="text-center py-3 mb-3" style="background: #f8f9fa; border-radius: 10px;">
                            <div class="position-relative d-inline-block mb-2">
                                <svg width="100" height="100" viewBox="0 0 120 120">
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#e9ecef" stroke-width="12"/>
                                    <circle cx="60" cy="60" r="54" fill="none" 
                                            stroke="{{ $percentage >= 75 ? '#198754' : ($percentage >= 40 ? '#ffc107' : '#dc3545') }}" 
                                            stroke-width="12"
                                            stroke-dasharray="339.292" 
                                            stroke-dashoffset="{{ 339.292 - (339.292 * $percentage / 100) }}"
                                            transform="rotate(-90 60 60)"
                                            class="progress-ring transition-all"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <h4 class="mb-0 fw-bold" id="percentageDisplay">{{ $percentage }}%</h4>
                                    <small class="text-muted" style="font-size: 0.65rem;">Access</small>
                                </div>
                            </div>
                            <p class="mb-0 small text-muted" id="permissionCount">{{ $checkedCount }} of {{ $totalCount }} permissions</p>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="saveBtn">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold small text-uppercase text-secondary mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm" id="selectAllPermissions">
                                <i class="fas fa-check-double me-2"></i>Grant All
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="deselectAllPermissions">
                                <i class="fas fa-times me-2"></i>Revoke All
                            </button>
                        </div>
                        <hr class="my-3">
                        <p class="small fw-semibold text-muted mb-2"><i class="fas fa-magic me-1"></i>Templates</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm" id="selectDashboardOnly">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard Only
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="selectViewOnly">
                                <i class="fas fa-eye me-1"></i>View-Only Access
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Permissions Matrix --}}
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <h5 class="fw-bold mb-0"><i class="fas fa-shield-alt me-2 text-warning"></i>Permissions Matrix</h5>
                            <div class="d-flex align-items-center gap-3">
                                <div class="input-group input-group-sm" style="max-width: 240px;">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control" id="permissionSearch" placeholder="Search permissions...">
                                    <span class="input-group-text bg-white text-muted small d-none d-md-flex" id="searchCount" style="font-size: 0.65rem;">0/0</span>
                                </div>
                                <span class="badge bg-light text-dark px-3 py-2" id="selectedBadge">{{ $checkedCount }} selected</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        {{-- Progress Bar --}}
                        <div class="px-4 pt-4">
                            <div class="progress" style="height: 6px; background: #e9ecef;">
                                <div class="progress-bar {{ $percentage >= 75 ? 'bg-success' : ($percentage >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                     id="progressBar" style="width: {{ $percentage }}%; transition: width 0.3s ease;"></div>
                            </div>
                        </div>

                        {{-- Permission Tabs --}}
                        <div class="p-4">
                            <ul class="nav nav-pills gap-1 mb-4 flex-wrap" id="permissionTabs" role="tablist">
                                @foreach($permissionGroups as $groupName => $groupPerms)
                                    @php
                                        $groupChecked = collect($groupPerms)->filter(fn($p) => $role->hasPermissionTo($p->name))->count();
                                        $groupTotal = count($groupPerms);
                                    @endphp
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link pill-tab {{ $loop->first ? 'active' : '' }}" 
                                                id="tab-{{ Str::slug($groupName) }}" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#content-{{ Str::slug($groupName) }}" 
                                                type="button" role="tab">
                                            {{ $groupName }}
                                            <span class="badge ms-1 group-badge" 
                                                  data-group="{{ Str::slug($groupName) }}"
                                                  style="background: {{ $groupChecked === $groupTotal ? 'rgba(25,135,84,0.15)' : ($groupChecked > 0 ? 'rgba(255,193,7,0.15)' : 'rgba(108,117,125,0.15)') }}; 
                                                         color: {{ $groupChecked === $groupTotal ? '#198754' : ($groupChecked > 0 ? '#856404' : '#6c757d') }};">
                                                {{ $groupChecked }}/{{ $groupTotal }}
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content" id="permissionTabsContent">
                                @foreach($permissionGroups as $groupName => $groupPerms)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                         id="content-{{ Str::slug($groupName) }}" 
                                         role="tabpanel" 
                                         data-group="{{ Str::slug($groupName) }}">
                                        
                                        {{-- Group Header --}}
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-secondary">
                                                    <i class="fas fa-folder-open me-2"></i>{{ $groupName }}
                                                </h6>
                                                <small class="text-muted">{{ count($groupPerms) }} permission{{ count($groupPerms) !== 1 ? 's' : '' }} in this group</small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success select-group" data-group="{{ Str::slug($groupName) }}">
                                                    <i class="fas fa-check-double me-1"></i>Select All
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary deselect-group" data-group="{{ Str::slug($groupName) }}">
                                                    <i class="fas fa-times me-1"></i>Clear
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Permission Cards --}}
                                        <div class="row g-2">
                                            @foreach($groupPerms as $permission)
                                                @php
                                                    $parts = str_contains($permission->name, '.') ? explode('.', $permission->name) : explode('_', $permission->name);
                                                    $action = end($parts);
                                                    $label = ucfirst(str_replace('_', ' ', $action));
                                                    $isChecked = $role->hasPermissionTo($permission->name);
                                                    $actionColors = [
                                                        'access' => ['bg' => '#0d6efd', 'label' => 'Access'],
                                                        'view' => ['bg' => '#0dcaf0', 'label' => 'View'],
                                                        'create' => ['bg' => '#198754', 'label' => 'Create'],
                                                        'edit' => ['bg' => '#ffc107', 'label' => 'Edit'],
                                                        'update' => ['bg' => '#ffc107', 'label' => 'Update'],
                                                        'delete' => ['bg' => '#dc3545', 'label' => 'Delete'],
                                                        'manage' => ['bg' => '#6f42c1', 'label' => 'Manage'],
                                                        'export' => ['bg' => '#6c757d', 'label' => 'Export'],
                                                        'import' => ['bg' => '#20c997', 'label' => 'Import'],
                                                        'approve' => ['bg' => '#fd7e14', 'label' => 'Approve'],
                                                    ];
                                                    $ac = $actionColors[$action] ?? ['bg' => '#6c757d', 'label' => ucfirst($action)];
                                                @endphp
                                                <div class="col-xl-4 col-lg-6 permission-item" data-permission="{{ strtolower($permission->name) }}">
                                                    <div class="perm-card {{ $isChecked ? 'active' : '' }}" data-group="{{ Str::slug($groupName) }}">
                                                        <label class="d-flex align-items-center p-3 cursor-pointer w-100 h-100 mb-0">
                                                            <div class="form-check form-switch me-3 mb-0">
                                                                <input class="form-check-input perm-check" type="checkbox" role="switch"
                                                                       name="permissions[]" value="{{ $permission->name }}"
                                                                       id="perm_{{ $permission->id }}"
                                                                       data-group="{{ Str::slug($groupName) }}"
                                                                       {{ $isChecked ? 'checked' : '' }}>
                                                            </div>
                                                            <div class="flex-grow-1 min-width-0">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <span class="badge fw-semibold" 
                                                                          style="background: {{ $ac['bg'] }}; font-size: 0.6rem; letter-spacing: 0.3px;">
                                                                        {{ $ac['label'] }}
                                                                    </span>
                                                                    <span class="fw-semibold small text-truncate">{{ $label }}</span>
                                                                </div>
                                                                <code class="text-muted d-block text-truncate" style="font-size: 0.6rem;">{{ $permission->name }}</code>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-white border-top px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Changes are saved only when you click "Save Changes"
                                <span class="d-none d-md-inline ms-2 text-secondary">| <kbd class="bg-light px-1" style="font-size: 0.6rem;">Ctrl+S</kbd> to save</span>
                            </small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
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
    .avatar-circle-lg {
        width: 56px; height: 56px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.35rem; font-weight: bold; flex-shrink: 0;
    }
    .perm-card {
        border: 2px solid #e9ecef; border-radius: 10px;
        background: #fff; transition: all 0.2s ease; height: 100%;
    }
    .perm-card:hover {
        border-color: #C8A165; box-shadow: 0 2px 8px rgba(200, 161, 101, 0.12);
    }
    .perm-card.active {
        border-color: #198754;
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.04) 0%, rgba(25, 135, 84, 0.08) 100%);
    }
    .perm-card.active:hover { border-color: #198754; }
    .perm-card label { cursor: pointer; }
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.3s ease; }
    .min-width-0 { min-width: 0; }

    .pill-tab {
        color: #6c757d; border-radius: 20px !important;
        padding: 0.35rem 1rem; font-size: 0.8rem; font-weight: 500;
        border: 1px solid transparent; transition: all 0.2s ease;
    }
    .pill-tab:hover:not(.active) {
        border-color: #dee2e6; background: #f8f9fa;
    }
    .pill-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: #fff !important; box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    }
    .nav-pills .nav-link { margin-bottom: 0; }

    .form-check-input:checked { background-color: #198754; border-color: #198754; }

    .permission-item.hidden { display: none !important; }

    #permissionSearch:focus { box-shadow: none; border-color: #C8A165; }
    .progress-ring { transition: stroke-dashoffset 0.5s ease; }
</style>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalPermissions = {{ $totalCount }};

    function updateUI() {
        const all = document.querySelectorAll('.perm-check');
        const checked = document.querySelectorAll('.perm-check:checked');
        const count = checked.length;
        const percentage = totalPermissions > 0 ? Math.round((count / totalPermissions) * 100) : 0;

        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.className = 'progress-bar ' + (percentage >= 75 ? 'bg-success' : (percentage >= 40 ? 'bg-warning' : 'bg-danger'));
        }

        const selectedBadge = document.getElementById('selectedBadge');
        if (selectedBadge) selectedBadge.textContent = count + ' selected';

        document.getElementById('percentageDisplay').textContent = percentage + '%';
        document.getElementById('permissionCount').textContent = count + ' of ' + totalPermissions + ' permissions';

        const ring = document.querySelector('.progress-ring');
        if (ring) {
            const circ = 339.292;
            ring.style.strokeDashoffset = circ - (circ * percentage / 100);
            ring.setAttribute('stroke', percentage >= 75 ? '#198754' : (percentage >= 40 ? '#ffc107' : '#dc3545'));
        }

        document.querySelectorAll('.tab-pane').forEach(pane => {
            const group = pane.dataset.group;
            const gChecks = pane.querySelectorAll('.perm-check');
            const gChecked = pane.querySelectorAll('.perm-check:checked').length;
            const gTotal = gChecks.length;
            const badge = document.querySelector(`.group-badge[data-group="${group}"]`);
            if (badge) {
                badge.textContent = gChecked + '/' + gTotal;
                badge.style.background = gChecked === gTotal ? 'rgba(25,135,84,0.15)' : (gChecked > 0 ? 'rgba(255,193,7,0.15)' : 'rgba(108,117,125,0.15)');
                badge.style.color = gChecked === gTotal ? '#198754' : (gChecked > 0 ? '#856404' : '#6c757d');
            }
        });

        document.querySelectorAll('.perm-card').forEach(card => {
            const cb = card.querySelector('.perm-check');
            card.classList.toggle('active', cb?.checked);
        });
    }

    document.querySelectorAll('.perm-check').forEach(cb => cb.addEventListener('change', updateUI));

    document.querySelectorAll('.select-group').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll(`.perm-check[data-group="${this.dataset.group}"]`).forEach(c => c.checked = true);
            updateUI();
        });
    });
    document.querySelectorAll('.deselect-group').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll(`.perm-check[data-group="${this.dataset.group}"]`).forEach(c => c.checked = false);
            updateUI();
        });
    });

    document.getElementById('selectAllPermissions')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = true);
        updateUI();
    });
    document.getElementById('deselectAllPermissions')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
        updateUI();
    });
    document.getElementById('selectDashboardOnly')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(c => {
            c.checked = c.value.includes('dashboard') || c.value.startsWith('access_');
        });
        updateUI();
    });
    document.getElementById('selectViewOnly')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(c => {
            c.checked = c.value.includes('view') || c.value.includes('access') || c.value.includes('read');
        });
        updateUI();
    });

    // Search
    const searchInput = document.getElementById('permissionSearch');
    const searchCount = document.getElementById('searchCount');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            let visible = 0, total = 0;
            document.querySelectorAll('.permission-item').forEach(item => {
                const match = item.dataset.permission?.includes(q) ?? false;
                item.classList.toggle('hidden', !match && q !== '');
                if (match || q === '') visible++;
                total++;
            });
            if (searchCount) searchCount.textContent = (q ? visible : total) + '/' + total;
        });
    }

    // Click on card toggles switch
    document.querySelectorAll('.perm-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const cb = this.querySelector('.perm-check');
                if (cb) { cb.checked = !cb.checked; updateUI(); }
            }
        });
    });

    // Ctrl+S to save
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('saveBtn')?.click();
        }
    });
});
</script>
@endsection
