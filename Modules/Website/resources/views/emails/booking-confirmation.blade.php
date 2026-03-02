<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
    <style>
        body { font-family: 'Outfit', sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .header { background: #1a1a1a; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .details { margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 30px; }
        .btn { display: inline-block; background: #d4a017; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Booking Confirmed!</h2>
        </div>
        
        <p>Dear <strong>{{ $booking->guest_name }}</strong>,</p>
        <p>Thank you for choosing Brickspoint ApartHotel. We are pleased to confirm your reservation.</p>

        <div class="details">
            <h3>Reservation Details</h3>
            <p><strong>Reference:</strong> {{ $booking->booking_reference }}</p>
            <p><strong>Room Type:</strong> {{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</p>
            <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($booking->check_in_date)->format('D, M d, Y') }}</p>
            <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($booking->check_out_date)->format('D, M d, Y') }}</p>
            <p><strong>Guests:</strong> {{ $booking->adults }} Adults, {{ $booking->children }} Children</p>
            <p><strong>Total Amount:</strong> ₦{{ number_format($booking->total_amount, 2) }}</p>
            <p><strong>Status:</strong> <span style="color: green;">{{ ucfirst($booking->status) }}</span></p>
            <p><em>Note: A specific room will be assigned at check-in.</em></p>
        </div>

        <p style="text-align: center;">
            <a href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" class="btn">View Booking Online</a>
        </p>

        <p>We look forward to welcoming you!</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Brickspoint ApartHotel. All rights reserved.</p>
            <p>123 Luxury Ave, Abuja, Nigeria | +234 800 123 4567</p>
        </div>
    </div>
</body>
</html>