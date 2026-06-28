<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session Expired — {{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=Montserrat:400,500,600,700|Playfair+Display:400,500,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
            background: #FFFFFF;
            color: #333333;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .lock-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 32px;
            border-radius: 50%;
            background: #F5F5F0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #C8A165;
        }
        .lock-icon svg {
            width: 36px;
            height: 36px;
            color: #C8A165;
        }
        .code {
            font-family: 'Playfair Display', serif;
            font-size: 96px;
            font-weight: 700;
            line-height: 1;
            color: #C8A165;
            margin-bottom: 8px;
        }
        .decorative-line {
            width: 60px;
            height: 2px;
            background: #C8A165;
            margin: 16px auto 24px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 12px;
        }
        p {
            font-size: 15px;
            line-height: 1.7;
            color: #6c757d;
            margin-bottom: 8px;
        }
        .actions {
            margin-top: 32px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 32px;
            background: #C8A165;
            color: #FFFFFF;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #b8924f; }
        .btn-secondary {
            display: inline-block;
            padding: 14px 32px;
            background: transparent;
            color: #333333;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.5px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-secondary:hover { background: #F5F5F0; }
        .footer-text {
            margin-top: 40px;
            font-size: 12px;
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="lock-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <div class="code">419</div>
        <div class="decorative-line"></div>
        <h1>Session Expired</h1>
        <p>Your session has timed out due to inactivity.</p>
        <p>Please refresh the page and try again — your data is still safe.</p>

        <div class="actions">
            <button onclick="window.location.reload()" class="btn-primary">
                Refresh Page
            </button>
            <a href="{{ url('/') }}" class="btn-secondary">
                Return to Homepage
            </a>
        </div>

        <p class="footer-text">{{ config('app.name') }} &mdash; Boutique Aparthotel</p>
    </div>
</body>
</html>
