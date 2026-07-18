<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Waiter POS - Login</title>
    @vite('resources/sass/app.scss')
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #141517;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        .login-card {
            background: #1e1f23;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .login-card .icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.8rem;
            color: #fff;
        }
        .login-card h1 {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .login-card p.subtitle {
            color: #6c757d;
            font-size: 0.82rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-card .form-label {
            color: #adb5bd;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .login-card .form-control {
            background: #2a2b30;
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
        }
        .login-card .form-control:focus {
            background: #2a2b30;
            border-color: #0d6efd;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.2);
        }
        .login-card .form-control::placeholder { color: #6c757d; }
        .login-card .btn-login {
            background: #0d6efd;
            border: none;
            border-radius: 10px;
            padding: 0.7rem;
            font-weight: 700;
            font-size: 0.9rem;
            width: 100%;
            color: #fff;
            transition: all 0.15s;
        }
        .login-card .btn-login:hover { background: #0b5ed7; }
        .login-card .btn-login:active { transform: scale(0.98); }
        .login-card .btn-login:disabled { opacity: 0.5; }
        .login-card .alert {
            border-radius: 10px;
            font-size: 0.82rem;
            padding: 0.6rem 1rem;
            border: none;
        }
        .login-card .alert-danger { background: rgba(220,53,69,0.15); color: #ea868f; }
        .login-card .alert-success { background: rgba(25,135,84,0.15); color: #75b798; }
        .login-card .form-check-label { color: #adb5bd; font-size: 0.82rem; }
        .login-card .form-check-input {
            background: #2a2b30;
            border-color: rgba(255,255,255,0.15);
        }
        .login-card .form-check-input:checked { background: #0d6efd; border-color: #0d6efd; }
        .login-card .links { text-align: center; margin-top: 1rem; }
        .login-card .links a { color: #6c757d; font-size: 0.8rem; text-decoration: none; }
        .login-card .links a:hover { color: #adb5bd; text-decoration: underline; }
        .alert-account {
            background: rgba(220,53,69,0.12);
            border: 1px solid rgba(220,53,69,0.3);
            border-radius: 10px;
            padding: 0.8rem 1rem;
            margin-bottom: 1rem;
            color: #ea868f;
            font-size: 0.82rem;
        }
        .alert-account strong { display: block; margin-bottom: 0.2rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon"><i class="bi bi-calculator"></i></div>
        <h1>Waiter POS</h1>
        <p class="subtitle">Sign in to start taking orders</p>

        @if(session('account_error'))
            <div class="alert-account">
                <strong>
                    @if(session('account_status') === 'suspended')
                        Account Suspended
                    @else
                        Account Deactivated
                    @endif
                </strong>
                {{ session('account_reason') }}
                <div class="mt-1">Please contact the administrator.</div>
            </div>
        @endif

        @if($errors->has('email') || $errors->has('password'))
            <div class="alert alert-danger">
                {{ $errors->first() ?: 'Invalid credentials' }}
            </div>
        @endif

        <form method="POST" action="{{ route('restaurant.waiter.login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                    placeholder="you@example.com" required autofocus autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Enter password" required autocomplete="current-password">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
            </button>
        </form>

        <div class="links">
            <a href="{{ route('login') }}">Back to main login</a>
        </div>
    </div>
</body>
</html>
