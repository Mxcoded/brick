<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationType === 'new' ? 'New Maintenance Request' : 'Maintenance Status Update' }}</title>
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
            padding: 35px 40px;
            text-align: center;
        }
        .logo-text {
            color: #C9A962;
            font-size: 26px;
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
        .alert-banner {
            padding: 16px 30px;
            text-align: center;
        }
        .alert-banner.new {
            background: linear-gradient(90deg, #EF4444 0%, #DC2626 100%);
        }
        .alert-banner.status-update {
            background: linear-gradient(90deg, #C9A962 0%, #D4B978 100%);
        }
        .alert-banner p {
            margin: 0;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .alert-banner.status-update p {
            color: #1A1A1A;
        }
        .email-content {
            padding: 40px;
        }
        .greeting {
            font-size: 16px;
            color: #555555;
            margin: 0 0 25px 0;
        }
        .details-grid {
            background: #FAF8F5;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .details-title {
            font-size: 13px;
            font-weight: 600;
            color: #1A1A1A;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 18px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #C9A962;
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #EDE8E1;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            width: 140px;
            font-size: 13px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            flex: 1;
            font-size: 14px;
            color: #2D2D2D;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-new {
            background: #FEE2E2;
            color: #991B1B;
        }
        .status-in_progress {
            background: #FEF3C7;
            color: #92400E;
        }
        .status-completed {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-cancelled {
            background: #E5E7EB;
            color: #374151;
        }
        .status-change-box {
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F2ED 100%);
            border-left: 4px solid #C9A962;
            border-radius: 0 12px 12px 0;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .status-change-label {
            font-size: 12px;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .status-change-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 14px;
        }
        .status-change-arrow .arrow {
            color: #C9A962;
            font-size: 20px;
        }
        .complaint-box {
            background: #F8F6F3;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .complaint-label {
            font-size: 12px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .complaint-text {
            color: #4A4A4A;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }
        .cta-section {
            text-align: center;
            margin-top: 30px;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #C9A962 0%, #B8942E 100%);
            color: #FFFFFF !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.35);
        }
        .email-footer {
            background: linear-gradient(180deg, #1A1A1A 0%, #0D0D0D 100%);
            padding: 30px 40px;
            text-align: center;
        }
        .footer-logo {
            color: #C9A962;
            font-size: 16px;
            font-weight: 300;
            letter-spacing: 3px;
            margin: 0 0 5px 0;
        }
        .footer-tagline {
            color: #666666;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 15px 0;
        }
        .footer-text {
            color: #888888;
            font-size: 11px;
            margin: 0;
        }
        .copyright {
            color: #555555;
            font-size: 10px;
            margin: 15px 0 0 0;
            padding-top: 15px;
            border-top: 1px solid #333333;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-content { padding: 25px; }
            .details-grid { padding: 20px; }
            .detail-row { flex-direction: column; }
            .detail-label { width: 100%; margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="logo-text">Brickspoint</h1>
                <p class="tagline">Maintenance Department</p>
            </div>
            
            <div class="alert-banner {{ $notificationType === 'new' ? 'new' : 'status-update' }}">
                <p>
                    @if($notificationType === 'new')
                        🔧 New Maintenance Request Submitted
                    @else
                        📋 Maintenance Status Updated
                    @endif
                </p>
            </div>
            
            <div class="email-content">
                <p class="greeting">
                    @if($notificationType === 'new')
                        A new maintenance request has been logged and requires attention.
                    @else
                        The status of a maintenance request has been updated.
                    @endif
                </p>

                @if($notificationType === 'status_update' && $previousStatus)
                <div class="status-change-box">
                    <p class="status-change-label">Status Change</p>
                    <div class="status-change-arrow">
                        <span class="status-badge status-{{ $previousStatus }}">
                            {{ str_replace('_', ' ', ucfirst($previousStatus)) }}
                        </span>
                        <span class="arrow">→</span>
                        <span class="status-badge status-{{ $log->status }}">
                            {{ str_replace('_', ' ', ucfirst($log->status)) }}
                        </span>
                    </div>
                </div>
                @endif

                <div class="details-grid">
                    <h3 class="details-title">Request Details</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Location</span>
                        <span class="detail-value">{{ $log->location }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Reported On</span>
                        <span class="detail-value">{{ $log->complaint_datetime?->format('l, F d, Y \\a\\t h:i A') ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Lodged By</span>
                        <span class="detail-value">{{ $log->lodged_by }}</span>
                    </div>
                    
                    @if($log->received_by)
                    <div class="detail-row">
                        <span class="detail-label">Received By</span>
                        <span class="detail-value">{{ $log->received_by }}</span>
                    </div>
                    @endif
                    
                    <div class="detail-row">
                        <span class="detail-label">Current Status</span>
                        <span class="detail-value">
                            <span class="status-badge status-{{ $log->status }}">
                                {{ str_replace('_', ' ', ucfirst($log->status)) }}
                            </span>
                        </span>
                    </div>
                    
                    @if($log->cost_of_fixing)
                    <div class="detail-row">
                        <span class="detail-label">Cost of Fixing</span>
                        <span class="detail-value" style="color: #C9A962; font-weight: 600;">₦{{ number_format($log->cost_of_fixing, 2) }}</span>
                    </div>
                    @endif
                    
                    @if($log->completion_date)
                    <div class="detail-row">
                        <span class="detail-label">Completion Date</span>
                        <span class="detail-value">{{ $log->completion_date?->format('F d, Y') }}</span>
                    </div>
                    @endif
                </div>

                <div class="complaint-box">
                    <p class="complaint-label">Nature of Complaint</p>
                    <p class="complaint-text">{!! nl2br(e($log->nature_of_complaint)) !!}</p>
                </div>

                <div class="cta-section">
                    <a href="{{ route('maintenance.show', $log->id) }}" class="cta-button">
                        View Full Details
                    </a>
                </div>
            </div>
            
            <div class="email-footer">
                <p class="footer-logo">Brickspoint</p>
                <p class="footer-tagline">Boutique Aparthotel</p>
                <p class="footer-text">
                    This is an automated notification from the Maintenance Log System.
                </p>
                <p class="copyright">
                    © {{ date('Y') }} Brickspoint Boutique Aparthotel. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
