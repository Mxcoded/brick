<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        table { mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F2EFEA; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F2EFEA; padding:30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
                    <tr>
                        <td style="background-color:#1A1A1A; padding:30px; text-align:center;">
                            <h1 style="color:#C9A962; font-size:24px; font-weight:300; letter-spacing:5px; text-transform:uppercase; margin:0;">Brickspoint</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#C9A962; padding:14px 20px; text-align:center;">
                            <p style="margin:0; color:#1A1A1A; font-size:13px; font-weight:700; letter-spacing:0.5px;">Registration Confirmed</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#FFFFFF; padding:35px 30px;">
                            <p style="color:#555555; font-size:15px; margin:0 0 25px 0; line-height:1.6;">Hi <strong>{{ $lead->name }}</strong>,</p>
                            <div style="color:#555555; font-size:14px; line-height:1.8;">{!! $body !!}</div>
                            @if ($event->code)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin:25px 0;">
                                <tr>
                                    <td style="padding:20px; text-align:center;">
                                        <p style="font-size:11px; color:#888888; text-transform:uppercase; letter-spacing:1px; margin:0 0 6px 0;">Your Registration Code</p>
                                        <p style="font-size:24px; color:#C9A962; font-weight:700; letter-spacing:3px; margin:0; font-family:'SF Mono','Fira Code',monospace;">{{ $event->code }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:30px 0 0 0;">
                                <tr><td style="border-top:1px solid #EDE8E1;"></td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#1A1A1A; padding:28px 30px; text-align:center;">
                            <p style="color:#C9A962; font-size:14px; font-weight:300; letter-spacing:3px; margin:0 0 3px 0;">Brickspoint</p>
                            <p style="color:#888888; font-size:10px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">This is an automated message. Do not reply directly.</p>
                            <p style="color:#555555; font-size:10px; margin:15px 0 0 0; padding-top:15px; border-top:1px solid #333333;">&copy; {{ date('Y') }} Brickspoint. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
