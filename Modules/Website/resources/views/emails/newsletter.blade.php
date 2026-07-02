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
            <o:AllowPNG/>
        </o:OfficeDocumentSettings>
    </xml>
    <style>
        table { border-collapse: collapse; }
        td { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; }
    </style>
    <![endif]-->
</head>
<body style="margin:0; padding:0; background-color:#F8F6F3; font-family:'Segoe UI', Tahoma, Arial, sans-serif; line-height:1.7; color:#2D2D2D; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F6F3;">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <!--[if mso]>
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" align="center">
                <tr><td>
                <![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:640px; background-color:#FFFFFF;">
                    <tr>
                        <td>
                            <!-- HEADER -->
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:640px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#2D2D2D" angle="135"/>
                            <v:textbox inset="0,0,0,0" style="mso-fit-shape-to-text:true;">
                            <![endif]-->
                            <div style="background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%); background-color:#1A1A1A; padding:45px 40px 35px; text-align:center;">
                                @if(config('app.logo'))
                                    <img src="{{ config('app.logo') }}" alt="Brickspoint Boutique Aparthotel" style="max-width:200px; height:auto; display:block; margin:0 auto; border:0; outline:none;" border="0">
                                @else
                                    <h1 style="color:#C9A962; font-size:32px; font-weight:300; letter-spacing:6px; margin:0 0 8px 0; text-transform:uppercase; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">Brickspoint</h1>
                                    <p style="color:#B8B8B8; font-size:12px; letter-spacing:3px; text-transform:uppercase; margin:0; font-weight:400; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">Boutique Aparthotel</p>
                                @endif
                            </div>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->

                            <!-- GOLD ACCENT LINE -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="3" style="height:3px; font-size:0; line-height:0; background-color:#C9A962;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- PREVIEW BANNER -->
                            @if($newsletter->preview_text)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color:#C9A962; padding:16px 40px; text-align:center; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
                                        <p style="color:#1A1A1A; font-size:14px; font-weight:600; letter-spacing:0.5px; margin:0;">{{ $newsletter->preview_text }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- GREETING -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:24px 40px 0; font-family:'Segoe UI', Tahoma, Arial, sans-serif; font-size:16px; line-height:1.7; color:#2D2D2D;">
                                        @if($subscriber->name)
                                            <p style="margin:0 0 8px;">Dear <strong>{{ $subscriber->name }}</strong>,</p>
                                        @else
                                            <p style="margin:0 0 8px;">Dear Valued Subscriber,</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- MAIN CONTENT -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:0 40px 45px; font-family:'Segoe UI', Tahoma, Arial, sans-serif; color:#4A4A4A; font-size:16px; line-height:1.75;">
                                        {!! $newsletter->content !!}
                                    </td>
                                </tr>
                            </table>

                            <!-- FOOTER -->
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:640px;">
                            <v:fill type="gradient" color="#0D0D0D" color2="#1A1A1A" angle="180"/>
                            <v:textbox inset="0,0,0,0" style="mso-fit-shape-to-text:true;">
                            <![endif]-->
                            <div style="background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%); background-color:#1A1A1A; padding:45px 40px 35px; text-align:center;">
                                <p style="color:#C9A962; font-size:22px; font-weight:300; letter-spacing:4px; margin:0 0 5px 0; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">Brickspoint</p>
                                <p style="color:#888888; font-size:11px; letter-spacing:2px; text-transform:uppercase; margin:0 0 25px 0; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">Boutique Aparthotel</p>

                                <p style="color:#AAAAAA; font-size:13px; line-height:1.8; margin-bottom:25px; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
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

                                <!-- SOCIAL LINKS -->
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="padding:20px 0; border-top:1px solid #333333; border-bottom:1px solid #333333; text-align:center; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
                                            @if(config('app.facebook'))
                                                <a href="{{ config('app.facebook') }}" style="display:inline-block; margin:0 6px; padding:8px 16px; color:#C9A962; font-size:13px; font-weight:500; letter-spacing:1px; text-decoration:none; text-transform:uppercase; border:1px solid #444444;">Facebook</a>
                                            @endif
                                            @if(config('app.instagram'))
                                                <a href="{{ config('app.instagram') }}" style="display:inline-block; margin:0 6px; padding:8px 16px; color:#C9A962; font-size:13px; font-weight:500; letter-spacing:1px; text-decoration:none; text-transform:uppercase; border:1px solid #444444;">Instagram</a>
                                            @endif
                                            @if(config('app.twitter'))
                                                <a href="{{ config('app.twitter') }}" style="display:inline-block; margin:0 6px; padding:8px 16px; color:#C9A962; font-size:13px; font-weight:500; letter-spacing:1px; text-decoration:none; text-transform:uppercase; border:1px solid #444444;">Twitter</a>
                                            @endif
                                            @if(config('app.website'))
                                                <a href="{{ config('app.website') }}" style="display:inline-block; margin:0 6px; padding:8px 16px; color:#C9A962; font-size:13px; font-weight:500; letter-spacing:1px; text-decoration:none; text-transform:uppercase; border:1px solid #444444;">Website</a>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                <p style="color:#777777; font-size:12px; line-height:1.7; margin:20px 0 0 0; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
                                    You're receiving this email because you subscribed to our newsletter<br>
                                    for exclusive offers and updates from Brickspoint Boutique Aparthotel.
                                </p>

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="padding:20px 0 0; border-top:1px solid #333333; margin-top:25px; text-align:center; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
                                            <p style="color:#666666; font-size:11px; margin:0 0 8px 0;">No longer wish to receive these emails?</p>
                                            <a href="{{ $unsubscribeUrl }}" style="color:#888888; font-size:11px; text-decoration:underline;">Unsubscribe from our mailing list</a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="color:#555555; font-size:11px; margin-top:20px; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
                                    &copy; {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                                </p>
                            </div>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                </table>
                <!--[if mso]>
                </td></tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
