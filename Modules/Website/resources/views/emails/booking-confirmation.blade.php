<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - {{ $booking->booking_reference }}</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7;
            color: #2D2D2D;
            background-color: #F8F6F3;
        }
        .email-wrapper {
            width: 100%;
            background: linear-gradient(180deg, #F8F6F3 0%, #EDE8E1 100%);
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(45, 45, 45, 0.12);
        }
        .email-header {
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            padding: 40px;
            text-align: center;
        }
        .logo-text {
            color: #C9A962;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 5px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .tagline {
            color: #888888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }
        .confirmation-banner {
            background: linear-gradient(90deg, #22C55E 0%, #16A34A 100%);
            padding: 20px 30px;
            text-align: center;
        }
        .confirmation-banner h2 {
            margin: 0;
            color: #FFFFFF;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .confirmation-banner .checkmark {
            font-size: 24px;
            margin-right: 10px;
        }
        .email-content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            color: #2D2D2D;
            margin: 0 0 15px 0;
        }
        .intro-text {
            color: #555555;
            font-size: 15px;
            margin: 0 0 30px 0;
        }
        .booking-ref-box {
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-bottom: 30px;
        }
        .booking-ref-label {
            color: #888888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 8px 0;
        }
        .booking-ref-value {
            color: #C9A962;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 3px;
            margin: 0;
        }
        .details-grid {
            background: #FAF8F5;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .details-title {
            font-size: 14px;
            font-weight: 600;
            color: #1A1A1A;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #C9A962;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #EDE8E1;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #777777;
            font-size: 14px;
        }
        .detail-value {
            color: #2D2D2D;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
        }
        .detail-value.highlight {
            color: #C9A962;
            font-size: 16px;
        }
        .status-confirmed {
            display: inline-block;
            background: #D1FAE5;
            color: #065F46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-note {
            background: #FEF3C7;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .info-note .icon {
            font-size: 18px;
        }
        .info-note p {
            margin: 0;
            color: #92400E;
            font-size: 13px;
            line-height: 1.5;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #C9A962 0%, #B8942E 100%);
            color: #FFFFFF !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.35);
        }
        .closing-text {
            color: #555555;
            font-size: 15px;
            margin: 30px 0 0 0;
            text-align: center;
        }
        .email-footer {
            background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%);
            padding: 35px 40px;
            text-align: center;
        }
        .footer-logo {
            color: #C9A962;
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 3px;
            margin: 0 0 5px 0;
        }
        .footer-tagline {
            color: #666666;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 20px 0;
        }
        .footer-contact {
            color: #999999;
            font-size: 12px;
            line-height: 1.8;
            margin: 0;
        }
        .footer-contact a {
            color: #C9A962;
            text-decoration: none;
        }
        .copyright {
            color: #555555;
            font-size: 11px;
            margin: 20px 0 0 0;
            padding-top: 20px;
            border-top: 1px solid #333333;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-content { padding: 25px; }
            .details-grid { padding: 20px; }
            .booking-ref-value { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="logo-text">Brickspoint</h1>
                <p class="tagline">Boutique Aparthotel</p>
            </div>
            
            <div class="confirmation-banner">
                <h2><span class="checkmark">✓</span> Booking Confirmed!</h2>
            </div>
            
            <div class="email-content">
                <p class="greeting">Dear <strong>{{ $booking->guest_name }}</strong>,</p>
                <p class="intro-text">
                    Thank you for choosing Brickspoint Boutique Aparthotel. We are delighted to confirm your reservation and look forward to providing you with an exceptional stay.
                </p>
                
                <div class="booking-ref-box">
                    <p class="booking-ref-label">Booking Reference</p>
                    <p class="booking-ref-value">{{ $booking->booking_reference }}</p>
                </div>
                
                <div class="details-grid">
                    <h3 class="details-title">Reservation Details</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Room Type</span>
                        <span class="detail-value">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Check-in</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('l, F d, Y') }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Check-out</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('l, F d, Y') }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Guests</span>
                        <span class="detail-value">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}{{ $booking->children > 0 ? ', ' . $booking->children . ' Child' . ($booking->children > 1 ? 'ren' : '') : '' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Amount</span>
                        <span class="detail-value highlight">₦{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="status-confirmed">{{ ucfirst($booking->status) }}</span></span>
                    </div>
                </div>
                
                <div class="info-note">
                    <span class="icon">📝</span>
                    <p>A specific room will be assigned upon check-in based on availability. Our check-in time is 2:00 PM and check-out is 12:00 PM.</p>
                </div>
                
                <div class="cta-section">
                    <a href="{{ route('website.booking.confirmation', $booking->booking_reference) }}" class="cta-button">
                        View Booking Online
                    </a>
                </div>
                
                <p class="closing-text">
                    We look forward to welcoming you!<br>
                    <strong style="color: #C9A962;">The Brickspoint Team</strong>
                </p>
            </div>
            
            <div class="email-footer">
                <p class="footer-logo">Brickspoint</p>
                <p class="footer-tagline">Boutique Aparthotel</p>
                
                <p class="footer-contact">
                    @if(config('app.address'))
                        {{ config('app.address') }}<br>
                    @else
                        Asokoro, Abuja, Nigeria<br>
                    @endif
                    @if(config('app.phone'))
                        <a href="tel:{{ config('app.phone') }}">{{ config('app.phone') }}</a>
                    @endif
                </p>
                
                <p class="copyright">
                    © {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
