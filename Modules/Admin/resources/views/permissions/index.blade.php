@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Permissions</h1>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create Permission Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.permissions.store') }}" class="d-flex align-items-center gap-3">
                    @csrf
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" placeholder="Permission Name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Create Permission
                    </button>
                </form>
            </div>
        </div>

        <!-- Permissions List -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Permissions List</h5>
                @if ($permissions->isEmpty())
                    <p>No permissions found.</p>
                @else
                    <ul class="list-group">
                        @foreach ($permissions as $permission)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $permission->name }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Assign Permission to Role Form -->
                                    <form method="POST" action="{{ route('admin.permissions.assign-to-role') }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                        <select name="role_id" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">Assign to Role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" {{ $permission->roles->contains($role->id) ? 'disabled' : '' }}>
                                                    {{ $role->name }} {{ $permission->roles->contains($role->id) ? '(Assigned)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-link"></i> Assign
                                        </button>
                                    </form>
                                    <!-- Edit and Delete Buttons -->
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this permission?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection