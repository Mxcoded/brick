@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Roles & Permissions</li>
@endsection

@section('page-content')
<div class="container-fluid">

    @php
        $permGroupsCollection = collect($permissionGroups);
        $totalPermissions = $permGroupsCollection->flatten()->count();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-user-shield me-2" style="color: #C8A165;"></i>Roles & Permissions</h1>
            <p class="text-muted mb-0">Manage user roles and their access permissions across the system</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#createRoleCollapse">
            <i class="fas fa-plus me-2"></i>Create Role
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="x-small text-muted text-uppercase mb-0">Total Roles</p>
                            <h4 class="fw-bold mb-0">{{ $roles->count() }}</h4>
                        </div>
                        <div class="opacity-25">
                            <i class="fas fa-user-tag fa-2x" style="color: #667eea;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #11998e !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="x-small text-muted text-uppercase mb-0">Total Permissions</p>
                            <h4 class="fw-bold mb-0">{{ $totalPermissions }}</h4>
                        </div>
                        <div class="opacity-25">
                            <i class="fas fa-key fa-2x" style="color: #11998e;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f093fb !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="x-small text-muted text-uppercase mb-0">Permission Groups</p>
                            <h4 class="fw-bold mb-0">{{ count($permissionGroups) }}</h4>
                        </div>
                        <div class="opacity-25">
                            <i class="fas fa-layer-group fa-2x" style="color: #f093fb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #4facfe !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="x-small text-muted text-uppercase mb-0">Users Assigned</p>
                            <h4 class="fw-bold mb-0">{{ $roles->sum(fn($r) => $r->users->count()) }}</h4>
                        </div>
                        <div class="opacity-25">
                            <i class="fas fa-users fa-2x" style="color: #4facfe;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-4 border-success mb-4 py-2">{{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-start border-4 border-info mb-4 py-2">{{ session('info') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Create Role Card --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 cursor-pointer" 
             role="button" data-bs-toggle="collapse" data-bs-target="#createRoleCollapse" aria-expanded="false">
            <h5 class="mb-0 fw-bold" style="color: #C8A165;">
                <i class="fas fa-plus-circle me-2"></i>Create New Role
            </h5>
            <i class="fas fa-chevron-down text-muted collapse-icon transition-rotate"></i>
        </div>
        <div class="collapse" id="createRoleCollapse">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.roles.store') }}" id="createRoleForm">
                    @csrf

                    <div class="row mb-4 align-items-end g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-uppercase text-muted"><i class="fas fa-tag me-1"></i>Role Name</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. supervisor, manager" required>
                            <div class="form-text">Use lowercase, no spaces (underscores allowed)</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted"><i class="fas fa-check-circle me-1"></i>Quick Select</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-success" id="createSelectAll">
                                    <i class="fas fa-check-double me-1"></i>All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="createSelectNone">
                                    <i class="fas fa-times me-1"></i>None
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" id="createSelectDashboards">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboards
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="fas fa-save me-2"></i>Create Role
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>Assign Permissions
                        </h6>
                        <span class="badge bg-light text-dark px-3 py-2" id="createSelectedCount">0 selected</span>
                    </div>

                    <div class="row g-3">
                        @foreach($permissionGroups as $groupName => $groupPerms)
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card h-100 border permission-group-card">
                                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small text-uppercase text-secondary">
                                            <i class="fas fa-folder me-1"></i>{{ $groupName }}
                                        </span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary py-0 px-2 select-group" title="Select All">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2 deselect-group" title="Deselect All">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body py-2 scrollbar-thin" style="max-height: 220px; overflow-y: auto;">
                                        @foreach($groupPerms as $permission)
                                            @php
                                                $parts = str_contains($permission->name, '.') ? explode('.', $permission->name) : explode('_', $permission->name);
                                                $action = end($parts);
                                                $action = $action === 'manage' ? '' : $action;
                                                $label = $action ? ucfirst(str_replace('_', ' ', $action)) : $permission->name;
                                            @endphp
                                            <div class="form-check mb-1 py-1 px-2 rounded hover-bg-light transition-fast">
                                                <input class="form-check-input perm-checkbox" type="checkbox" 
                                                       name="permissions[]" value="{{ $permission->name }}" 
                                                       id="create_perm_{{ $permission->id }}">
                                                <label class="form-check-label small w-100 cursor-pointer" for="create_perm_{{ $permission->id }}">
                                                    <span class="d-block">{{ $label }}</span>
                                                    <code class="text-muted" style="font-size: 0.6rem;">{{ $permission->name }}</code>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer bg-white py-1 px-3 text-end">
                                        <small class="text-muted">{{ count($groupPerms) }} perm{{ count($groupPerms) !== 1 ? 's' : '' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Roles List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-list me-2 text-secondary"></i>Existing Roles</h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="roleSearch" placeholder="Search roles...">
                    </div>
                    <span class="badge bg-light text-dark px-3 py-2 d-none" id="selectedRolesCount">0 selected</span>
                    <button type="submit" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn" 
                            form="bulkDeleteForm"
                            onclick="return confirm('Delete selected roles? This action cannot be undone.')">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.roles.mass_destroy') }}" method="POST" id="bulkDeleteForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input class="form-check-input" type="checkbox" id="selectAll" title="Select All">
                            </th>
                            <th>Role</th>
                            <th class="text-center" style="width: 180px;">Permissions</th>
                            <th class="text-center" style="width: 100px;">Users</th>
                            <th class="text-end pe-4" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr class="role-row align-middle">
                                <td class="ps-4">
                                    @if(!in_array($role->name, ['admin']))
                                        <input class="form-check-input role-checkbox" type="checkbox" name="ids[]" value="{{ $role->id }}">
                                    @else
                                        <i class="fas fa-lock text-muted" title="System role"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($role->name === 'admin')
                                                <span class="avatar-circle bg-danger text-white"><i class="fas fa-crown"></i></span>
                                            @elseif($role->name === 'guest')
                                                <span class="avatar-circle bg-info text-white"><i class="fas fa-user"></i></span>
                                            @else
                                                <span class="avatar-circle bg-primary text-white">{{ strtoupper(substr($role->name, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ ucfirst($role->name) }}
                                                @if(in_array($role->name, ['admin', 'guest']))
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2" style="font-size: 0.6rem; vertical-align: middle;">SYSTEM</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $role->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @php 
                                            $permPercent = $totalPermissions > 0 ? ($role->permissions->count() / $totalPermissions) * 100 : 0;
                                            $barColor = $permPercent >= 75 ? 'bg-success' : ($permPercent >= 40 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 120px; background: #e9ecef;">
                                            <div class="progress-bar {{ $barColor }}" style="width: {{ $permPercent }}%"></div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-2">{{ $role->permissions->count() }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 {{ $role->users->count() > 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                        <i class="fas fa-user me-1"></i>{{ $role->users->count() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Configure Permissions">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        @if(!in_array($role->name, ['admin']))
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $role->id }}, '{{ $role->name }}')" 
                                                    title="Delete Role">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-user-tag fa-4x mb-3 d-block" style="color: #C8A165; opacity: 0.3;"></i>
                                        <p class="fw-semibold mb-1">No roles found</p>
                                        <p class="text-muted small mb-0">Create your first role above to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt fa-lg text-danger"></i>
                    </span>
                </div>
                <h5 class="fw-bold mb-2">Delete Role</h5>
                <p class="mb-1">Are you sure you want to delete <strong id="deleteRoleName" class="text-danger"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-circle {
        width: 40px; height: 40px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 0.85rem;
    }
    .permission-group-card { transition: all 0.2s ease; }
    .permission-group-card:hover {
        border-color: #C8A165 !important;
        box-shadow: 0 0.25rem 0.5rem rgba(200, 161, 101, 0.08);
    }
    .role-row { transition: background 0.15s ease; }
    .role-row:hover { background: rgba(200, 161, 101, 0.03); }
    .cursor-pointer { cursor: pointer; }
    .transition-rotate { transition: transform 0.3s ease; }
    [aria-expanded="true"] .transition-rotate { transform: rotate(180deg); }
    .hover-bg-light:hover { background: rgba(0,0,0,0.02); }
    .transition-fast { transition: background 0.15s ease; }
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: #f1f1f1; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #aaa; }
    .role-row.hidden { display: none !important; }
</style>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.role-checkbox');
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedRolesCount');

    function toggleDeleteBtn() {
        const checked = Array.from(checkboxes).filter(c => c.checked);
        const count = checked.length;
        deleteBtn.classList.toggle('d-none', count === 0);
        selectedCount.classList.toggle('d-none', count === 0);
        selectedCount.textContent = count + ' selected';
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(c => c.checked = selectAll.checked);
        toggleDeleteBtn();
    });

    checkboxes.forEach(c => c.addEventListener('change', toggleDeleteBtn));

    // Role search
    const roleSearch = document.getElementById('roleSearch');
    roleSearch?.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.role-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.classList.toggle('hidden', !text.includes(q) && q !== '');
        });
    });

    // Permission checkboxes count
    const permCheckboxes = document.querySelectorAll('.perm-checkbox');
    const createSelectedCount = document.getElementById('createSelectedCount');
    function updateCreateCount() {
        const count = document.querySelectorAll('.perm-checkbox:checked').length;
        createSelectedCount.textContent = count + ' selected';
    }
    permCheckboxes.forEach(c => c.addEventListener('change', updateCreateCount));

    // Group select/deselect
    document.querySelectorAll('.select-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.permission-group-card').querySelectorAll('.perm-checkbox').forEach(c => c.checked = true);
            updateCreateCount();
        });
    });
    document.querySelectorAll('.deselect-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.permission-group-card').querySelectorAll('.perm-checkbox').forEach(c => c.checked = false);
            updateCreateCount();
        });
    });

    // Quick select buttons
    document.getElementById('createSelectAll')?.addEventListener('click', () => {
        permCheckboxes.forEach(c => c.checked = true);
        updateCreateCount();
    });
    document.getElementById('createSelectNone')?.addEventListener('click', () => {
        permCheckboxes.forEach(c => c.checked = false);
        updateCreateCount();
    });
    document.getElementById('createSelectDashboards')?.addEventListener('click', () => {
        permCheckboxes.forEach(c => {
            c.checked = c.value.includes('dashboard') || c.value.includes('access');
        });
        updateCreateCount();
    });

    // Click row toggles checkbox
    document.querySelectorAll('.role-row').forEach(row => {
        row.addEventListener('click', function(e) {
            const cb = this.querySelector('.role-checkbox');
            if (cb && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
                cb.checked = !cb.checked;
                toggleDeleteBtn();
            }
        });
    });
});

function confirmDelete(roleId, roleName) {
    document.getElementById('deleteRoleName').textContent = roleName;
    document.getElementById('deleteForm').action = '{{ url("admin/roles") }}/' + roleId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
