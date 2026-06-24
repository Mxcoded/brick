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
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="max-width:640px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:35px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:640px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#2D2D2D" angle="135" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            @if(config('app.logo'))
                                <img src="{{ config('app.logo') }}" alt="Brickspoint Boutique Aparthotel" style="max-width:200px; height:auto; display:block; margin:0 auto;">
                            @else
                                <h1 style="color:#C9A962; font-size:28px; font-weight:300; letter-spacing:6px; text-transform:uppercase; margin:0 0 6px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                                <p style="color:#B8B8B8; font-size:11px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            @endif
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- Preview Banner -->
                    @if($newsletter->preview_text)
                    <tr>
                        <td style="background-color:#C9A962; padding:14px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:640px;">
                            <v:fill type="gradient" color="#C9A962" color2="#D4B978" angle="0" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="margin:0; color:#1A1A1A; font-size:13px; font-weight:600; letter-spacing:0.5px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{{ $newsletter->preview_text }}</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                    @endif

                    <!-- Greeting -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:24px 35px 0 35px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:15px; line-height:1.7; color:#2D2D2D;">
                            @if($subscriber->name)
                                <p style="margin:0 0 8px;">Dear <strong>{{ $subscriber->name }}</strong>,</p>
                            @else
                                <p style="margin:0 0 8px;">Dear Valued Subscriber,</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:0 35px 25px 35px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:15px; line-height:1.75; color:#4A4A4A;">
                            {!! $newsletter->content !!}
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="background-color:#FFFFFF;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:0 35px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="border-top:1px solid #E5E0D8;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:35px 30px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:640px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#0D0D0D" angle="180" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="color:#C9A962; font-size:18px; font-weight:300; letter-spacing:4px; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</p>
                            <p style="color:#888888; font-size:10px; letter-spacing:2px; text-transform:uppercase; margin:0 0 20px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            <p style="color:#AAAAAA; font-size:12px; line-height:1.8; margin:0 0 20px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                @if(config('app.address'))
                                    {{ config('app.address') }}<br>
                                @endif
                                @if(config('app.phone'))
                                    <a href="tel:{{ config('app.phone') }}" style="color:#C9A962; text-decoration:none;">{{ config('app.phone') }}</a><br>
                                @endif
                                @if(config('app.email'))
                                    <a href="mailto:{{ config('app.email') }}" style="color:#C9A962; text-decoration:none;">{{ config('app.email') }}</a>
                                @endif
                            </p>

                            <!-- Social Links -->
                            @if(config('app.facebook') || config('app.instagram') || config('app.twitter') || config('app.website'))
                            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 20px;">
                                <tr>
                                    <td style="padding:15px 0; border-top:1px solid #333333; border-bottom:1px solid #333333;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                @if(config('app.facebook'))
                                                <td style="padding:0 5px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="border:1px solid #444444; padding:6px 14px;">
                                                                <a href="{{ config('app.facebook') }}" style="color:#C9A962; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:500; letter-spacing:1px; text-transform:uppercase;">Facebook</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endif
                                                @if(config('app.instagram'))
                                                <td style="padding:0 5px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="border:1px solid #444444; padding:6px 14px;">
                                                                <a href="{{ config('app.instagram') }}" style="color:#C9A962; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:500; letter-spacing:1px; text-transform:uppercase;">Instagram</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endif
                                                @if(config('app.twitter'))
                                                <td style="padding:0 5px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="border:1px solid #444444; padding:6px 14px;">
                                                                <a href="{{ config('app.twitter') }}" style="color:#C9A962; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:500; letter-spacing:1px; text-transform:uppercase;">Twitter</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endif
                                                @if(config('app.website'))
                                                <td style="padding:0 5px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="border:1px solid #444444; padding:6px 14px;">
                                                                <a href="{{ config('app.website') }}" style="color:#C9A962; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:500; letter-spacing:1px; text-transform:uppercase;">Website</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="color:#777777; font-size:11px; line-height:1.7; margin:0 0 20px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                You're receiving this email because you subscribed to our newsletter<br>
                                for exclusive offers and updates from Brickspoint Boutique Aparthotel.
                            </p>

                            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 20px; padding-top:15px; border-top:1px solid #333333;">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#666666; font-size:10px; margin:0 0 6px 0;">No longer wish to receive these emails?</p>
                                        <a href="{{ $unsubscribeUrl }}" style="color:#888888; font-size:10px; text-decoration:underline;">Unsubscribe from our mailing list</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#555555; font-size:10px; margin:15px 0 0 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                &copy; {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                            </p>
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