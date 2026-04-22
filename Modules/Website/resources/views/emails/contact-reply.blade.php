<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response from Brickspoint Boutique Aparthotel</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7;
            color: #2D2D2D;
            background-color: #F8F6F3;
        }
        .email-wrapper {
            width: 100%;
            background: linear-gradient(180deg, #F8F6F3 0%, #EDE8E1 100%);
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(45, 45, 45, 0.12);
        }
        .email-header {
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            padding: 40px;
            text-align: center;
        }
        .logo-text {
            color: #C9A962;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 5px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .tagline {
            color: #888888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }
        .response-banner {
            background: linear-gradient(90deg, #C9A962 0%, #D4B978 100%);
            padding: 14px 30px;
            text-align: center;
        }
        .response-banner p {
            margin: 0;
            color: #1A1A1A;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .email-content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            color: #2D2D2D;
            margin: 0 0 20px 0;
        }
        .intro-text {
            color: #555555;
            font-size: 15px;
            margin: 0 0 25px 0;
        }
        .reply-box {
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F2ED 100%);
            border-left: 4px solid #C9A962;
            border-radius: 0 12px 12px 0;
            padding: 25px;
            margin: 25px 0;
        }
        .reply-text {
            margin: 0;
            color: #3D3D3D;
            font-size: 15px;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .signature-section {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #EDE8E1;
        }
        .signature-text {
            color: #555555;
            font-size: 15px;
            margin: 0;
            line-height: 1.8;
        }
        .signature-name {
            color: #C9A962;
            font-weight: 600;
        }
        .original-section {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #EDE8E1;
        }
        .original-label {
            font-size: 11px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 12px 0;
        }
        .original-date {
            color: #AAAAAA;
            font-weight: 400;
        }
        .original-box {
            background: #F8F6F3;
            border-radius: 8px;
            padding: 20px;
        }
        .original-text {
            margin: 0;
            color: #777777;
            font-size: 14px;
            font-style: italic;
            line-height: 1.7;
        }
        .email-footer {
            background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%);
            padding: 35px 40px;
            text-align: center;
        }
        .footer-logo {
            color: #C9A962;
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 3px;
            margin: 0 0 5px 0;
        }
        .footer-tagline {
            color: #666666;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 20px 0;
        }
        .footer-note {
            color: #888888;
            font-size: 12px;
            line-height: 1.7;
            margin: 0 0 20px 0;
        }
        .footer-links {
            margin: 20px 0;
        }
        .footer-links a {
            color: #C9A962;
            text-decoration: none;
            font-size: 12px;
            margin: 0 10px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .copyright {
            color: #555555;
            font-size: 11px;
            margin: 20px 0 0 0;
            padding-top: 20px;
            border-top: 1px solid #333333;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-header { padding: 30px 25px; }
            .email-content { padding: 30px 25px; }
            .email-footer { padding: 30px 25px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="logo-text">Brickspoint</h1>
                <p class="tagline">Boutique Aparthotel</p>
            </div>
            
            <div class="response-banner">
                <p>✉️ Response to Your Inquiry</p>
            </div>
            
            <div class="email-content">
                <p class="greeting">Dear <strong>{{ $contactMessage->name }}</strong>,</p>
                
                <p class="intro-text">
                    Thank you for reaching out to us. We're pleased to respond to your inquiry:
                </p>
                
                <div class="reply-box">
                    <p class="reply-text">{!! nl2br(e($reply->message)) !!}</p>
                </div>
                
                <div class="signature-section">
                    <p class="signature-text">
                        Warm regards,<br>
                        <span class="signature-name">{{ $staffName }}</span><br>
                        Brickspoint Boutique Aparthotel
                    </p>
                </div>
                
                <div class="original-section">
                    <p class="original-label">
                        Your Original Message 
                        <span class="original-date">• {{ $contactMessage->created_at->format('F d, Y \a\t h:i A') }}</span>
                    </p>
                    <div class="original-box">
                        <p class="original-text">{!! nl2br(e($contactMessage->message)) !!}</p>
                    </div>
                </div>
            </div>
            
            <div class="email-footer">
                <p class="footer-logo">Brickspoint</p>
                <p class="footer-tagline">Boutique Aparthotel</p>
                
                <p class="footer-note">
                    This email was sent in response to your inquiry.<br>
                    If you have any further questions, simply reply to this email.
                </p>
                
                <div class="footer-links">
                    <a href="{{ config('app.url') }}">Visit Website</a>
                    <a href="mailto:{{ config('mail.from.address') }}">Contact Us</a>
                </div>
                
                <p class="copyright">
                    © {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
