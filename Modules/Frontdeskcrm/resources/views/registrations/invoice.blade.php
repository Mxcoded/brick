<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $registration->reservation_code }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #C8A165; padding-bottom: 15px; }
        .header h1 { color: #C8A165; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-table td:first-child { font-weight: bold; width: 140px; color: #555; }
        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th { background: #C8A165; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items tr:last-child td { border-bottom: 2px solid #C8A165; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .total-row td { font-weight: bold; font-size: 14px; padding-top: 10px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; color: #888; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="header">
        <h1>BRICKSPOINT BOUTIQUE APARTHOTEL</h1>
        <p>Invoice / Receipt</p>
        <p style="font-size:10px;color:#999;">{{ $registration->reservation_code }} | {{ now()->format('M d, Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td>Guest</td>
            <td>{{ $registration->guest->full_name ?? $registration->full_name }}</td>
            <td>Check-In</td>
            <td>{{ $registration->check_in?->format('M d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Room</td>
            <td>{{ $registration->roomUnit->room_number ?? $registration->roomType->name ?? '—' }}</td>
            <td>Check-Out</td>
            <td>{{ $registration->check_out?->format('M d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{!! $registration->stay_status === 'checked_out' ? '<span class="badge badge-success">Checked Out</span>' : ($registration->stay_status === 'checked_in' ? '<span class="badge badge-warning">In House</span>' : '<span class="badge badge-danger">' . ucfirst($registration->stay_status) . '</span>') !!}</td>
            <td>Nights</td>
            <td>{{ $registration->no_of_nights }}</td>
        </tr>
    </table>

    @php
        $roomCharge = ($registration->room_rate ?? 0) * ($registration->no_of_nights ?? 1);
        $folioCharges = $registration->folioCharges->sum('amount');
        $discountAmount = $registration->total_discount * ($registration->no_of_nights ?? 1);
        $totalCharges = $roomCharge + $folioCharges - $discountAmount;
        $taxRate = app(\App\Services\PropertyService::class)->taxRate();
        $taxAmount = round($totalCharges * $taxRate / 100, 2);
        $grandTotal = $totalCharges + $taxAmount;
        $balance = $grandTotal - $registration->total_paid;
    @endphp

    <table class="items">
        <thead>
            <tr>
                <th style="width:40%;">Description</th>
                <th class="text-center" style="width:10%;">Qty</th>
                <th class="text-end" style="width:20%;">Unit Price</th>
                <th class="text-end" style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Room Charge ({{ $registration->no_of_nights }} {{ Str::plural('night', $registration->no_of_nights) }} @ ₦{{ number_format($registration->room_rate ?? 0) }})</td>
                <td class="text-center">{{ $registration->no_of_nights }}</td>
                <td class="text-end">₦{{ number_format($registration->room_rate ?? 0, 2) }}</td>
                <td class="text-end">₦{{ number_format($roomCharge, 2) }}</td>
            </tr>
            @foreach($registration->folioCharges as $charge)
            <tr>
                <td><small>{{ $charge->chargeType->name ?? '—' }}</small> {{ $charge->description }}</td>
                <td class="text-center">{{ $charge->quantity }}</td>
                <td class="text-end">₦{{ number_format($charge->unit_price, 2) }}</td>
                <td class="text-end">₦{{ number_format($charge->amount, 2) }}</td>
            </tr>
            @endforeach

            @if($folioCharges == 0 && $registration->folioCharges->isEmpty())
            <tr>
                <td colspan="4" class="text-center text-muted">No additional charges</td>
            </tr>
            @endif

            @if($discountAmount > 0)
            <tr class="text-success">
                <td colspan="3" class="text-end"><i class="fas fa-tag me-1"></i>Discount{{ $registration->discount_reason ? ' ('.$registration->discount_reason.')' : '' }}</td>
                <td class="text-end">- ₦{{ number_format($discountAmount, 2) }}</td>
            </tr>
            @endif

            <tr>
                <td colspan="3" class="text-end">Subtotal</td>
                <td class="text-end">₦{{ number_format($totalCharges, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-end">VAT ({{ $taxRate }}%)</td>
                <td class="text-end">₦{{ number_format($taxAmount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-end">Total (incl. tax)</td>
                <td class="text-end">₦{{ number_format($grandTotal, 2) }}</td>
            </tr>

            @foreach($registration->payments as $payment)
            <tr style="color: #155724;">
                <td colspan="3" class="text-end">
                    <i class="fas fa-money-bill-wave me-1"></i>Payment: {{ $payment->payment_method }}
                    <small>({{ $payment->payment_date?->format('M d') }})</small>
                </td>
                <td class="text-end">- ₦{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="3" class="text-end">Outstanding Balance</td>
                <td class="text-end {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                    ₦{{ number_format($balance, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Brickspoint Boutique Aparthotel | Abuja, Nigeria</p>
        <p>Thank you for your patronage!</p>
        <p style="font-size:8px;color:#bbb;">Invoice generated {{ now()->format('F d, Y \a\t H:i') }}</p>
    </div>

    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button onclick="window.print()" style="padding:10px 30px;background:#C8A165;color:#fff;border:none;border-radius:5px;cursor:pointer;">
            <i class="fas fa-print"></i> Print / Save PDF
        </button>
    </div>

</body>
</html>
