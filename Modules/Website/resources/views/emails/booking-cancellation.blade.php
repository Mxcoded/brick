<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled - {{ $booking->booking_reference }}</title>
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

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:35px 30px; text-align:center;">
                            <div style="text-align:center;">
                                <h1 style="color:#C9A962; font-size:26px; font-weight:300; letter-spacing:6px; text-transform:uppercase; margin:0 0 4px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                                <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Cancellation Banner -->
                    <tr>
                        <td style="background-color:#DC2626; padding:18px 20px; text-align:center;">
                            <h2 style="margin:0; color:#FFFFFF; font-size:18px; font-weight:600; letter-spacing:1px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#10007; Booking Cancelled</h2>
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
                                            Your reservation at Brickspoint Boutique Aparthotel has been cancelled as requested.
                                            If you did not request this cancellation, please contact us immediately.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Booking Reference -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1A1A1A; margin-bottom:30px;">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <p style="color:#888888; font-size:10px; letter-spacing:2px; text-transform:uppercase; margin:0 0 6px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Cancelled Booking Reference</p>
                                        <p style="color:#C9A962; font-size:26px; font-weight:700; letter-spacing:4px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{{ $booking->booking_reference }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Reservation Details -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:28px 28px 0 28px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #DC2626; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Cancelled Reservation Details</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 28px 0 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Room Type</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Check-in</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('l, F d, Y') }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Check-out</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('l, F d, Y') }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Status</td>
                                                            <td style="width:60%; text-align:right;">
                                                                <span style="display:inline-block; background-color:#FEE2E2; color:#DC2626; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ ucfirst($booking->status) }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>

                            <!-- Help Note -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FEF3C7; margin-bottom:28px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="30" valign="top" style="font-size:18px; color:#92400E; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#128222;</td>
                                                <td style="color:#92400E; font-size:13px; line-height:1.5; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                                    Need help? Contact us at <strong>{{ config('mail.from.address') }}</strong> or call <strong>{{ config('app.phone') }}</strong>.
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
                                            We hope to welcome you in the future.<br>
                                            <strong style="color:#C9A962;">The Brickspoint Team</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
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
                                @if(config('app.address'))
                                    {{ config('app.address') }}<br>
                                @else
                                    Asokoro, Abuja, Nigeria<br>
                                @endif
                                @if(config('app.phone'))
                                    <a href="tel:{{ config('app.phone') }}" style="color:#C9A962; text-decoration:none;">{{ config('app.phone') }}</a>
                                @endif
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
