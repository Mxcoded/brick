@extends('layouts.master')

@section('title', 'Manage Users — ' . $property->name)

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-users me-2"></i>Manage Users</h4>
            <p class="text-muted mb-0">{{ $property->name }}</p>
        </div>
        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 fw-bold">Assigned Users</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Email</th><th>Default</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($property->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->pivot->is_default)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.properties.removeUser', [$property, $user]) }}" method="POST" onsubmit="return confirm('Remove this user?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No users assigned.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 fw-bold">Add User</div>
                <div class="card-body">
                    @if($users->isNotEmpty())
                    <form action="{{ route('admin.properties.assignUser', $property) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Select User</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">— Choose —</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" class="form-check-input" id="isDefault" value="1">
                                <label class="form-check-label" for="isDefault">Set as default property for this user</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-plus me-1"></i> Assign User</button>
                        </div>
                    </form>
                    @else
                    <p class="text-muted mb-0">All users are already assigned to this property.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
