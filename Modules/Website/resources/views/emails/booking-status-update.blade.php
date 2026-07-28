<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $statusLabel }} — {{ $booking->booking_reference }}</title>
    <style>
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td { mso-table-lspace: 0; mso-table-rspace: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        .ExternalClass, .ReadMsgBody { width: 100%; }
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass td, .ExternalClass div { line-height: 100%; }
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

                    <!-- Status Banner -->
                    <tr>
                        <td style="padding:0;">
                            @if($booking->status === 'checked_in')
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0EA5E9;">
                            @elseif($booking->status === 'completed')
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#8B5CF6;">
                            @else
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#16A34A;">
                            @endif
                                <tr>
                                    <td style="padding:18px 20px; text-align:center;">
                                        <h2 style="margin:0; color:#FFFFFF; font-size:18px; font-weight:600; letter-spacing:1px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                            @if($booking->status === 'checked_in')
                                                &#128682; You're Checked In
                                            @elseif($booking->status === 'completed')
                                                &#10024; Thank You for Staying
                                            @else
                                                &#10003; {{ $statusLabel }}
                                            @endif
                                        </h2>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:40px 35px 20px 35px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:17px; color:#2D2D2D; margin:0 0 12px 0; line-height:1.5;">Dear <strong>{{ $booking->guest_name }}</strong>,</p>
                                        @if($booking->status === 'checked_in')
                                            <p style="color:#666666; font-size:14px; margin:0 0 28px 0; line-height:1.7;">
                                                Welcome to Brickspoint Boutique Aparthotel! Your check-in is complete and your room is ready. We hope you enjoy your stay.
                                            </p>
                                        @elseif($booking->status === 'completed')
                                            <p style="color:#666666; font-size:14px; margin:0 0 28px 0; line-height:1.7;">
                                                Thank you for choosing Brickspoint Boutique Aparthotel. We hope you had a wonderful stay and look forward to welcoming you again.
                                            </p>
                                        @else
                                            <p style="color:#666666; font-size:14px; margin:0 0 28px 0; line-height:1.7;">
                                                Your booking status has been updated to <strong>{{ $statusLabel }}</strong>. Please find the details below.
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Booking Reference -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1A1A1A; margin-bottom:30px;">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <p style="color:#888888; font-size:10px; letter-spacing:2px; text-transform:uppercase; margin:0 0 6px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Booking Reference</p>
                                        <p style="color:#C9A962; font-size:26px; font-weight:700; letter-spacing:4px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{{ $booking->booking_reference }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Reservation Details -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:28px 28px 0 28px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Reservation Details</p>
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
                                            @if($booking->roomUnit)
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Room Number</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">Room {{ $booking->roomUnit->room_number }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @endif
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
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Guests</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}{{ $booking->children > 0 ? ', ' . $booking->children . ' Child' . ($booking->children > 1 ? 'ren' : '') : '' }}</td>
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
                                                                <span style="display:inline-block; background-color:#D1FAE5; color:#065F46; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ $statusLabel }}</span>
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

                            @if($booking->status === 'checked_in')
                            <!-- Check-in Info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#EFF6FF; border:1px solid #BFDBFE; margin-bottom:28px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="30" valign="top" style="font-size:18px; color:#1D4ED8;">&#128197;</td>
                                                <td style="color:#1E3A5F; font-size:13px; line-height:1.5;">
                                                    Check-in time is <strong>3:00 PM</strong> and check-out is <strong>12:00 PM</strong>.
                                                    If you have any questions, please contact our front desk.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if($booking->status === 'completed')
                            <!-- Checkout Info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F3FF; border:1px solid #DDD6FE; margin-bottom:28px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="30" valign="top" style="font-size:18px; color:#7C3AED;">&#11088;</td>
                                                <td style="color:#5B21B6; font-size:13px; line-height:1.5;">
                                                    We hope you enjoyed your stay at Brickspoint. If you have a moment, we would love to hear your feedback.
                                                    Safe travels and we look forward to welcoming you again!
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color:#C9A962; padding:0;">
                                                    <a href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" style="display:inline-block; padding:15px 40px; background-color:#C9A962; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View Booking Online</a>
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
                                            @if($booking->status === 'checked_in')
                                                Enjoy your stay!<br>
                                            @elseif($booking->status === 'completed')
                                                Until next time!<br>
                                            @else
                                                If you have any questions, please don't hesitate to reach out.<br>
                                            @endif
                                            <strong style="color:#C9A962;">The Brickspoint Team</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:30px 0 0 0;">
                                <tr><td style="border-top:1px solid #EDE8E1;"></td></tr>
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
