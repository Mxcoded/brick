<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking - {{ $booking->booking_reference }}</title>
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

                    <!-- Staff Banner -->
                    <tr>
                        <td style="background-color:#1E3A5F; padding:18px 20px; text-align:center;">
                            <h2 style="margin:0; color:#FFFFFF; font-size:18px; font-weight:600; letter-spacing:1px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#128203; New Booking Received</h2>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:40px 35px 20px 35px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="font-size:14px; color:#666666; margin:0 0 24px 0; line-height:1.6;">A new reservation has been made on the website. Please review the details below and prepare for the guest's arrival.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Booking Reference -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1A1A1A; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <p style="color:#888888; font-size:10px; letter-spacing:2px; text-transform:uppercase; margin:0 0 6px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Booking Reference</p>
                                        <p style="color:#C9A962; font-size:26px; font-weight:700; letter-spacing:4px; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{{ $booking->booking_reference }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Guest Information -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:28px 28px 0 28px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Guest Information</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 28px 0 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Name</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ $booking->guest_name }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Email</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;"><a href="mailto:{{ $booking->guest_email }}" style="color:#1E3A5F; text-decoration:none;">{{ $booking->guest_email }}</a></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @if($booking->guest_phone)
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Phone</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;"><a href="tel:{{ $booking->guest_phone }}" style="color:#1E3A5F; text-decoration:none;">{{ $booking->guest_phone }}</a></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:11px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Guests</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}{{ $booking->children > 0 ? ', ' . $booking->children . ' Child' . ($booking->children > 1 ? 'ren' : '') : '' }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>

                            <!-- Reservation Details -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:20px;">
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
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ optional($booking->roomType)->name ?? 'Not assigned' }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @if(optional($booking->roomUnit)->name)
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Room Unit</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ $booking->roomUnit->name }}</td>
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
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Nights</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Booking Source</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ ucfirst($booking->source ?? 'Website') }}</td>
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
                                                                <span style="display:inline-block; background-color:#D1FAE5; color:#065F46; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ ucfirst($booking->status) }}</span>
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

                            @if($booking->addons->isNotEmpty())
                            <!-- Add-ons -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:28px 28px 0 28px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Add-ons</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 28px 0 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            @foreach($booking->addons as $addon)
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">{{ $addon->pivot->name }}</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">
                                                                @if((int) $addon->pivot->quantity > 1){{ (int) $addon->pivot->quantity }} &times; @endif
                                                                &#x20A6;{{ number_format((float) $addon->pivot->total, 2) }}
                                                                <br><small style="color:#888888; font-weight:400;">@if($addon->pivot->is_per_night)Per night &times; {{ \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) }} nights @else One-time @endif</small>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>
                            @endif

                            <!-- Payment Summary -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:28px 28px 0 28px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Payment Summary</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 28px 0 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Total Amount</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:15px; color:#C9A962; font-weight:700; text-align:right;">&#x20A6;{{ number_format($booking->total_amount, 2) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Amount Paid</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">&#x20A6;{{ number_format($booking->amount_paid ?? 0, 2) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0; border-bottom:1px solid #EDE8E1;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Payment Method</td>
                                                            <td style="width:60%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:600; text-align:right;">{{ str_replace('_', ' ', ucfirst($booking->payment_method ?? 'N/A')) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:11px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="width:40%; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#888888;">Payment Status</td>
                                                            <td style="width:60%; text-align:right;">
                                                                @if($booking->payment_status === 'paid')
                                                                    <span style="display:inline-block; background-color:#D1FAE5; color:#065F46; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Paid</span>
                                                                @elseif($booking->payment_status === 'partial')
                                                                    <span style="display:inline-block; background-color:#FEF3C7; color:#92400E; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Partial</span>
                                                                @else
                                                                    <span style="display:inline-block; background-color:#FEE2E2; color:#991B1B; padding:3px 12px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Pending</span>
                                                                @endif
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

                            @if($booking->special_requests)
                            <!-- Special Requests -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#EFF6FF; border:1px solid #BFDBFE; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#1E3A5F; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 8px 0;">&#128172; Special Requests</p>
                                        <p style="color:#1E3A5F; font-size:13px; line-height:1.6; margin:0;">{{ $booking->special_requests }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if($booking->admin_notes)
                            <!-- Admin Notes -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FEF3C7; border:1px solid #FDE68A; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#92400E; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 8px 0;">&#128221; Internal Notes</p>
                                        <p style="color:#92400E; font-size:13px; line-height:1.6; margin:0;">{{ $booking->admin_notes }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Action Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color:#1E3A5F; padding:0;">
                                                    <a href="{{ route('website.admin.bookings.show', $booking->id) }}" style="display:inline-block; padding:15px 40px; background-color:#1E3A5F; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View in Dashboard</a>
                                                </td>
                                            </tr>
                                        </table>
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
                            <p style="color:#666666; font-size:9px; letter-spacing:3px; text-transform:uppercase; margin:0 0 18px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Boutique Aparthotel &mdash; Staff Notification</p>
                            <p style="color:#555555; font-size:10px; margin:18px 0 0 0; padding-top:18px; border-top:1px solid #333333; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                This is an internal notification. Do not forward to guests.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
