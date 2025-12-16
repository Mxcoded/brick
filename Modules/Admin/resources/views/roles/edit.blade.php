@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Role: <span class="text-primary">{{ $role->name }}</span></h1>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- 1. Role Name Column --}}
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 fw-bold">Role Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Permissions Matrix Column --}}
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark">Assign Permissions</h6>
                        
                        {{-- "Select All" Helper Script --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.perm-check').forEach(c => c.checked = true);">
                            Select All
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Loop through Groups --}}
                            @foreach($permissionGroups as $groupName => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-uppercase text-secondary" style="font-size: 0.8rem;">
                                            {{ $groupName }} Access
                                        </h6>
                                        
                                        @foreach($permissions as $permission)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input perm-check" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->name }}" 
                                                       id="perm_{{ $permission->id }}"
                                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                
                                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                    {{-- Format: "access_admin_dashboard" -> "Admin Dashboard" --}}
                                                    {{ ucwords(str_replace('_', ' ', str_replace($groupName, '', $permission->name))) }}
                                                    <small class="text-muted d-block" style="font-size: 0.7em;">{{ $permission->name }}</small>
                                                </label>
                                            </div>
                                        @endforeach
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