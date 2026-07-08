<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $registration->reservation_code }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #C8A165; padding-bottom: 10px; }
        .header h1 { color: #C8A165; margin: 0; font-size: 18px; }
        .header p { margin: 3px 0 0; color: #666; font-size: 10px; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 6px; vertical-align: top; font-size: 9px; }
        .info-table td:first-child { font-weight: bold; width: 100px; color: #555; }
        table.items { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table.items th { background: #C8A165; color: #fff; padding: 5px 8px; text-align: left; font-size: 9px; }
        table.items td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .total-row td { font-weight: bold; font-size: 11px; padding-top: 8px; border-top: 2px solid #C8A165; }
        .footer { text-align: center; margin-top: 25px; padding-top: 10px; border-top: 1px solid #ddd; color: #888; font-size: 8px; }
        .badge-success { color: #155724; }
        .badge-danger { color: #721c24; }
        .grand-total { font-size: 13px; color: #C8A165; }
    </style>
</head>
<body>

    <div class="header">
        <h1>BRICKSPOINT BOUTIQUE APARTHOTEL</h1>
        <p>Tax Invoice / Receipt</p>
        <p style="font-size:8px;color:#999;">{{ $registration->reservation_code }} | {{ now()->format('M d, Y') }}</p>
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
            <td>Reference</td>
            <td>#{{ $registration->id }}</td>
            <td>Nights</td>
            <td>{{ $registration->no_of_nights }}</td>
        </tr>
        @if($registration->guest?->address)
        <tr>
            <td>Address</td>
            <td colspan="3">{{ $registration->guest->address }}</td>
        </tr>
        @endif
    </table>

    @php
        $balance = $grandTotal - $registration->total_paid;
        $netCharges = $totalCharges - ($discountAmount ?? 0);
    @endphp

    <table class="items">
        <thead>
            <tr>
                <th style="width:45%;">Description</th>
                <th class="text-center" style="width:10%;">Qty</th>
                <th class="text-end" style="width:20%;">Unit Price</th>
                <th class="text-end" style="width:25%;">Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Room Charge ({{ $registration->no_of_nights }} {{ Str::plural('night', $registration->no_of_nights) }})</td>
                <td class="text-center">{{ $registration->no_of_nights }}</td>
                <td class="text-end">{{ number_format($registration->room_rate ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($roomCharge, 2) }}</td>
            </tr>
            @forelse($registration->folioCharges as $charge)
            <tr>
                <td><small>{{ $charge->chargeType->name ?? '' }}</small> {{ $charge->description }}</td>
                <td class="text-center">{{ $charge->quantity }}</td>
                <td class="text-end">{{ number_format($charge->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($charge->amount, 2) }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
        <tfoot>
            @if(!empty($discountAmount) && $discountAmount > 0)
            <tr style="color: #155724;">
                <td colspan="3" class="text-end">Discount{{ $registration->discount_reason ? ' ('.$registration->discount_reason.')' : '' }}</td>
                <td class="text-end">- {{ number_format($discountAmount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="3" class="text-end">Subtotal</td>
                <td class="text-end">{{ number_format($netCharges, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-end">VAT ({{ $taxRate }}%)</td>
                <td class="text-end">{{ number_format($taxAmount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-end">Total (incl. tax)</td>
                <td class="text-end grand-total">₦{{ number_format($grandTotal, 2) }}</td>
            </tr>
            @foreach($registration->payments as $payment)
            <tr style="color: #155724;">
                <td colspan="3" class="text-end">
                    Payment: {{ $payment->payment_method }} {{ $payment->payment_date?->format('M d') }}
                </td>
                <td class="text-end">- {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-end">Outstanding Balance</td>
                <td class="text-end {{ $balance > 0 ? 'badge-danger' : 'badge-success' }}">
                    ₦{{ number_format($balance, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Brickspoint Boutique Aparthotel | Abuja, Nigeria | RC: BN 1234567</p>
        <p>VAT Registration: 12345678-0001 | Thank you for your patronage!</p>
        <p style="font-size:7px;color:#bbb;">Invoice generated {{ now()->format('F d, Y \a\t H:i') }}</p>
    </div>

</body>
</html>
