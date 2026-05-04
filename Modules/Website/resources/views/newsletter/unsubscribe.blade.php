<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - {{ config('app.name', 'Brickspoint') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Proxima Nova', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333333;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo-text {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #C8A165;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
        }
        
        .icon-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        }
        
        .icon svg {
            width: 40px;
            height: 40px;
        }
        
        .icon-success svg {
            color: #28a745;
        }
        
        .icon-error svg {
            color: #dc3545;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 16px;
            color: #333333;
        }
        
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #666666;
            margin-bottom: 24px;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background-color: #C8A165;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #b8915b;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #999999;
        }
        
        .footer a {
            color: #C8A165;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <span class="logo-text">BRICKSPOINT</span>
        </div>
        
        <div class="card">
            @if($success)
                <div class="icon icon-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1>Unsubscribed Successfully</h1>
            @else
                <div class="icon icon-error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1>Unsubscribe Failed</h1>
            @endif
            
            <p>{{ $message }}</p>
            
            <a href="{{ url('/') }}" class="btn">Visit Our Website</a>
        </div>
        
        <div class="footer">
            <p>{{ config('app.name', 'Brickspoint') }} &copy; {{ date('Y') }}</p>
            <p>Questions? <a href="{{ route('website.contact') }}">Contact Us</a></p>
        </div>
    </div>
</body>
</html>
