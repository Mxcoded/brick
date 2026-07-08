<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Brickspoint') }} - Reset Password</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss'])
    
    <style>
        :root {
            --color-gold: #C8A165;
            --color-gold-dark: #b08d55;
            --color-dark: #1a1a1a;
            --color-cream: #F5F5F0;
        }
        
        body {
            font-family: 'Proxima Nova', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--color-cream) 0%, #fff 100%);
        }
        
        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }
        
        .reset-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 2.5rem;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .reset-header img {
            width: 120px;
            margin-bottom: 1.5rem;
        }
        
        .reset-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--color-dark);
            margin-bottom: 0.5rem;
        }
        
        .reset-header p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .reset-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(200, 161, 101, 0.15) 0%, rgba(200, 161, 101, 0.05) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .reset-icon i {
            font-size: 1.75rem;
            color: var(--color-gold);
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
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        }
        
        .auth-link:hover {
            color: var(--color-gold-dark);
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.2);
            color: #0f5132;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <img src="{{ Storage::url('images/brickspoint_logo.png') }}" alt="Brickspoint">
                <div class="reset-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your email to receive a password reset link</p>
            </div>
            
            @if (session('status'))
                <div class="alert alert-success mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" 
                               required autocomplete="email" autofocus
                               placeholder="Enter your email address">
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="auth-link">
                    <i class="fas fa-arrow-left me-1"></i>Back to Sign In
                </a>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('website.home') }}" class="auth-link" style="font-size: 0.85rem;">
                <i class="fas fa-home me-1"></i>Return to Website
            </a>
        </div>
    </div>
    
    @vite(['resources/js/app.js'])
</body>
</html>
