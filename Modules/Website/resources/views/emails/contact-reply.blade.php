<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response from Brickspoint Boutique Aparthotel</title>
    <style>
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
        }
        .ExternalClass, .ReadMsgBody {
            width: 100%;
        }
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass td, .ExternalClass div {
            line-height: 100%;
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F2EFEA; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F2EFEA; padding:30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
                    <tr>
                        <td style="background-color:#1A1A1A; padding:30px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#2D2D2D" angle="135" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <h1 style="color:#C9A962; font-size:24px; font-weight:300; letter-spacing:5px; text-transform:uppercase; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                            <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#C9A962; padding:14px 20px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#C9A962" color2="#D4B978" angle="0" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="margin:0; color:#1A1A1A; font-size:13px; font-weight:700; letter-spacing:0.5px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#9993; Response to Your Inquiry</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#FFFFFF; padding:35px 30px 20px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:16px; color:#2D2D2D; margin:0 0 15px 0; line-height:1.5;">Dear <strong>{{ $contactMessage->name }}</strong>,</p>
                                        <p style="color:#555555; font-size:14px; margin:0 0 25px 0; line-height:1.6;">Thank you for reaching out to us. We're pleased to respond to your inquiry:</p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; border-left:4px solid #C9A962; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:22px 24px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:14px; color:#3D3D3D; line-height:1.8;">{!! nl2br(e($reply->message)) !!}</td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:25px 0 25px 0; padding-top:25px; border-top:1px solid #EDE8E1;">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#555555; font-size:14px; margin:0; line-height:1.8;">
                                            Warm regards,<br>
                                            <strong style="color:#C9A962;">{{ $staffName }}</strong><br>
                                            Brickspoint Boutique Aparthotel
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:25px 0 0 0; padding-top:25px; border-top:1px solid #EDE8E1;">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:10px; font-weight:600; color:#888888; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0;">
                                            Your Original Message
                                            <span style="color:#AAAAAA; font-weight:400;">&bull; {{ $contactMessage->created_at->format('F d, Y \a\t h:i A') }}</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F6F3;">
                                <tr>
                                    <td style="padding:18px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#777777; font-style:italic; line-height:1.7;">{!! nl2br(e($contactMessage->message)) !!}</td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:30px 0 0 0;">
                                <tr>
                                    <td style="border-top:1px solid #EDE8E1;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#1A1A1A; padding:28px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#0D0D0D" angle="180" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="color:#C9A962; font-size:14px; font-weight:300; letter-spacing:3px; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</p>
                            <p style="color:#666666; font-size:9px; letter-spacing:2px; text-transform:uppercase; margin:0 0 15px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            <p style="color:#888888; font-size:11px; margin:0 0 15px 0; line-height:1.7; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                This email was sent in response to your inquiry.<br>
                                If you have any further questions, simply reply to this email.
                            </p>
                            <p style="margin:15px 0;">
                                <a href="{{ config('app.url') }}" style="color:#C9A962; text-decoration:none; font-size:11px; margin:0 8px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Visit Website</a>
                                <a href="mailto:{{ config('mail.from.address') }}" style="color:#C9A962; text-decoration:none; font-size:11px; margin:0 8px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Contact Us</a>
                            </p>
                            <p style="color:#555555; font-size:10px; margin:15px 0 0 0; padding-top:15px; border-top:1px solid #333333; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&copy; {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>