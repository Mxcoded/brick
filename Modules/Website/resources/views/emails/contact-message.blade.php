<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
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
            padding: 35px 40px;
            text-align: center;
        }
        .logo-text {
            color: #C9A962;
            font-size: 26px;
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
        .alert-banner {
            background: linear-gradient(90deg, #C9A962 0%, #D4B978 100%);
            padding: 14px 30px;
            text-align: center;
        }
        .alert-banner p {
            margin: 0;
            color: #1A1A1A;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .alert-banner i {
            margin-right: 8px;
        }
        .email-content {
            padding: 40px;
        }
        .info-grid {
            background: #FAF8F5;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #EDE8E1;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 100px;
            font-size: 12px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            flex: 1;
            font-size: 15px;
            color: #2D2D2D;
            font-weight: 500;
        }
        .info-value a {
            color: #B8942E;
            text-decoration: none;
        }
        .message-section {
            margin-top: 25px;
        }
        .message-label {
            font-size: 12px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .message-box {
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F2ED 100%);
            border-left: 4px solid #C9A962;
            border-radius: 0 12px 12px 0;
            padding: 25px;
        }
        .message-text {
            margin: 0;
            color: #4A4A4A;
            font-size: 15px;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .email-footer {
            background: #F8F6F3;
            padding: 25px 40px;
            text-align: center;
            border-top: 1px solid #EDE8E1;
        }
        .footer-text {
            margin: 0;
            color: #888888;
            font-size: 12px;
        }
        .footer-text a {
            color: #B8942E;
            text-decoration: none;
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
            
            <div class="alert-banner">
                <p>📩 New Contact Form Submission</p>
            </div>
            
            <div class="email-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">From</div>
                        <div class="info-value">{{ $data['name'] }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value"><a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Received</div>
                        <div class="info-value">{{ now()->format('F d, Y \a\t h:i A') }}</div>
                    </div>
                </div>
                
                <div class="message-section">
                    <p class="message-label">Message Content</p>
                    <div class="message-box">
                        <p class="message-text">{!! nl2br(e($data['message'])) !!}</p>
                    </div>
                </div>
            </div>
            
            <div class="email-footer">
                <p class="footer-text">
                    Reply directly to this email or <a href="mailto:{{ $data['email'] }}">click here</a> to respond to the guest.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
