<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name', 'BRICKSPOINT') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --gold: #C8A165;
            --gold-dark: #b08d55;
            --dark: #1a1a1a;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Playfair Display', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark) 0%, #2a2a2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .error-container {
            text-align: center;
            padding: 3rem 2rem;
            max-width: 520px;
            width: 100%;
        }
        .brand-name {
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 2.5rem;
        }
        .brand-sub {
            font-size: 0.65rem;
            color: var(--gold);
            vertical-align: super;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1;
            margin-bottom: 0.75rem;
            text-shadow: 0 4px 20px rgba(200, 161, 101, 0.25);
        }
        .error-message {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 0.75rem;
            font-family: system-ui, sans-serif;
        }
        .error-description {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            font-family: system-ui, sans-serif;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.4rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: system-ui, sans-serif;
            border: none;
            cursor: pointer;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            color: #fff;
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(200, 161, 101, 0.35);
        }
        .btn-outline {
            border: 2px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
            background: transparent;
        }
        .btn-outline:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="brand-name">BRICKSPOINT<sub class="brand-sub">ERP</sub></div>

        <div class="error-code">@yield('code')</div>
        <div class="error-message">@yield('message')</div>
        <div class="error-description">@yield('description')</div>

        <div class="error-actions">
            @section('navigation')
            <a href="{{ route('home') }}" class="btn btn-gold">
                <i class="fas fa-home"></i> Back to Home
            </a>
            @show
        </div>
    </div>
</body>
</html>
