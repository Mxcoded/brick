<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
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
                            <p style="margin:0; color:#1A1A1A; font-size:13px; font-weight:700; letter-spacing:0.5px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#128232; New Contact Form Submission</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#FFFFFF; padding:35px 30px 20px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:24px 24px 0 24px;"></td>
                                </tr>
                                <tr>
                                    <td style="padding:0 24px 0 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="width:100px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:10px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">From</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:14px; color:#2D2D2D; font-weight:500; padding:10px 0; border-bottom:1px solid #EDE8E1;">{{ $data['name'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="width:100px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:10px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Email</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:14px; color:#2D2D2D; font-weight:500; padding:10px 0; border-bottom:1px solid #EDE8E1;"><a href="mailto:{{ $data['email'] }}" style="color:#B8942E; text-decoration:none;">{{ $data['email'] }}</a></td>
                                            </tr>
                                            <tr>
                                                <td style="width:100px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:10px 0; vertical-align:top;">Received</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:14px; color:#2D2D2D; font-weight:500; padding:10px 0;">{{ now()->format('F d, Y \a\t h:i A') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:0 0 10px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:11px; font-weight:600; color:#888888; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 12px 0;">Message Content</p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; border-left:4px solid #C9A962; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:22px 24px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:14px; color:#4A4A4A; line-height:1.8;">{!! nl2br(e($data['message'])) !!}</td>
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
                        <td style="background-color:#F8F6F3; padding:20px 30px; text-align:center; border-top:1px solid #EDE8E1;">
                            <p style="margin:0; color:#888888; font-size:11px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                Reply directly to this email or <a href="mailto:{{ $data['email'] }}" style="color:#B8942E; text-decoration:none;">click here</a> to respond to the guest.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>