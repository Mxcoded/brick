@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
            <a href="{{ route('admin.employees.create-user') }}" class="btn btn-primary btn-sm" aria-label="Create user for employee">
                <i class="fas fa-user-plus me-2"></i> Create User for Employee
            </a>
        </div>

        <!-- Success/Error Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Users Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Roles</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->roles->isNotEmpty())
                                            @foreach ($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No roles assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                            data-bs-target="#roleModal{{ $user->id }}" aria-label="Manage roles for {{ $user->name }}">
                                            <i class="fas fa-user-tag me-1"></i> Manage Roles
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role Management Modal -->
        @foreach ($users as $user)
            <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1" aria-labelledby="roleModalLabel{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="roleModalLabel{{ $user->id }}">Manage Roles for {{ $user->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('admin.users.assign-role') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="mb-3">
                                    <label class="form-label">Assign Roles (up to 2)</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($roles as $role)
                                            <div class="form-check">
                                                <input type="checkbox" name="roles[]" id="role_{{ $role->name }}_{{ $user->id }}" value="{{ $role->name }}" class="form-check-input" {{ $user->hasRole($role->name) ? 'checked' : '' }} aria-label="Assign {{ $role->name }} to {{ $user->name }}" onchange="limitCheckboxes(this, 2)">
                                                <label for="role_{{ $role->name }}_{{ $user->id }}" class="form-check-label">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" aria-label="Assign roles to {{ $user->name }}">
                                    <i class="fas fa-check me-1"></i> Assign
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        function limitCheckboxes(checkbox, max) {
            const form = checkbox.form;
            const checked = form.querySelectorAll('input[name="roles[]"]:checked');
            if (checked.length > max) {
                checkbox.checked = false;
            }
        }
    </script>
@endpush