@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-users me-2" style="color: #C8A165;"></i>Manage Users</h1>
            <p class="text-muted mb-0">Staff and guest accounts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.guest.create') }}" class="btn btn-outline-info">
                <i class="fas fa-user-plus me-1"></i> New Guest
            </a>
            <a href="{{ route('admin.employees.create-user') }}" class="btn btn-outline-primary">
                <i class="fas fa-user-plus me-1"></i> New Staff
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-0">{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $currentType = request('type', '');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $users->count() }}</div>
                    <div class="small text-muted text-uppercase">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $totalStaff }}</div>
                    <div class="small text-muted text-uppercase">Staff Accounts</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-info">{{ $totalGuests }}</div>
                    <div class="small text-muted text-uppercase">Guest Accounts</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 py-3">
            <span class="fw-semibold"><i class="fas fa-list me-2" style="color: #C8A165;"></i>All Accounts</span>
            <div class="d-flex gap-2" id="typeFilters">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm status-filter {{ $currentType === '' ? 'active' : '' }}">All</a>
                <a href="{{ route('admin.users.index', ['type' => 'staff']) }}" class="btn btn-sm status-filter {{ $currentType === 'staff' ? 'active' : '' }}">Staff</a>
                <a href="{{ route('admin.users.index', ['type' => 'guest']) }}" class="btn btn-sm status-filter {{ $currentType === 'guest' ? 'active' : '' }}">Guests</a>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($users->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Name</th>
                                <th style="width: 260px;">Email</th>
                                <th style="width: 100px;">Type</th>
                                <th>Roles</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="text-muted">{{ $user->id }}</td>
                                    <td class="fw-medium">{{ $user->name }}</td>
                                    <td class="small">{{ $user->email }}</td>
                                    <td>
                                        @if ($user->isStaff())
                                            <span class="badge bg-success">Staff</span>
                                        @elseif ($user->isGuest())
                                            <span class="badge bg-info">Guest</span>
                                        @else
                                            <span class="badge bg-secondary">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user->roles->isNotEmpty())
                                            @foreach ($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">No roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#roleModal{{ $user->id }}">
                                                <i class="fas fa-user-tag me-1"></i> Roles
                                            </button>
                                            @can('users.update')
                                                <a href="{{ route('admin.users.password.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-key me-1"></i> Password
                                                </a>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#typeModal{{ $user->id }}">
                                                    <i class="fas fa-exchange-alt me-1"></i> Type
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">
                        @if ($currentType === 'staff')
                            No staff accounts found.
                        @elseif ($currentType === 'guest')
                            No guest accounts found.
                        @else
                            No users found.
                        @endif
                    </h5>
                </div>
            @endif
        </div>
    </div>

    {{-- Role Modals --}}
    @foreach ($users as $user)
        @php
            $allowedRoles = $user->isGuest() ? $guestRoles : $staffRoles;
        @endphp
        <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Roles for {{ $user->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <span class="badge {{ $user->isStaff() ? 'bg-success' : 'bg-info' }} mb-2">{{ $user->isStaff() ? 'Staff Account' : 'Guest Account' }}</span>
                            <p class="small text-muted mb-2">
                                @if ($user->isGuest())
                                    Guest accounts can only have the <strong>guest</strong> role.
                                @else
                                    Staff accounts can have roles like admin, staff, hr_manager, etc.
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.users.assign-role') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($allowedRoles as $roleName)
                                    @php $role = $roles->firstWhere('name', $roleName); @endphp
                                    @if ($role)
                                        <div class="form-check">
                                            <input type="checkbox" name="roles[]" id="role_{{ $roleName }}_{{ $user->id }}"
                                                value="{{ $roleName }}" class="form-check-input"
                                                {{ $user->hasRole($roleName) ? 'checked' : '' }}
                                                onchange="limitCheckboxes(this, 2)">
                                            <label for="role_{{ $roleName }}_{{ $user->id }}" class="form-check-label">{{ $roleName }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @if ($user->isStaff())
                                <p class="small text-muted mt-2 mb-0"><i class="fas fa-info-circle me-1"></i> Max 2 roles per user.</p>
                            @endif
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-check me-1"></i> Assign
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Type Change Modals --}}
    @foreach ($users as $user)
        @php
            $newType = $user->isStaff() ? 'guest' : 'staff';
            $newTypeLabel = $user->isStaff() ? 'Guest' : 'Staff';
            $newBadgeClass = $user->isStaff() ? 'bg-info' : 'bg-success';
            $currentLabel = $user->isStaff() ? 'Staff' : 'Guest';
            $currentBadge = $user->isStaff() ? 'bg-success' : 'bg-info';
        @endphp
        <div class="modal fade" id="typeModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-exchange-alt me-2" style="color: #C8A165;"></i>Change Account Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.users.type.update', $user->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            <p class="mb-3"><strong>{{ $user->name }}</strong> is currently:
                                <span class="badge {{ $currentBadge }} ms-1">{{ $currentLabel }}</span>
                            </p>
                            <p class="mb-2">Change to: <span class="badge {{ $newBadgeClass }} ms-1">{{ $newTypeLabel }}</span></p>
                            <input type="hidden" name="type" value="{{ $newType }}">

                            @if ($user->isStaff())
                                <div class="alert alert-warning border-0 mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>What will happen:</strong>
                                    <ul class="mb-0 mt-1 ps-3">
                                        <li>All current roles will be removed</li>
                                        <li>The <strong>guest</strong> role will be assigned</li>
                                        <li>The user will only have website portal access</li>
                                    </ul>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>What will happen:</strong>
                                    <ul class="mb-0 mt-1 ps-3">
                                        <li>The <strong>guest</strong> role will be removed</li>
                                        <li>No new roles will be auto-assigned</li>
                                        <li>Use the <strong>Roles</strong> button after to assign staff roles</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn" style="background-color: #C8A165; border-color: #C8A165; color: #fff;">
                                <i class="fas fa-check me-1"></i> Change to {{ $newTypeLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('page-scripts')
    <script>
        function limitCheckboxes(checkbox, max) {
            const form = checkbox.form;
            const checked = form.querySelectorAll('input[name="roles[]"]:checked');
            if (checked.length > max) {
                checkbox.checked = false;
            }
        }
    </script>
@endsection

@section('styles')
<style>
    .card { border-radius: 10px; }
    .card-header { border-bottom: 2px solid #f0f0f0; }
    .status-filter { border-radius: 20px; padding: 4px 14px; font-size: 0.82rem; font-weight: 500; border: 1px solid #dee2e6; background: #fff; color: #666; text-decoration: none; transition: all 0.15s; }
    .status-filter:hover { border-color: #C8A165; color: #C8A165; }
    .status-filter.active { background: #C8A165; border-color: #C8A165; color: #fff; }
    table thead th { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; border-bottom: 2px solid #f0f0f0 !important; }
    table tbody tr { transition: background-color 0.15s; }
    table tbody tr:hover { background-color: #f8f9fa !important; }
</style>
@endsection
