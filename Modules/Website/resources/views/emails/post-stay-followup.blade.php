<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank you for staying with us</title>
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
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F2EFEA; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F2EFEA; padding:30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:35px 30px; text-align:center;">
                            <div style="text-align:center;">
                                <h1 style="color:#C9A962; font-size:26px; font-weight:300; letter-spacing:6px; text-transform:uppercase; margin:0 0 4px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                                <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Banner -->
                    <tr>
                        <td style="background-color:#C9A962; padding:18px 20px; text-align:center;">
                            <h2 style="margin:0; color:#FFFFFF; font-size:18px; font-weight:600; letter-spacing:1px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Thank You for Staying With Us</h2>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:40px 35px 20px 35px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:17px; color:#2D2D2D; margin:0 0 12px 0; line-height:1.5;">Dear <strong>{{ $booking->guest_name }}</strong>,</p>
                                        <p style="color:#666666; font-size:14px; margin:0 0 28px 0; line-height:1.7;">
                                            Thank you for choosing Brickspoint Boutique Aparthotel. We hope you had a wonderful stay and look forward to welcoming you again.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Booking Reference -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1A1A1A; margin-bottom:30px;">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <p style="color:#888888; font-size:10px; letter-spacing:2px; text-transform:uppercase; margin:0 0 6px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Stay Reference</p>
                                        <p style="color:#C9A962; font-size:26px; font-weight:700; letter-spacing:4px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{{ $booking->booking_reference }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color:#C9A962; padding:0;">
                                                    <a href="{{ route('website.book') }}" style="display:inline-block; padding:15px 40px; background-color:#C9A962; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Book Your Next Stay</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Closing -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#888888; font-size:14px; margin:0; line-height:1.7;">
                                            We can't wait to host you again!<br>
                                            <strong style="color:#C9A962;">The Brickspoint Team</strong>
                                        </p>
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

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:30px 30px; text-align:center;">
                            <p style="color:#C9A962; font-size:16px; font-weight:300; letter-spacing:4px; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</p>
                            <p style="color:#666666; font-size:9px; letter-spacing:3px; text-transform:uppercase; margin:0 0 18px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            <p style="color:#999999; font-size:11px; line-height:1.8; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                @if(config('app.address')){{ config('app.address') }}<br>@else Asokoro, Abuja, Nigeria<br>@endif
                                @if(config('app.phone'))<a href="tel:{{ config('app.phone') }}" style="color:#C9A962; text-decoration:none;">{{ config('app.phone') }}</a>@endif
                            </p>
                            <p style="color:#555555; font-size:10px; margin:18px 0 0 0; padding-top:18px; border-top:1px solid #333333; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                &copy; {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
