<!DOCTYPE html>
<html>
<head>
    <title>Checkout Receipt</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
        <div style="text-align: center; background: #C8A165; padding: 25px; color: #fff;">
            <h2 style="margin: 0;">Thank You for Staying!</h2>
        </div>

        <div style="padding: 25px; background: #f9f9f9;">
            <p>Dear <strong>{{ $registration->full_name }}</strong>,</p>
            <p>We hope you enjoyed your stay at Brickspoint Boutique Aparthotel. Please find your invoice attached to this email.</p>

            <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Reservation:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">#{{ $registration->reservation_code }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Room:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $registration->roomUnit->room_number ?? $registration->room_allocation ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Check-in:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $registration->check_in?->format('M d, Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Check-out:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $registration->check_out?->format('M d, Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Nights:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $registration->no_of_nights }}</td>
                </tr>
            </table>

            <p style="margin-top: 20px; padding: 12px; background: #e8f5e9; border-radius: 4px; text-align: center;">
                <strong>Invoice attached to this email.</strong>
            </p>
        </div>

        <div style="text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #ddd;">
            <p style="margin: 0; color: #C8A165; font-weight: bold;">Brickspoint Boutique Aparthotel</p>
            <p style="font-size: 12px; color: #999; margin: 5px 0;">Abuja, Nigeria</p>
            <p style="font-size: 11px; color: #aaa;">We look forward to welcoming you again!</p>
        </div>
    </div>
</body>
</html>
