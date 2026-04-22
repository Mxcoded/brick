<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $newsletter->subject }}</title>
    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; }
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7;
            color: #2D2D2D;
            background-color: #F8F6F3;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Main Wrapper */
        .email-wrapper {
            width: 100%;
            background: linear-gradient(180deg, #F8F6F3 0%, #EDE8E1 100%);
            padding: 40px 20px;
        }
        
        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(45, 45, 45, 0.12);
        }
        
        /* Elegant Header */
        .email-header {
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 50%, #1A1A1A 100%);
            padding: 45px 40px 35px;
            text-align: center;
            position: relative;
        }
        
        .email-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #C9A962, transparent);
        }
        
        .logo {
            max-width: 200px;
            height: auto;
        }
        
        .logo-text {
            color: #C9A962;
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 6px;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }
        
        .tagline {
            color: #B8B8B8;
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0;
            font-weight: 400;
        }
        
        /* Preview Banner */
        .preview-banner {
            background: linear-gradient(90deg, #C9A962 0%, #D4B978 50%, #C9A962 100%);
            padding: 16px 40px;
            text-align: center;
        }
        
        .preview-text {
            color: #1A1A1A;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        /* Content Area */
        .email-content {
            padding: 45px 50px;
            background: #FFFFFF;
        }
        
        .email-content h1 {
            color: #1A1A1A;
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 20px 0;
            line-height: 1.3;
            position: relative;
            padding-bottom: 15px;
        }
        
        .email-content h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #C9A962, #D4B978);
            border-radius: 2px;
        }
        
        .email-content h2 {
            color: #2D2D2D;
            font-size: 22px;
            font-weight: 600;
            margin: 30px 0 15px 0;
            line-height: 1.4;
        }
        
        .email-content h3 {
            color: #3D3D3D;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 12px 0;
        }
        
        .email-content p {
            margin: 0 0 18px 0;
            color: #4A4A4A;
            font-size: 16px;
            line-height: 1.75;
        }
        
        .email-content a {
            color: #B8942E;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid #D4B978;
            transition: all 0.2s ease;
        }
        
        .email-content a:hover {
            color: #1A1A1A;
            border-bottom-color: #1A1A1A;
        }
        
        .email-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .email-content ul, 
        .email-content ol {
            padding-left: 24px;
            margin: 20px 0;
            color: #4A4A4A;
        }
        
        .email-content li {
            margin-bottom: 12px;
            font-size: 16px;
            line-height: 1.6;
            padding-left: 8px;
        }
        
        .email-content ul li::marker {
            color: #C9A962;
        }
        
        /* Blockquote */
        .email-content blockquote {
            margin: 25px 0;
            padding: 20px 25px;
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F2ED 100%);
            border-left: 4px solid #C9A962;
            border-radius: 0 8px 8px 0;
            font-style: italic;
            color: #555555;
        }
        
        /* Button Styles */
        .email-content .btn,
        .email-content .button,
        .email-content a[href*="button"],
        .cta-button {
            display: inline-block;
            padding: 16px 36px;
            background: linear-gradient(135deg, #C9A962 0%, #B8942E 100%);
            color: #FFFFFF !important;
            text-decoration: none !important;
            border: none;
            border-bottom: none !important;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            margin: 20px 0;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.35);
            transition: all 0.3s ease;
        }
        
        .email-content .btn:hover,
        .email-content .button:hover,
        .cta-button:hover {
            background: linear-gradient(135deg, #D4B978 0%, #C9A962 100%);
            box-shadow: 0 6px 20px rgba(201, 169, 98, 0.45);
            transform: translateY(-2px);
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #E5E0D8, transparent);
            margin: 35px 0;
        }
        
        /* Feature Box */
        .feature-box {
            background: #FAF8F5;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #EDE8E1;
        }
        
        /* Footer */
        .email-footer {
            background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%);
            padding: 45px 40px 35px;
            text-align: center;
        }
        
        .footer-logo {
            color: #C9A962;
            font-size: 22px;
            font-weight: 300;
            letter-spacing: 4px;
            margin: 0 0 5px 0;
        }
        
        .footer-tagline {
            color: #888888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 25px 0;
        }
        
        .footer-contact {
            color: #AAAAAA;
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .footer-contact a {
            color: #C9A962;
            text-decoration: none;
        }
        
        .social-links {
            margin: 25px 0;
            padding: 20px 0;
            border-top: 1px solid #333333;
            border-bottom: 1px solid #333333;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 12px;
            padding: 8px 16px;
            color: #C9A962;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            text-decoration: none;
            text-transform: uppercase;
            border: 1px solid #444444;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .social-links a:hover {
            background: #C9A962;
            color: #1A1A1A;
            border-color: #C9A962;
        }
        
        .footer-note {
            color: #777777;
            font-size: 12px;
            line-height: 1.7;
            margin: 20px 0 0 0;
        }
        
        .unsubscribe-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #333333;
        }
        
        .unsubscribe-section p {
            color: #666666;
            font-size: 11px;
            margin: 0 0 8px 0;
        }
        
        .unsubscribe-section a {
            color: #888888;
            font-size: 11px;
            text-decoration: underline;
        }
        
        .unsubscribe-section a:hover {
            color: #C9A962;
        }
        
        .copyright {
            color: #555555;
            font-size: 11px;
            margin-top: 20px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 640px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            
            .email-container {
                border-radius: 12px;
            }
            
            .email-header {
                padding: 35px 25px 30px;
            }
            
            .logo-text {
                font-size: 26px;
                letter-spacing: 4px;
            }
            
            .preview-banner {
                padding: 14px 25px;
            }
            
            .email-content {
                padding: 30px 25px;
            }
            
            .email-content h1 {
                font-size: 24px;
            }
            
            .email-content h2 {
                font-size: 20px;
            }
            
            .email-content p,
            .email-content li {
                font-size: 15px;
            }
            
            .email-footer {
                padding: 35px 25px 30px;
            }
            
            .social-links a {
                margin: 5px 8px;
                padding: 6px 12px;
                font-size: 11px;
            }
        }
        
        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .email-wrapper {
                background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%) !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Elegant Header -->
            <div class="email-header">
                @if(config('app.logo'))
                    <img src="{{ config('app.logo') }}" alt="Brickspoint Boutique Aparthotel" class="logo">
                @else
                    <h1 class="logo-text">Brickspoint</h1>
                    <p class="tagline">Boutique Aparthotel</p>
                @endif
            </div>
            
            <!-- Preview Banner -->
            @if($newsletter->preview_text)
            <div class="preview-banner">
                <p class="preview-text">{{ $newsletter->preview_text }}</p>
            </div>
            @endif
            
            <!-- Main Content -->
            <div class="email-content">
                {!! $newsletter->content !!}
            </div>
            
            <!-- Premium Footer -->
            <div class="email-footer">
                <p class="footer-logo">Brickspoint</p>
                <p class="footer-tagline">Boutique Aparthotel</p>
                
                <div class="footer-contact">
                    @if(config('app.address'))
                        {{ config('app.address') }}<br>
                    @endif
                    @if(config('app.phone'))
                        <a href="tel:{{ config('app.phone') }}">{{ config('app.phone') }}</a><br>
                    @endif
                    @if(config('app.email'))
                        <a href="mailto:{{ config('app.email') }}">{{ config('app.email') }}</a>
                    @endif
                </div>
                
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
                    @if(config('app.website'))
                        <a href="{{ config('app.website') }}">Website</a>
                    @endif
                </div>
                
                <p class="footer-note">
                    You're receiving this email because you subscribed to our newsletter<br>
                    for exclusive offers and updates from Brickspoint Boutique Aparthotel.
                </p>
                
                <div class="unsubscribe-section">
                    <p>No longer wish to receive these emails?</p>
                    <a href="{{ $unsubscribeUrl }}">Unsubscribe from our mailing list</a>
                </div>
                
                <p class="copyright">
                    © {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
