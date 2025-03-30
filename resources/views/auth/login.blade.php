@extends('layouts.app')

@section('content')
<div class="login-container d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow-lg border-0 rounded-lg" style="width: 100%; max-width: 450px;">
        <div class="card-header bg-white py-4 border-0 text-center">
            <div class="login-icon mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0d6efd" class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                </svg>
            </div>
            <h3 class="my-2">Welcome back</h3>
            <p class="text-muted mb-0">Sign in to your account</p>
        </div>
        
        <div class="card-body p-5">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                           placeholder="Enter your email">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                           name="password" required autocomplete="current-password"
                           placeholder="Enter your password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    @if (Route::has('password.request'))
                    <div>
                        <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot password?</a>
                    </div>
                    @endif
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Sign in
                    </button>
                </div>
                
                @if (Route::has('register'))
                <div class="text-center pt-3">
                    <p class="text-muted">Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Sign up</a></p>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<style>
    .login-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    }
    .login-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
    }
    .card {
        border: none;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endsection