<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response from Brickspoint</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #d4af37;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .reply-box {
            background: #f8f9fa;
            border-left: 4px solid #d4af37;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .reply-box p {
            margin: 0;
            white-space: pre-wrap;
        }
        .original-message {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .original-message h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .original-message blockquote {
            margin: 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-style: italic;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer a {
            color: #d4af37;
            text-decoration: none;
        }
        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Brickspoint</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Dear {{ $contactMessage->name }},</p>
            
            <p>Thank you for contacting us. Here is our response:</p>
            
            <div class="reply-box">
                <p>{!! nl2br(e($reply->message)) !!}</p>
            </div>
            
            <div class="signature">
                <p>
                    Best regards,<br>
                    <strong>{{ $staffName }}</strong><br>
                    Brickspoint Team
                </p>
            </div>
            
            <div class="original-message">
                <h4>Your Original Message ({{ $contactMessage->created_at->format('M d, Y \a\t h:i A') }}):</h4>
                <blockquote>
                    {!! nl2br(e($contactMessage->message)) !!}
                </blockquote>
            </div>
        </div>
        
        <div class="footer">
            <p>
                This email was sent in response to your inquiry at Brickspoint.<br>
                If you have any further questions, feel free to reply to this email.
            </p>
            <p>
                <a href="{{ config('app.url') }}">Visit Our Website</a> |
                <a href="mailto:{{ config('mail.from.address') }}">Contact Us</a>
            </p>
            <p style="margin-top: 15px; color: #999;">
                © {{ date('Y') }} Brickspoint. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
