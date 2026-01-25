<!DOCTYPE html>
<html>
<head>
    <title>Registration Status</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
        <div style="text-align: center; background: #1a1a1a; padding: 20px; color: #fff;">
            <h2>{{ $headline }}</h2>
        </div>
        
        <div style="padding: 20px; background: #f9f9f9;">
            <p>Dear <strong>{{ $registration->full_name }}</strong>,</p>
            <p>{{ $messageBody }}</p>
            
            <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Room:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $registration->room_allocation ?? 'Assigned upon arrival' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Check-in:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ \Carbon\Carbon::parse($registration->check_in)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Reference:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">#{{ $registration->id }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
            <p>&copy; {{ date('Y') }} Brickspoint Aparthotel. All rights reserved.</p>
        </div>
    </div>
</body>
</html>