@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key me-2 text-gold"></i> Manage Permissions
        </h1>
        <span class="text-muted small">{{ $permissions->count() }} total</span>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        {{-- Create Permission --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i> New Permission</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Permission Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. reports.export" required>
                            <small class="text-muted">Use dot notation: <code>resource.action</code></small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-2"></i> Create Permission
                        </button>
                    </form>
                </div>
            </div>

            {{-- Assign Permission to Role --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2 text-info"></i> Assign to Role</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.assign-to-role') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Permission</label>
                            <select name="permission_id" class="form-select" required>
                                <option value="">Select Permission</option>
                                @foreach ($permissions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info w-100 text-white">
                            <i class="fas fa-link me-2"></i> Assign
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Permissions List --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">All Permissions</h5>
                </div>
                <div class="card-body p-0">
                    @if ($permissions->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No permissions defined yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Permission</th>
                                        <th>Assigned Roles</th>
                                        <th style="width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grouped = $permissions->groupBy(function($p) {
                                            if (str_contains($p->name, '.')) return explode('.', $p->name)[0];
                                            $parts = explode('_', $p->name);
                                            return count($parts) > 1 ? $parts[0] . '_' . $parts[1] : $parts[0];
                                        });
                                    @endphp
                                    @foreach($grouped as $group => $groupPerms)
                                        <tr class="table-secondary">
                                            <td colspan="3">
                                                <strong class="small text-uppercase">{{ $group }}</strong>
                                                <span class="badge bg-secondary ms-2">{{ $groupPerms->count() }}</span>
                                            </td>
                                        </tr>
                                        @foreach($groupPerms as $permission)
                                            <tr>
                                                <td>
                                                    <code>{{ $permission->name }}</code>
                                                </td>
                                                <td>
                                                    @forelse($permission->roles as $assignedRole)
                                                        <span class="badge bg-info me-1">{{ $assignedRole->name }}</span>
                                                    @empty
                                                        <span class="text-muted small">Not assigned</span>
                                                    @endforelse
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this permission?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
