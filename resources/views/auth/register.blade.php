<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Brickspoint') }} - Create Account</title>
    
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
            flex: 0 0 40%;
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.9) 0%, rgba(26, 26, 26, 0.7) 100%),
                        url('{{ asset("images/hotel-room.jpg") }}') center/cover no-repeat;
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
            max-width: 350px;
        }
        
        .brand-logo {
            width: 160px;
            height: auto;
            margin-bottom: 2rem;
            filter: brightness(0) invert(1);
        }
        
        .brand-tagline {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .brand-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .benefits-list {
            margin-top: 2.5rem;
            text-align: left;
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .benefit-item i {
            color: var(--color-gold);
            margin-right: 0.75rem;
            font-size: 1rem;
        }
        
        /* Right Side - Form */
        .auth-form-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 3rem;
            background: #fff;
            overflow-y: auto;
        }
        
        .auth-form-wrapper {
            width: 100%;
            max-width: 480px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header .mobile-logo {
            display: none;
            width: 100px;
            margin-bottom: 1rem;
        }
        
        .auth-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.85rem;
            font-weight: 600;
            color: var(--color-dark);
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
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
            padding-left: 2.5rem;
        }
        
        .btn-gold {
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-gold-dark) 100%);
            border: none;
            color: #fff;
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
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
        
        .auth-link {
            color: var(--color-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .auth-link:hover {
            color: var(--color-gold-dark);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
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
        
        .password-requirements {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.25rem;
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
                padding: 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
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
                <h2 class="brand-tagline">Join Our Guest Community</h2>
                <p class="brand-subtitle">
                    Create your account and enjoy exclusive benefits with Brickspoint Boutique Aparthotel.
                </p>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Faster online booking & check-in</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>View and manage your reservations</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Exclusive member-only offers</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Save your preferences for future stays</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Form Panel -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <img src="{{ Storage::url('images/brickspoint_logo.png') }}" alt="Brickspoint" class="mobile-logo">
                    <h1>Create Your Account</h1>
                    <p>Fill in your details to get started</p>
                </div>
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input id="name" type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" 
                                       required autocomplete="name" autofocus
                                       placeholder="Enter your full name">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input id="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email"
                                       placeholder="your@email.com">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone input-icon"></i>
                                <input id="contact_number" type="tel" 
                                       class="form-control @error('contact_number') is-invalid @enderror" 
                                       name="contact_number" value="{{ old('contact_number') }}" 
                                       required autocomplete="tel"
                                       placeholder="+234 800 000 0000">
                            </div>
                            @error('contact_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password"
                                       placeholder="Create a password">
                            </div>
                            <div class="password-requirements">Min. 8 characters</div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input id="password-confirm" type="password" 
                                       class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password"
                                       placeholder="Confirm your password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms" style="font-size: 0.85rem;">
                            I agree to the <a href="#" class="auth-link">Terms of Service</a> and 
                            <a href="#" class="auth-link">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-gold">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign In</a></p>
                </div>
                
                <div class="text-center mt-3">
                    <a href="{{ route('website.home') }}" class="auth-link" style="font-size: 0.85rem;">
                        <i class="fas fa-home me-1"></i>Return to Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @vite(['resources/js/app.js'])
</body>
</html>
