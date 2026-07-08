<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Verification — {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss'])
    <style>
        body { background: #f5f7fb; min-height: 100vh; display: flex; align-items: center; font-family: system-ui, -apple-system, sans-serif; }
        .verify-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden; }
        .brand-bar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 2rem; text-align: center; }
        .brand-bar h2 { color: #C8A165; font-weight: 700; letter-spacing: 2px; margin: 0; }
        .brand-bar small { color: rgba(255,255,255,0.5); letter-spacing: 3px; text-transform: uppercase; font-size: 0.7rem; }
        .result-card { border-radius: 12px; }
        .result-card.active { background: #f0fdf4; border: 2px solid #22c55e; }
        .result-card.inactive { background: #fef2f2; border: 2px solid #ef4444; }
        .status-badge { padding: 6px 20px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
        .status-badge.active { background: #dcfce7; color: #16a34a; }
        .status-badge.inactive { background: #fecaca; color: #dc2626; }
        .icon-circle { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .icon-circle.active { background: #dcfce7; color: #16a34a; }
        .icon-circle.inactive { background: #fecaca; color: #dc2626; }
        .footer-link { color: #94a3b8; text-decoration: none; font-size: 0.8rem; }
        .footer-link:hover { color: #C8A165; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 560px;">
        <div class="card verify-card">
            <div class="brand-bar">
                <h2>BRICKSPOINT</h2>
                <small>Staff Verification Portal</small>
            </div>
            <div class="card-body p-4">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-start border-4 border-danger py-2 small">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
                    </div>
                @endif

                {{-- Result --}}
                @if ($verified = session('verified'))
                    <div class="result-card {{ $verified['status'] }} p-4 mb-4 text-center">
                        <div class="icon-circle {{ $verified['status'] }} mx-auto mb-3">
                            @if($verified['status'] === 'active')
                                <i class="fas fa-check-circle"></i>
                            @else
                                <i class="fas fa-times-circle"></i>
                            @endif
                        </div>
                        <h4 class="fw-bold mb-1">{{ $verified['name'] }}</h4>
                        <p class="text-muted small mb-3">{{ $verified['position'] }} &middot; {{ $verified['department'] }}</p>
                        <div class="d-flex justify-content-center gap-4 mb-3">
                            <div>
                                <small class="text-muted d-block">Staff Code</small>
                                <strong>{{ $verified['staff_code'] }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Employed Since</small>
                                <strong>{{ $verified['employed_since'] }}</strong>
                            </div>
                        </div>
                        <span class="status-badge {{ $verified['status'] }}">
                            <i class="fas fa-{{ $verified['status'] === 'active' ? 'check' : 'times' }} me-1"></i>
                            {{ $verified['status'] === 'active' ? 'Active Staff' : 'No Longer Employed' }}
                        </span>
                    </div>
                @endif

                <p class="text-muted text-center mb-4 small">
                    <i class="fas fa-shield-alt me-1"></i> 
                    Verify if a staff code is active by entering it below.
                </p>

                <form method="POST" action="{{ route('staff.verify.lookup') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Staff Code</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                            <input type="text" name="staff_code" class="form-control" 
                                   placeholder="Enter staff code" value="{{ old('staff_code') }}" 
                                   required autofocus autocomplete="off">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-lg w-100 py-3">
                        <i class="fas fa-search me-2"></i> Verify
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="footer-link">
                        <i class="fas fa-lock me-1"></i> Staff Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
