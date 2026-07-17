<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Brickspoint') }} - Sign In</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons (local) -->
    @vite(['resources/sass/app.scss'])
    
    <style>
        :root {
            --color-gold: #C8A165;
            --color-gold-dark: #b08d55;
            --color-dark: #1a1a1a;
            --color-cream: #F5F5F0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Proxima Nova', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }
        
        .auth-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        /* Left Side - Image/Branding */
        .auth-brand {
            flex: 1;
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.9) 0%, rgba(26, 26, 26, 0.7) 100%),
                        url('{{ asset("images/hotel-lobby.jpg") }}') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            color: #fff;
        }
        
        .auth-brand::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
        }
        
        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 400px;
        }
        
        .brand-logo {
            width: 180px;
            height: auto;
            margin-bottom: 2rem;
            filter: brightness(0) invert(1);
        }
        
        .brand-tagline {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 500;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .brand-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .brand-features {
            margin-top: 3rem;
            display: flex;
            gap: 2rem;
            justify-content: center;
        }
        
        .brand-feature {
            text-align: center;
        }
        
        .brand-feature i {
            font-size: 1.5rem;
            color: var(--color-gold);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .brand-feature span {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        /* Right Side - Form */
        .auth-form-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            background: #fff;
        }
        
        .auth-form-wrapper {
            width: 100%;
            max-width: 420px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .auth-header .mobile-logo {
            display: none;
            width: 120px;
            margin-bottom: 1.5rem;
        }
        
        .auth-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--color-dark);
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            padding: 0.875rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .form-control:focus {
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.15);
            background: #fff;
        }
        
        .form-control::placeholder {
            color: #aaa;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 4;
        }
        
        .input-group .form-control {
            padding-left: 2.75rem;
        }
        
        .btn-gold {
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-gold-dark) 100%);
            border: none;
            color: #fff;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-gold:hover {
            background: linear-gradient(135deg, var(--color-gold-dark) 0%, #9a7a45 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(200, 161, 101, 0.4);
        }
        
        .form-check-input:checked {
            background-color: var(--color-gold);
            border-color: var(--color-gold);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.25);
        }
        
        .auth-link {
            color: var(--color-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .auth-link:hover {
            color: var(--color-gold-dark);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #aaa;
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .auth-footer p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Back to website link */
        .back-link {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .back-link:hover {
            color: var(--color-gold);
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .auth-brand {
                display: none;
            }
            
            .auth-form-container {
                background: linear-gradient(135deg, var(--color-cream) 0%, #fff 100%);
            }
            
            .auth-header .mobile-logo {
                display: inline-block;
            }
        }
        
        @media (max-width: 576px) {
            .auth-form-container {
                padding: 2rem 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Branding Panel -->
        <div class="auth-brand">
            <a href="{{ route('website.home') }}" class="back-link">
                <i class="fas fa-arrow-left me-2"></i>Back to Website
            </a>
            
            <div class="brand-content">
                <img src="{{ Storage::url('images/brickspoint_logo.png') }}" alt="Brickspoint" class="brand-logo">
                <h2 class="brand-tagline">Experience Luxury Living</h2>
                <p class="brand-subtitle">
                    Welcome to Brickspoint Boutique Aparthotel. Your sanctuary of comfort and elegance in the heart of Abuja.
                </p>
                
                <div class="brand-features">
                    <div class="brand-feature">
                        <i class="fas fa-concierge-bell"></i>
                        <span>24/7 Service</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-wifi"></i>
                        <span>Free WiFi</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-utensils"></i>
                        <span>Fine Dining</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Form Panel -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <img src="{{ Storage::url('images/brickspoint_logo.png') }}" alt="Brickspoint" class="mobile-logo">
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your account</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input id="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" 
                                   required autocomplete="email" autofocus
                                   placeholder="Enter your email">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input id="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="current-password"
                                   placeholder="Enter your password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if ($remaining > 0 && $remaining < 5)
                        <div class="alert alert-warning py-2 small d-flex align-items-center" role="alert" style="font-size:0.85rem;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>{{ $remaining }} of 5 login attempts remaining.</span>
                        </div>
                    @elseif ($remaining === 0)
                        <div class="alert alert-danger py-2 small d-flex align-items-center" role="alert" style="font-size:0.85rem;">
                            <i class="fas fa-lock me-2"></i>
                            <span>Too many login attempts. Please try again in {{ ceil($retryAfter / 60) }} minute(s).</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link">Forgot Password?</a>
                        @endif
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-gold">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </div>
                </form>
                
                @if (Route::has('register'))
                <div class="auth-footer">
                    <p>Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create Account</a></p>
                </div>
                @endif
                
                <div class="text-center mt-4">
                    <a href="{{ route('website.home') }}" class="auth-link" style="font-size: 0.85rem;">
                        <i class="fas fa-home me-1"></i>Return to Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Account Status Modal --}}
    @if (session('account_error'))
    <div class="modal fade" id="accountStatusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 text-white" style="background: {{ session('account_status') === 'suspended' ? '#ffc107' : '#dc3545' }};">
                    <h5 class="modal-title">
                        <i class="fas {{ session('account_status') === 'suspended' ? 'fa-pause-circle' : 'fa-ban' }} me-2"></i>
                        Account {{ ucfirst(session('account_status')) }}
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas {{ session('account_status') === 'suspended' ? 'fa-pause-circle text-warning' : 'fa-times-circle text-danger' }}" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="fw-bold">Hello, {{ session('account_name') }}</h5>
                    <p class="text-muted mb-1">
                        Your account has been <strong>{{ session('account_status') }}</strong>.
                    </p>
                    <p class="text-muted mb-0">{{ session('account_reason') }}</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <a href="{{ route('website.home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i> Go to Website
                    </a>
                    <button type="button" class="btn" style="background-color: #C8A165; color: #fff;" onclick="window.location.reload()">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('accountStatusModal'));
            modal.show();
        });
    </script>
    @endif

    @vite(['resources/js/app.js'])
</body>
</html>
