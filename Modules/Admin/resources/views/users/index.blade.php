@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
@endsection

@section('page-content')
    @php
        $currentType = request('type', '');
        $totalActive = $users->where('status', 'active')->count();
        $totalSuspended = $users->where('status', 'suspended')->count();
        $totalDeactivated = $users->where('status', 'deactivated')->count();
    @endphp

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

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $users->count() }}</div>
                    <div class="small text-muted text-uppercase">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $totalActive }}</div>
                    <div class="small text-muted text-uppercase">Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $totalSuspended }}</div>
                    <div class="small text-muted text-uppercase">Suspended</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-danger">{{ $totalDeactivated }}</div>
                    <div class="small text-muted text-uppercase">Deactivated</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
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
                                <th style="width: 45px;">#</th>
                                <th>Name</th>
                                <th style="width: 220px;">Email</th>
                                <th style="width: 80px;">Type</th>
                                <th style="width: 90px;">Status</th>
                                <th>Roles</th>
                                <th style="width: 230px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $statusColors = ['active' => 'success', 'suspended' => 'warning', 'deactivated' => 'danger'];
                                    $statusColor = $statusColors[$user->status] ?? 'secondary';
                                    $userRoles = $user->roles->pluck('name')->toArray();
                                @endphp
                                <tr class="{{ $user->status !== 'active' ? 'opacity-75' : '' }}">
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
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($userRoles)
                                            @foreach ($userRoles as $role)
                                                <span class="badge bg-primary me-1">{{ $role }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">No roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#roleModal{{ $user->id }}">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            @can('users.update')
                                                <a href="{{ route('admin.users.password.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#typeModal{{ $user->id }}">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                                @if ($user->isStaff())
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                                                        data-bs-target="#resendModal{{ $user->id }}" title="Resend login credentials">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                @endif
                                                @if ($user->isActive())
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                        data-bs-target="#suspendModal{{ $user->id }}">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deactivateModal{{ $user->id }}">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Activate account">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
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
        @php $allowedRoles = $user->isGuest() ? $guestRoles : $staffRoles; @endphp
        <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Roles for {{ $user->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <span class="badge {{ $user->isStaff() ? 'bg-success' : 'bg-info' }} mb-2">{{ $user->isStaff() ? 'Staff Account' : 'Guest Account' }}</span>
                        <p class="small text-muted mb-2">
                            @if ($user->isGuest())
                                Guest accounts can only have the <strong>guest</strong> role.
                            @else
                                Staff accounts can have roles like admin, staff, hr_manager, etc.
                            @endif
                        </p>
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
                            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-check me-1"></i> Assign</button>
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
                        @csrf @method('PATCH')
                        <div class="modal-body">
                            <p class="mb-3"><strong>{{ $user->name }}</strong> is currently: <span class="badge {{ $currentBadge }} ms-1">{{ $currentLabel }}</span></p>
                            <p class="mb-2">Change to: <span class="badge {{ $newBadgeClass }} ms-1">{{ $newTypeLabel }}</span></p>
                            <input type="hidden" name="type" value="{{ $newType }}">
                            <div class="alert alert-warning border-0 mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>What will happen:</strong>
                                <ul class="mb-0 mt-1 ps-3">
                                    @if ($user->isStaff())
                                        <li>All current roles will be removed</li>
                                        <li>The <strong>guest</strong> role will be assigned</li>
                                        <li>The user will only have website portal access</li>
                                    @else
                                        <li>The <strong>guest</strong> role will be removed</li>
                                        <li>No new roles will be auto-assigned</li>
                                        <li>Use the <strong>Roles</strong> button after to assign staff roles</li>
                                    @endif
                                </ul>
                            </div>
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

    {{-- Suspend Modals --}}
    @foreach ($users as $user)
        <div class="modal fade" id="suspendModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header" style="background: #ffc107;">
                        <h5 class="modal-title"><i class="fas fa-pause me-2"></i>Suspend Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="suspended">
                        <div class="modal-body">
                            <p class="mb-3">You are about to suspend <strong>{{ $user->name }}</strong> ({{ $user->email }}).</p>
                            <p class="small text-muted mb-2">A suspended user cannot log in until reactivated.</p>
                            <label class="form-label fw-semibold">Reason for suspension <span class="text-danger">*</span></label>
                            <textarea name="suspension_reason" class="form-control" rows="3" required placeholder="e.g. Policy violation, investigation pending..."></textarea>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn" style="background-color: #ffc107; color: #212529;">
                                <i class="fas fa-pause me-1"></i> Suspend Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Deactivate Modals --}}
    @foreach ($users as $user)
        <div class="modal fade" id="deactivateModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header" style="background: #dc3545; color: #fff;">
                        <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Deactivate Account</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="deactivated">
                        <div class="modal-body">
                            <p class="mb-3">You are about to <strong class="text-danger">deactivate</strong> <strong>{{ $user->name }}</strong> ({{ $user->email }}).</p>
                            <div class="alert alert-danger border-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This action is irreversible unless an admin manually reactivates the account.
                                The user will lose all system access immediately.
                            </div>
                            <label class="form-label fw-semibold">Reason for deactivation <span class="text-danger">*</span></label>
                            <textarea name="suspension_reason" class="form-control" rows="3" required placeholder="e.g. Employment ended, security concern..."></textarea>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban me-1"></i> Deactivate Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Resend Credentials Modals --}}
    @foreach ($users as $user)
        @if ($user->isStaff())
        <div class="modal fade" id="resendModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header" style="background: #C8A165; color: #fff;">
                        <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Resend Credentials</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.users.resend-credentials', $user->id) }}">
                        @csrf
                        <div class="modal-body text-center">
                            <i class="fas fa-envelope-open-text text-gold" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p class="mb-1">Send new login credentials to:</p>
                            <p class="fw-bold mb-0">{{ $user->email }}</p>
                            <p class="small text-muted mt-2 mb-0">A new random password will be generated and sent via email.</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-gold">
                                <i class="fas fa-paper-plane me-1"></i> Send Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endsection

@section('page-scripts')
    <script>
        function limitCheckboxes(checkbox, max) {
            var form = checkbox.form;
            var checked = form.querySelectorAll('input[name="roles[]"]:checked');
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
    table thead th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; border-bottom: 2px solid #f0f0f0 !important; }
    table tbody tr { transition: background-color 0.15s; }
    table tbody tr:hover { background-color: #f8f9fa !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08c54; border-color: #b08c54; color: #fff; }
    .text-gold { color: #C8A165; }
    .opacity-75 { opacity: 0.75; }
</style>
@endsection
