<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Guest Registration Folio - {{ $registration->full_name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .hotel-info {
            text-align: left;
            margin-left: 20px;
        }

        .hotel-info h1 {
            margin: 0;
            font-size: 20px;
            color: #000;
        }

        .folio-info {
            text-align: right;
        }

        .folio-info h2 {
            margin: 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-grid td {
            border: none;
            padding: 4px 0;
            vertical-align: top;
        }

        table.detail-table th,
        table.detail-table td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }

        table.detail-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            background-color: #f0f0f0;
            padding: 5px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
        }

        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .footer-box {
            width: 48%;
            border: 1px solid #999;
            padding: 10px;
            min-height: 120px;
        }

        .signature-img {
            max-width: 250px;
            max-height: 80px;
            display: block;
            margin-top: 5px;
        }

        /* Updated policy styles for density */
        .policy-box {
            border: 1px solid #999; 
            padding: 10px;
        }
        .policy-list {
            font-size: 8px;
            color: #555;
            line-height: 1.2;
            margin: 0;
            padding-left: 15px;
            list-style-type: disc;
        }
        .policy-list li {
            margin-bottom: 2px;
        }
        .signature-line {
            border-top: 1px solid #333; 
            margin-top: 5px; 
            padding-top: 5px; 
            margin-bottom: 0;
            font-weight: bold;
        }


        /* Helper for layout */
        .w-50 { width: 50%; }
        .w-33 { width: 33.33%; }
        .text-right { text-align: right; }
        strong { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">

        <table class="header-table" style="width: 100%; border: 0;">
            <tr>
                <td style="width: 120px; border: 0;">
                    @if ($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td style="border: 0; text-align: center; padding-left: 15px;">
                    <h1 style="margin: 0; font-size: 20px;">Guest Check-in Registration Form</h1>
                    {{-- <p style="margin: 0;">123 Hotel Address, City, Country</p>
                    <p style="margin: 0;">Phone: (123) 456-7890 | Email: info@brickspoint.com</p> --}}
                </td>
                <td style="border: 0; text-align: right; vertical-align: top;">
                    <h2 style="margin: 0; font-size: 16px;"></h2>
                    <p style="margin: 2px 0;"><strong>Reg ID:</strong> {{ $registration->id }}</p>
                    <p style="margin: 2px 0;"><strong>Print Date:</strong> {{ now()->format('M d, Y H:i A') }} <br> by {{ Auth::user()->name }}</p>
                </td>
            </tr>
        </table>

        <div class="section-title">Guest & Stay Information</div>
        <table class="data-grid">
            <tr>
                <td class="w-33"><strong>Lead Guest:</strong> {{ $registration->full_name }}</td>
                <td class="w-33"><strong>Check-in:</strong> {{ $registration->check_in->format('M d, Y') }}</td>
                <td class="w-33"><strong>Booking Source:</strong> {{ $registration->bookingSource?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Contact:</strong> {{ $registration->contact_number }}</td>
                <td><strong>Check-out:</strong> {{ $registration->check_out->format('M d, Y') }}</td>
                <td><strong>Guest Type:</strong> {{ $registration->guestType?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $registration->email ?? 'N/A' }}</td>
                <td><strong>Total Nights:</strong> {{ $registration->no_of_nights }}</td>
                <td><strong>Finalized By:</strong>  @if($registration->stay_status !== 'draft_by_guest' && $registration->finalized_by_agent_id)
                    {{ App\Models\User::find($registration->finalized_by_agent_id)->name ?? 'N/A' }}
                @endif</td>
            </tr>
            <tr>
                <td><strong>Nationality:</strong> {{ $registration->nationality ?? 'N/A' }}</td>
                <td><strong>Company:</strong> {{ $registration->guest->company_name ?? 'N/A' }}</td>
                <td><strong>Gender:</strong> {{ $registration->gender ? ucfirst($registration->gender) : 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">Occupant & Room Details</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Occupant Name</th>
                    <th>Room Allocation</th>
                    <th>Rate (per night)</th>
                    <th>B&B</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color: #fdfde9;">
                    <td><strong>{{ $registration->full_name }} (Group Lead)</strong></td>
                    <td><strong>{{ $registration->room_allocation }}</strong></td>
                    <td><strong>{{ number_format($registration->room_rate, 2) }}</strong></td>
                    <td>{{ $registration->bed_breakfast ? 'Yes' : 'No' }}</td>
                    <td>{{ $registration->stay_status }}</td>
                </tr>
                @foreach ($groupMembers as $member)
                    <tr>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->room_allocation }}</td>
                        <td>{{ $member->room_rate ? number_format($member->room_rate, 2) : 'N/A' }}</td>
                        <td>{{ $member->bed_breakfast ? 'Yes' : 'No' }}</td>
                        <td>{{ $member->stay_status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 100%; border: 0;">
            <tr>
                {{-- Financial Summary --}}
                <td style="width: 50%; padding-right: 10px; vertical-align: top; border: 0;">
                    <div class="section-title">Financial Summary</div>
                    <table class="detail-table">
                        <tr>
                            <td class="w-50"><strong>Billing Type:</strong></td>
                            <td class="w-50">{{ $registration->billing_type ? ucfirst($registration->billing_type) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>{{ $registration->payment_method ? ucfirst($registration->payment_method) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Bill (Est.):</strong></td>
                            <td>
                                <strong>{{ number_format($registration->total_amount, 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Guest Agreement (with Full Policy) --}}
                <td style="width: 50%; padding-left: 10px; vertical-align: top; border: 0;">
                    <div class="section-title">Guest Agreement</div>
                    <div class="policy-box">
                        
                        {{-- === NEW: FULL POLICY LIST ADDED === --}}
                        <ul class="policy-list">
                            <li>The agreed rate is valid for this stay only. For long stays, Bricks Point reserves the right to revert to the RACK RATE if checkout occurs before the agreed duration.</li>
                            <li>Check-in is at <strong>3:00 PM</strong> and check-out is at <strong>12:00 noon</strong>. Early check-in and late check-out are subject to availability and may incur additional fees. After 5:00 PM, a full rate applies. No-shows will be charged for a full day.</li>
                            <li>Lost room keys will incur a fine.</li>
                            <li>Personal safes are available in each apartment. Please use them to secure your valuables. Bricks Point is not liable for any loss.</li>
                            <li>If you sustain an injury or experience loss/damage to property during your stay, please notify hotel management before departure. Any related claims will be governed by the laws of the country where the hotel is located, and its courts will have exclusive jurisdiction.</li>
                            <li>By signing this form, you agree to abide by our policies.</li>
                        </ul>
                        {{-- === END OF NEW POLICY LIST === --}}

                        @if ($guestSignatureBase64)
                            <img src="data:image/png;base64,{{ $guestSignatureBase64 }}" alt="Guest Signature"
                                class="signature-img">
                        @else
                            <p style="margin-top: 5px;"><strong>(No signature provided)</strong></p>
                        @endif
                        
                        <p class="signature-line">
                            Guest Signature
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="policy" style="font-size: 8px; text-align: center; margin-top: 20px;">
            Thank you for choosing Brickspoint Boutique Aparthotel.
        </div>

    </div>
</body>
</html>