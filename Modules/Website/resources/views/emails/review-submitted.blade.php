<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Review</title>
    <style>
        body, table, td, p, a, li, blockquote { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0; mso-table-rspace: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
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
                            <h1 style="color:#C9A962; font-size:26px; font-weight:300; letter-spacing:6px; text-transform:uppercase; margin:0 0 4px 0;">Brickspoint</h1>
                            <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0;">Boutique Aparthotel</p>
                        </td>
                    </tr>

                    <!-- Success Banner -->
                    <tr>
                        <td style="background-color:#C9A962; padding:30px 20px; text-align:center;">
                            <p style="font-size:40px; margin:0 0 8px 0;">&#10024;</p>
                            <h2 style="margin:0; color:#FFFFFF; font-size:20px; font-weight:600; letter-spacing:1px;">Thank You, {{ $testimonial->guest_name }}!</h2>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:40px 35px 30px 35px;">
                            <p style="font-size:16px; color:#2D2D2D; margin:0 0 16px 0; line-height:1.6;">Dear <strong>{{ $testimonial->guest_name }}</strong>,</p>
                            <p style="color:#666666; font-size:14px; margin:0 0 20px 0; line-height:1.7;">
                                Thank you for taking the time to share your experience at Brickspoint Boutique Aparthotel.
                                We truly value your feedback.
                            </p>

                            <!-- Review Summary -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px; border-radius:8px;">
                                <tr>
                                    <td style="padding:24px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; padding-bottom:10px; border-bottom:2px solid #C9A962;">Your Review</p>
                                        <p style="color:#888888; font-size:13px; margin:0 0 8px 0;">
                                            Type: <strong style="color:#2D2D2D;">{{ $testimonial->typeLabel() }}</strong>
                                        </p>
                                        @if ($testimonial->dining_venue || $testimonial->event_name || $testimonial->stay_type)
                                        <p style="color:#888888; font-size:13px; margin:0 0 8px 0;">
                                            Context: <strong style="color:#2D2D2D;">{{ $testimonial->contextLabel() }}</strong>
                                        </p>
                                        @endif
                                        <p style="color:#888888; font-size:13px; margin:0 0 12px 0;">
                                            Rating: 
                                            @for ($i = 0; $i < 5; $i++)
                                                <span style="color: {{ $i < $testimonial->rating ? '#F59E0B' : '#DDD' }};">&#9733;</span>
                                            @endfor
                                        </p>
                                        <div style="background:#FFFFFF; border-left:3px solid #C9A962; padding:14px 16px; border-radius:4px;">
                                            <p style="color:#555555; font-size:14px; margin:0; font-style:italic; line-height:1.6;">"{{ $testimonial->text }}"</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Moderation Note -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FEF3C7; margin-bottom:28px; border-radius:8px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="30" valign="top" style="font-size:18px; color:#92400E;">&#128221;</td>
                                                <td style="color:#92400E; font-size:13px; line-height:1.5;">
                                                    Your review will be published once it has been reviewed by our team. We appreciate your patience!
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#888888; font-size:14px; margin:0 0 8px 0; line-height:1.7;">
                                Warm regards,<br>
                                <strong style="color:#C9A962;">The Brickspoint Team</strong>
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0 0 0;">
                                <tr>
                                    <td style="border-top:1px solid #EDE8E1;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:30px 30px; text-align:center;">
                            <p style="color:#C9A962; font-size:16px; font-weight:300; letter-spacing:4px; margin:0 0 3px 0;">Brickspoint</p>
                            <p style="color:#666666; font-size:9px; letter-spacing:3px; text-transform:uppercase; margin:0 0 18px 0;">Boutique Aparthotel</p>
                            <p style="color:#999999; font-size:11px; line-height:1.8; margin:0;">
                                @if(config('app.address'))
                                    {{ config('app.address') }}<br>
                                @else
                                    Asokoro, Abuja, Nigeria<br>
                                @endif
                                @if(config('app.phone'))
                                    <a href="tel:{{ config('app.phone') }}" style="color:#C9A962; text-decoration:none;">{{ config('app.phone') }}</a>
                                @endif
                            </p>
                            <p style="color:#555555; font-size:10px; margin:18px 0 0 0; padding-top:18px; border-top:1px solid #333333;">
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
