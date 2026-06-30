<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            width: 80mm;
            margin: 0 auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            padding: 2mm 3mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .header { margin-bottom: 4mm; }
        .header h2 { font-size: 12pt; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 8pt; margin-top: 1mm; }
        .divider { border-top: 1px dashed #000; margin: 2mm 0; }
        .divider-solid { border-top: 1px solid #000; margin: 2mm 0; }
        .order-info { font-size: 8pt; margin-bottom: 2mm; }
        .order-info table { width: 100%; font-size: 8pt; }
        .order-info td { padding: 0.5mm 0; }
        .items { width: 100%; font-size: 9pt; border-collapse: collapse; }
        .items th { border-bottom: 1px solid #000; padding: 1mm 0; font-size: 8pt; text-align: left; }
        .items td { padding: 0.8mm 0; vertical-align: top; }
        .items .qty { text-align: center; width: 10mm; }
        .items .price { text-align: right; width: 20mm; }
        .totals { width: 100%; font-size: 9pt; margin-top: 1mm; }
        .totals td { padding: 0.5mm 0; }
        .totals .label { text-align: left; }
        .totals .value { text-align: right; }
        .grand-total { font-size: 12pt; font-weight: bold; }
        .grand-total td { padding-top: 1mm; }
        .footer { margin-top: 4mm; font-size: 7pt; }
        .thank-you { font-size: 10pt; margin: 3mm 0; }
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { width: 80mm; }
        }
    </style>
</head>
<body>
    <div class="header center">
        <h2>{{ config('app.name') }}</h2>
        <p>Restaurant Receipt</p>
    </div>

    <div class="divider"></div>

    <div class="order-info">
        <table>
            <tr><td>Receipt #</td><td class="right">{{ $order->id }}</td></tr>
            <tr><td>Date</td><td class="right">{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Source</td><td class="right">{{ $order->type === 'walk_in' ? ($order->customer_name ?: 'Walk-in') : 'Table ' . $order->source_id }}</td></tr>
            @if($order->customer_name && $order->type === 'walk_in')
            <tr><td>Customer</td><td class="right">{{ $order->customer_name }}</td></tr>
            @endif
            @if($order->shift && $order->shift->user)
            <tr><td>Cashier</td><td class="right">{{ $order->shift->user->name }}</td></tr>
            @endif
        </table>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Qty</th>
                <th class="price">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->menuItem?->name ?? 'Item' }}</td>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="price">₦{{ number_format(($item->menuItem?->price ?? 0) * $item->quantity) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">₦{{ number_format($order->subtotal) }}</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td class="label">Discount{{ $order->discount_type === 'percentage' ? ' (' . $order->discount_type . ')' : '' }}</td>
            <td class="value">-₦{{ number_format($order->discount) }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">VAT ({{ $order->vat_rate }}%)</td>
            <td class="value">₦{{ number_format($order->vat) }}</td>
        </tr>
        <tr class="grand-total">
            <td class="label">TOTAL</td>
            <td class="value">₦{{ number_format($order->grand_total) }}</td>
        </tr>
    </table>

    <div class="divider-solid"></div>

    <div class="thank-you center">Thank you!</div>

    <div class="footer center">
        <p>{{ config('app.name') }} | Restaurant POS</p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
