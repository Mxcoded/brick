@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Permission</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key me-2 text-gold"></i> Permission:
            <code>{{ $permission->name }}</code>
        </h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Rename Permission --}}
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i> Rename Permission</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.update', $permission->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Permission Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Assign Roles --}}
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-link me-2 text-success"></i> Role Assignments</h5>
                    <span class="badge bg-info">{{ $permission->roles->count() }} roles</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.update-roles', $permission->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Assign to Roles</label>
                            <div class="row">
                                @foreach ($roles as $role)
                                    <div class="col-lg-4 col-md-6 mb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->id }}"
                                                id="role_{{ $role->id }}"
                                                {{ $permission->roles->contains($role->id) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="role_{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.querySelectorAll('[name^=\'roles\']').forEach(c => c.checked = true)">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('[name^=\'roles\']').forEach(c => c.checked = false)">Deselect All</button>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sync me-2"></i> Update Assignments
                        </button>
                    </form>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Deleting this permission will remove it from all roles. This action cannot be undone.</p>
                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this permission? This will remove it from all roles.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Delete Permission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
