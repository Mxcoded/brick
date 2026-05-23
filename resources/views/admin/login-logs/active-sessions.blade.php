@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.login-logs.index') }}">Login History</a></li>
    <li class="breadcrumb-item active">Active Sessions</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-wifi me-2 text-success"></i>Active Sessions</h1>
            <p class="text-muted mb-0">Users currently logged into the system</p>
        </div>
        <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Login History
        </a>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $sessions->count() }}</h2>
                    <p class="mb-0">Active Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $sessions->where('device_type', 'desktop')->count() }}</h2>
                    <p class="mb-0">Desktop Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $sessions->where('device_type', 'mobile')->count() + $sessions->where('device_type', 'tablet')->count() }}</h2>
                    <p class="mb-0">Mobile/Tablet Sessions</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Sessions List --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Currently Active Users</h6>
        </div>
        <div class="card-body">
            @if($sessions->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No active sessions at the moment</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Logged In</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ substr($session->user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $session->user->name ?? 'Unknown' }}</strong>
                                                <br><small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address }}</code>
                                    </td>
                                    <td>
                                        @if($session->device_type == 'mobile')
                                            <i class="fas fa-mobile-alt text-info"></i>
                                        @elseif($session->device_type == 'tablet')
                                            <i class="fas fa-tablet-alt text-warning"></i>
                                        @else
                                            <i class="fas fa-desktop text-secondary"></i>
                                        @endif
                                        {{ ucfirst($session->device_type) }}
                                        <br><small class="text-muted">{{ $session->platform }}</small>
                                    </td>
                                    <td>{{ $session->browser }}</td>
                                    <td>{{ $session->logged_in_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $session->logged_in_at->diffForHumans(null, true) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endsection
