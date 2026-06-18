<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Staff Account Has Been Created</title>
    <style>
        body, html {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7; color: #2D2D2D; background-color: #F8F6F3;
        }
        .email-wrapper { width: 100%; background: linear-gradient(180deg, #F8F6F3 0%, #EDE8E1 100%); padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 40px rgba(45,45,45,0.12); }
        .email-header { background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%); padding: 35px 40px; text-align: center; }
        .logo-text { color: #C9A962; font-size: 26px; font-weight: 300; letter-spacing: 5px; margin: 0 0 5px 0; text-transform: uppercase; }
        .tagline { color: #888888; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
        .alert-banner { padding: 16px 30px; text-align: center; background: linear-gradient(90deg, #C9A962 0%, #D4B978 100%); }
        .alert-banner p { margin: 0; color: #1A1A1A; font-size: 14px; font-weight: 600; letter-spacing: 0.5px; }
        .email-content { padding: 40px; }
        .greeting { font-size: 16px; color: #555555; margin: 0 0 25px 0; }
        .credentials-card { background: #FAF8F5; border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        .credentials-title { font-size: 13px; font-weight: 600; color: #1A1A1A; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 18px 0; padding-bottom: 10px; border-bottom: 2px solid #C9A962; }
        .cred-row { display: flex; padding: 10px 0; border-bottom: 1px solid #EDE8E1; }
        .cred-row:last-child { border-bottom: none; }
        .cred-label { width: 120px; font-size: 13px; font-weight: 600; color: #888888; text-transform: uppercase; letter-spacing: 0.5px; }
        .cred-value { flex: 1; font-size: 14px; color: #2D2D2D; font-weight: 500; font-family: 'SF Mono', 'Fira Code', monospace; }
        .notice-box { background: #FFF8E1; border-left: 4px solid #C9A962; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; font-size: 13px; color: #666; }
        .cta-section { text-align: center; margin-top: 30px; }
        .cta-button { display: inline-block; padding: 14px 35px; background: linear-gradient(135deg, #C9A962 0%, #B8942E 100%); color: #FFFFFF !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 4px 15px rgba(201,169,98,0.35); }
        .email-footer { background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%); padding: 30px 40px; text-align: center; }
        .footer-logo { color: #C9A962; font-size: 16px; font-weight: 300; letter-spacing: 3px; margin: 0 0 5px 0; }
        .footer-tagline { color: #666666; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 15px 0; }
        .footer-text { color: #888888; font-size: 11px; margin: 0; }
        .copyright { color: #555555; font-size: 10px; margin: 15px 0 0 0; padding-top: 15px; border-top: 1px solid #333333; }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-content { padding: 25px; }
            .credentials-card { padding: 20px; }
            .cred-row { flex-direction: column; }
            .cred-label { width: 100%; margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="logo-text">Brickspoint</h1>
                <p class="tagline">Staff Portal</p>
            </div>

            <div class="alert-banner">
                <p>Welcome! Your Account Has Been Created</p>
            </div>

            <div class="email-content">
                <p class="greeting">Hi {{ $name }},</p>
                <p style="color: #555; margin-bottom: 25px;">
                    A staff account has been created for you on the <strong>Brickspoint ERP</strong> system.
                    Use the credentials below to log in and access the staff portal.
                </p>

                <div class="credentials-card">
                    <h3 class="credentials-title">Login Credentials</h3>
                    <div class="cred-row">
                        <span class="cred-label">Email</span>
                        <span class="cred-value">{{ $email }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Password</span>
                        <span class="cred-value">{{ $password }}</span>
                    </div>
                </div>

                <div class="notice-box">
                    <strong>🔒 Security Notice:</strong> For security reasons, please change your password after your first login. If you did not request this account, please contact the IT department immediately.
                </div>

                <div class="cta-section">
                    <a href="{{ $loginUrl }}" class="cta-button">
                        Log In to Staff Portal
                    </a>
                </div>
            </div>

            <div class="email-footer">
                <p class="footer-logo">Brickspoint</p>
                <p class="footer-tagline">Hospitality Management System</p>
                <p class="footer-text">This is an automated message. Do not reply directly.</p>
                <p class="copyright">&copy; {{ date('Y') }} Brickspoint. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
