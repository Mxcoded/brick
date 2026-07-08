<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Staff Account Has Been Created</title>
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
                            <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Staff Portal</p>
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
                            <p style="margin:0; color:#1A1A1A; font-size:13px; font-weight:700; letter-spacing:0.5px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Welcome! Your Account Has Been Created</p>
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
                                        <p style="color:#555555; font-size:15px; margin:0 0 25px 0; line-height:1.6;">Hi <strong>{{ $name }}</strong>,</p>
                                        <p style="color:#555555; font-size:14px; margin:0 0 25px 0; line-height:1.6;">
                                            A staff account has been created for you on the <strong>Brickspoint ERP</strong> system. Use the credentials below to log in and access the staff portal.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:24px 24px 0 24px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Login Credentials</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 24px 0 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="width:120px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:10px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Email</td>
                                                <td style="font-family:'SF Mono','Fira Code',monospace; font-size:14px; color:#2D2D2D; font-weight:500; padding:10px 0; border-bottom:1px solid #EDE8E1;">{{ $email }}</td>
                                            </tr>
                                            <tr>
                                                <td style="width:120px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:10px 0; vertical-align:top;">Password</td>
                                                <td style="font-family:'SF Mono','Fira Code',monospace; font-size:14px; color:#2D2D2D; font-weight:500; padding:10px 0;">{{ $password }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFF8E1; border-left:4px solid #C9A962; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#666666; line-height:1.5;">
                                        <strong>&#128274; Security Notice:</strong> For security reasons, please change your password after your first login. If you did not request this account, please contact the IT department immediately.
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color:#C9A962; padding:0;">
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $loginUrl }}" style="height:46px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#B8942E" fillcolor="#C9A962">
                                                    <w:anchorlock/>
                                                    <center style="color:#FFFFFF; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Log In to Staff Portal</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <a href="{{ $loginUrl }}" style="display:inline-block; padding:14px 32px; background-color:#C9A962; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Log In to Staff Portal</a>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
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
                            <p style="color:#666666; font-size:9px; letter-spacing:2px; text-transform:uppercase; margin:0 0 15px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Hospitality Management System</p>
                            <p style="color:#888888; font-size:10px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">This is an automated message. Do not reply directly.</p>
                            <p style="color:#555555; font-size:10px; margin:15px 0 0 0; padding-top:15px; border-top:1px solid #333333; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&copy; {{ date('Y') }} Brickspoint. All rights reserved.</p>
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