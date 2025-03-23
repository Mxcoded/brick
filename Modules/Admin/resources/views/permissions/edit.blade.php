<!-- Modules/Admin/Resources/views/permissions/edit.blade.php -->
@extends('admin::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Permission</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Permission: {{ $permission->name }}</h1>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Edit Permission Name Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Update Permission Name</h5>
                <form method="POST" action="{{ route('admin.permissions.update', $permission->id) }}" class="d-flex align-items-center gap-3">
                    @csrf
                    @method('PUT')
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name) }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Permission
                    </button>
                </form>
            </div>
        </div>

        <!-- Manage Role Assignments -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Assign Roles to Permission</h5>
                <form method="POST" action="{{ route('admin.permissions.update-roles', $permission->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="roles" class="form-label">Select Roles:</label>
                        <select name="roles[]" id="roles" class="form-select" multiple>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ $permission->roles->contains($role->id) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple roles.</small>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-link me-2"></i> Update Role Assignments
                    </button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Permissions
        </a>
    </div>
@endsection