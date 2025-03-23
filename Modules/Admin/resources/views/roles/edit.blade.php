<!-- Modules/Admin/Resources/views/roles/edit.blade.php -->
@extends('admin::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Role: {{ $role->name }}</h1>
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

        <!-- Edit Role Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.update', $role->id) }}" class="d-flex align-items-center gap-3">
                    @csrf
                    @method('PUT')
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Role
                    </button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Roles
        </a>
    </div>
@endsection