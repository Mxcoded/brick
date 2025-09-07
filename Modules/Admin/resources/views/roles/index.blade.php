@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Roles</h1>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create Role Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="d-flex align-items-center gap-3">
                    @csrf
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" placeholder="Role Name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Create Role
                    </button>
                </form>
            </div>
        </div>

        <!-- Roles List -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Roles List</h5>
                <ul class="list-group">
                    @foreach ($roles as $role)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $role->name }}</span>
                            <div>
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection