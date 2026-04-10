<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsletter->subject }}</title>
    <style>
        /* Reset styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Proxima Nova', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        
        /* Container */
        .email-wrapper {
            width: 100%;
            background-color: #f5f5f5;
            padding: 40px 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background-color: #333333;
            padding: 30px 40px;
            text-align: center;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
        }
        
        .logo-text {
            color: #C8A165;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
        }
        
        /* Preview text */
        .preview-text {
            color: #C8A165;
            font-size: 14px;
            text-align: center;
            padding: 15px 40px;
            background-color: #333333;
            border-bottom: 3px solid #C8A165;
            margin: 0;
        }
        
        /* Content */
        .email-content {
            padding: 40px;
        }
        
        .email-content h1, 
        .email-content h2, 
        .email-content h3 {
            color: #333333;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .email-content h1 {
            font-size: 28px;
            border-bottom: 2px solid #C8A165;
            padding-bottom: 10px;
        }
        
        .email-content h2 {
            font-size: 22px;
        }
        
        .email-content h3 {
            font-size: 18px;
        }
        
        .email-content p {
            margin: 15px 0;
            color: #555555;
        }
        
        .email-content a {
            color: #C8A165;
            text-decoration: underline;
        }
        
        .email-content img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .email-content ul, 
        .email-content ol {
            padding-left: 20px;
            color: #555555;
        }
        
        .email-content li {
            margin-bottom: 8px;
        }
        
        /* Button styles for content */
        .email-content .btn,
        .email-content .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #C8A165;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        /* Footer */
        .email-footer {
            background-color: #333333;
            padding: 30px 40px;
            text-align: center;
        }
        
        .footer-content {
            color: #999999;
            font-size: 12px;
            line-height: 1.8;
        }
        
        .footer-content a {
            color: #C8A165;
            text-decoration: none;
        }
        
        .footer-content a:hover {
            text-decoration: underline;
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #C8A165;
            font-size: 14px;
        }
        
        .unsubscribe-link {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #444444;
        }
        
        .unsubscribe-link a {
            color: #888888;
            font-size: 11px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
            
            .email-header,
            .email-content,
            .email-footer {
                padding: 20px;
            }
            
            .email-content h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                @if(config('app.logo'))
                    <img src="{{ config('app.logo') }}" alt="{{ config('app.name', 'Brickspoint') }}" class="logo">
                @else
                    <h1 class="logo-text">BRICKSPOINT</h1>
                @endif
            </div>
            
            <!-- Preview Text -->
            @if($newsletter->preview_text)
                <p class="preview-text">{{ $newsletter->preview_text }}</p>
            @endif
            
            <!-- Content -->
            <div class="email-content">
                {!! $newsletter->content !!}
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-content">
                    <p><strong style="color: #C8A165;">{{ config('app.name', 'Brickspoint') }}</strong></p>
                    
                    @if(config('app.address'))
                        <p>{{ config('app.address') }}</p>
                    @endif
                    
                    <div class="social-links">
                        @if(config('app.facebook'))
                            <a href="{{ config('app.facebook') }}">Facebook</a>
                        @endif
                        @if(config('app.instagram'))
                            <a href="{{ config('app.instagram') }}">Instagram</a>
                        @endif
                        @if(config('app.twitter'))
                            <a href="{{ config('app.twitter') }}">Twitter</a>
                        @endif
                    </div>
                    
                    <p>You are receiving this email because you subscribed to our newsletter.</p>
                    
                    <div class="unsubscribe-link">
                        <p>Don't want to receive these emails anymore?</p>
                        <a href="{{ $unsubscribeUrl }}">Unsubscribe from this list</a>
                    </div>
                    
                    <p style="margin-top: 20px; color: #666666;">
                        © {{ date('Y') }} {{ config('app.name', 'Brickspoint') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
