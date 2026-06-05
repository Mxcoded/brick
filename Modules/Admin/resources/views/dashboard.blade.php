@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0">Admin Dashboard</h3>
        <span class="text-muted small">Welcome, {{ Auth::user()->name }}</span>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div><i class="fas fa-users fa-2x opacity-50"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $totalUsers }}</h5>
                        <small>Total Users</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.roles.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div><i class="fas fa-user-tag fa-2x opacity-50"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $totalRoles }}</h5>
                            <small>Roles</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.permissions.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div><i class="fas fa-key fa-2x opacity-50"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $totalPermissions }}</h5>
                            <small>Permissions</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div><i class="fas fa-cube fa-2x opacity-50"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $activeModules }}</h5>
                        <small>Active Modules</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- RECENT USERS --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i> Recent Users</h5>
                    @can('manage_users')
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td class="fw-semibold">{{ $user->name }}</td>
                                    <td class="text-muted small">{{ $user->email }}</td>
                                    <td class="text-muted small">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No users yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- UPCOMING BANQUET EVENTS --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-glass-cheers me-2 text-gold"></i> Upcoming Events</h5>
                    @can('access_banquet_dashboard')
                        <a href="{{ route('banquet.index') }}" class="btn btn-sm btn-outline-gold">View All</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @include('banquet::partials.upcomingevent')
                </div>
            </div>
        </div>
    </div>
@endsection