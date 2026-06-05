@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection

@section('page-content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Roles</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Create Role Card — Collapsible with Permission Matrix --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#createRoleCollapse" aria-expanded="false">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i> Create New Role</h5>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div class="collapse" id="createRoleCollapse">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" id="createRoleForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. supervisor" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i> Create Role with Permissions
                            </button>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3">Assign Permissions <small class="text-muted fw-normal">(check the permissions this role should have)</small></h6>

                    <div class="row">
                        @foreach($permissionGroups as $groupName => $groupPerms)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0 text-secondary text-uppercase small">{{ $groupName }}</h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1 select-group" style="font-size: 0.65rem;">All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 deselect-group" style="font-size: 0.65rem;">None</button>
                                        </div>
                                    </div>
                                    <div class="border-top pt-2">
                                        @foreach($groupPerms as $permission)
                                            @php
                                                $parts = str_contains($permission->name, '.') ? explode('.', $permission->name) : explode('_', $permission->name);
                                                $action = end($parts);
                                                $action = $action === 'manage' ? '' : $action;
                                                $label = $action ? ucfirst(str_replace('_', ' ', $action)) : $permission->name;
                                            @endphp
                                            <div class="form-check form-check-inline mb-1" style="min-width: 120px;">
                                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="create_perm_{{ $permission->id }}">
                                                <label class="form-check-label small" for="create_perm_{{ $permission->id }}">
                                                    {{ $label }}
                                                    <br><small class="text-muted" style="font-size: 0.6rem;">{{ $permission->name }}</small>
                                                </label>
                                            </div>
                                        @endforeach
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
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.roles.mass_destroy') }}" method="POST" id="bulkDeleteForm">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title m-0">Existing Roles</h5>
                    <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;" onclick="return confirm('Delete selected roles? This cannot be undone.')">
                        <i class="fas fa-trash me-1"></i> Delete Selected
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th>Role Name</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>
                                        <input class="form-check-input role-checkbox" type="checkbox" name="ids[]" value="{{ $role->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">{{ $role->permissions->count() }}</span>
                                        <small class="text-muted ms-1">permissions</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary rounded-pill">{{ $role->users->count() }}</span>
                                        <small class="text-muted ms-1">users</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Configure
                                        </a>
                                        @if(!in_array($role->name, ['admin']))
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All logic
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.role-checkbox');
    const deleteBtn = document.getElementById('bulkDeleteBtn');

    function toggleDeleteBtn() {
        const anyChecked = Array.from(checkboxes).some(c => c.checked);
        deleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(c => c.checked = selectAll.checked);
            toggleDeleteBtn();
        });
    }

    checkboxes.forEach(c => {
        c.addEventListener('change', toggleDeleteBtn);
    });

    // Per-group Select All / Deselect
    document.querySelectorAll('.select-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.border.rounded').querySelectorAll('.perm-checkbox').forEach(c => c.checked = true);
        });
    });
    document.querySelectorAll('.deselect-group').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.border.rounded').querySelectorAll('.perm-checkbox').forEach(c => c.checked = false);
        });
    });
});
</script>
@endsection
