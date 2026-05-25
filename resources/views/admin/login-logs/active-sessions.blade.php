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
            <p class="text-muted mb-0">Real-time user session monitoring (timeout: {{ App\Models\UserLoginLog::SESSION_TIMEOUT_MINUTES }} mins)</p>
        </div>
        <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Login History
        </a>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $activeSessions->count() }}</h2>
                    <p class="mb-0"><i class="fas fa-circle fa-xs me-1"></i>Active Now</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $staleSessions->count() }}</h2>
                    <p class="mb-0"><i class="fas fa-clock fa-xs me-1"></i>Idle Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $activeSessions->where('device_type', 'desktop')->count() }}</h2>
                    <p class="mb-0">Desktop Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $activeSessions->whereIn('device_type', ['mobile', 'tablet'])->count() }}</h2>
                    <p class="mb-0">Mobile Active</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Sessions List --}}
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-circle fa-xs me-2"></i>Active Users ({{ $activeSessions->count() }})</h6>
        </div>
        <div class="card-body">
            @if($activeSessions->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No active sessions at the moment</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Last Activity</th>
                                <th>Session Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeSessions as $session)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-success text-white me-2">
                                                {{ substr($session->user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $session->user->name ?? 'Unknown' }}</strong>
                                                <br><small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code>{{ $session->ip_address }}</code></td>
                                    <td>
                                        @if($session->device_type == 'mobile')
                                            <i class="fas fa-mobile-alt text-info"></i>
                                        @elseif($session->device_type == 'tablet')
                                            <i class="fas fa-tablet-alt text-warning"></i>
                                        @else
                                            <i class="fas fa-desktop text-secondary"></i>
                                        @endif
                                        {{ ucfirst($session->device_type ?? 'desktop') }}
                                        <br><small class="text-muted">{{ $session->platform }}</small>
                                    </td>
                                    <td>{{ $session->browser }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ ($session->last_activity_at ?? $session->logged_in_at)->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>{{ $session->logged_in_at->diffForHumans(null, true) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Stale/Idle Sessions --}}
    @if($staleSessions->isNotEmpty())
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-clock fa-xs me-2"></i>Idle Sessions ({{ $staleSessions->count() }}) - No activity for {{ App\Models\UserLoginLog::SESSION_TIMEOUT_MINUTES }}+ minutes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Last Activity</th>
                            <th>Logged In</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staleSessions as $session)
                            <tr class="table-warning">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-warning text-dark me-2">
                                            {{ substr($session->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $session->user->name ?? 'Unknown' }}</strong>
                                            <br><small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $session->ip_address }}</code></td>
                                <td>
                                    @if($session->device_type == 'mobile')
                                        <i class="fas fa-mobile-alt text-info"></i>
                                    @elseif($session->device_type == 'tablet')
                                        <i class="fas fa-tablet-alt text-warning"></i>
                                    @else
                                        <i class="fas fa-desktop text-secondary"></i>
                                    @endif
                                    {{ ucfirst($session->device_type ?? 'desktop') }}
                                </td>
                                <td>
                                    <span class="text-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ ($session->last_activity_at ?? $session->logged_in_at)->diffForHumans() }}
                                    </span>
                                </td>
                                <td>{{ $session->logged_in_at->format('M d, h:i A') }}</td>
                                <td>
                                    @if($session->logged_in_at->diffInHours(now()) >= App\Models\UserLoginLog::MAX_SESSION_AGE_HOURS)
                                        <span class="badge bg-danger">Session Expired (24h+)</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Idle</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
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
