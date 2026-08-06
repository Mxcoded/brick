<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - {{ $booking->booking_reference }}</title>
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
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#2D2D2D" angle="135" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <div style="text-align:center;">
                                <h1 style="color:#C9A962; font-size:26px; font-weight:300; letter-spacing:6px; text-transform:uppercase; margin:0 0 4px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                                <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel</p>
                            </div>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- Confirmation Banner -->
                    <tr>
                        <td style="background-color:#16A34A; padding:18px 20px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#22C55E" color2="#16A34A" angle="0" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <h2 style="margin:0; color:#FFFFFF; font-size:18px; font-weight:600; letter-spacing:1px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#10003; Booking Confirmed</h2>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
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
                                            Thank you for choosing Brickspoint Boutique Aparthotel. Your reservation is confirmed and we look forward to welcoming you.
                                        </p>
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
                                            @if($booking->addons->isNotEmpty())
                                            @foreach($booking->addons as $addon)
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">{{ $addon->pivot->name }}</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">
                                                                @if((int) $addon->pivot->quantity > 1){{ (int) $addon->pivot->quantity }} &times; @endif
                                                                &#x20A6;{{ number_format((float) $addon->pivot->total, 2) }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Total Amount</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:15px; color:#C9A962; font-weight:700; text-align:right;">₦{{ number_format($booking->total_amount, 2) }}</td>
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
                                                                <!--[if mso]>
                                                                <span style="background-color:#D1FAE5; color:#065F46; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase;">{{ ucfirst($booking->status) }}</span>
                                                                <![endif]-->
                                                                <!--[if !mso]><!-->
                                                                <span style="display:inline-block; background-color:#D1FAE5; color:#065F46; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ ucfirst($booking->status) }}</span>
                                                                <!--<![endif]-->
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

                            <!-- Payment Status -->
                            @php
                                $isPayOnArrival = $booking->payment_method === 'pay_on_arrival';
                                $needsPayment = !$isPayOnArrival && $booking->payment_status !== 'paid';
                            @endphp

                            @if($needsPayment)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FEE2E2; border:1px solid #FECACA; margin-bottom:28px;">
                                    <tr>
                                        <td style="padding:20px 24px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td width="36" valign="top" style="font-size:24px; color:#DC2626; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#9888;</td>
                                                    <td style="color:#991B1B; font-size:14px; line-height:1.6; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                                        <strong style="font-size:15px; color:#DC2626; text-transform:uppercase; letter-spacing:0.5px;">Payment Pending</strong><br>
                                                        Your booking is confirmed but payment has not yet been received. Please complete your payment to <strong>secure your reservation</strong>.
                                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;">
                                                            <tr>
                                                                <td align="center" style="background-color:#DC2626; padding:0; border-radius:6px;">
                                                                    <a href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" style="display:inline-block; padding:12px 28px; background-color:#DC2626; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Pay Now</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @elseif($booking->payment_status === 'paid')
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#D1FAE5; border:1px solid #A7F3D0; margin-bottom:28px;">
                                    <tr>
                                        <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td width="30" valign="top" style="font-size:18px; color:#065F46; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#10003;</td>
                                                    <td style="color:#065F46; font-size:13px; line-height:1.5; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Payment received. Your reservation is fully secured.</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @elseif($isPayOnArrival)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#EFF6FF; border:1px solid #BFDBFE; margin-bottom:28px;">
                                    <tr>
                                        <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td width="30" valign="top" style="font-size:18px; color:#1D4ED8; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#128197;</td>
                                                    <td style="color:#1E3A5F; font-size:13px; line-height:1.5; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">You selected <strong>Pay on Arrival</strong>. Payment will be collected at check-in.</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- Info Note -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FEF3C7; margin-bottom:28px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="30" valign="top" style="font-size:18px; color:#92400E; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#128221;</td>
                                                <td style="color:#92400E; font-size:13px; line-height:1.5; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">A specific room will be assigned upon check-in based on availability. Our check-in time is <strong>3:00 PM</strong> and check-out is <strong>12:00 PM</strong>.</td>
                                            </tr>
                                        </table>
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
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" style="height:50px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#B8942E" fillcolor="#C9A962">
                                                    <w:anchorlock/>
                                                    <center style="color:#FFFFFF; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View Booking Online</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <a href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" style="display:inline-block; padding:15px 40px; background-color:#C9A962; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View Booking Online</a>
                                                    <!--<![endif]-->
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
                                            We look forward to welcoming you!<br>
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
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#0D0D0D" angle="180" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
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
