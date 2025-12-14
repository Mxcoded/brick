<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 14px; }
        .header { width: 100%; border-bottom: 2px solid #C8A165; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #C8A165; text-transform: uppercase; }
        .invoice-title { float: right; font-size: 20px; font-weight: bold; color: #333; }
        
        .details-box { width: 100%; margin-bottom: 30px; }
        .client-info { float: left; width: 50%; }
        .meta-info { float: right; width: 40%; text-align: right; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8f9fa; color: #333; font-weight: bold; padding: 10px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-end { text-align: right; }
        
        .totals { width: 40%; float: right; margin-top: 20px; }
        .totals-row { padding: 5px 0; border-bottom: 1px solid #eee; }
        .totals-row.grand { border-bottom: 2px solid #C8A165; font-weight: bold; font-size: 16px; margin-top: 10px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .status-stamp { 
            position: absolute; top: 150px; right: 50px; font-size: 40px; font-weight: bold; 
            color: rgba(200, 161, 101, 0.3); border: 4px solid rgba(200, 161, 101, 0.3); 
            padding: 10px 20px; transform: rotate(-15deg); text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="header">
        <span class="logo">The Brick Hall</span>
        <span class="invoice-title">INVOICE</span>
    </div>

    <div class="status-stamp">{{ $order->payment_status }}</div>

    <div class="details-box">
        <div class="client-info">
            <strong>Bill To:</strong><br>
            {{ $order->contact_person_name }}<br>
            {{ $order->customer->organization ?? '' }}<br>
            {{ $order->contact_person_email }}<br>
            {{ $order->contact_person_phone }}
        </div>
        <div class="meta-info">
            <strong>Invoice #:</strong> {{ $order->order_id }}<br>
            <strong>Date:</strong> {{ now()->format('M d, Y') }}<br>
            <strong>Event Date:</strong> {{ $order->preparation_date->format('M d, Y') }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Hall Rental Fees</strong><br><small class="text-muted">Venue Booking Charge</small></td>
                <td class="text-end">₦{{ number_format($order->hall_rental_fees, 2) }}</td>
            </tr>
            @foreach($order->eventDays as $day)
                @foreach($day->menuItems as $item)
                <tr>
                    <td>
                        <strong>{{ ucfirst($item->meal_type) }}</strong> ({{ $day->event_date->format('M d') }})<br>
                        <small>
                            {{ implode(', ', json_decode($item->menu_items, true)) }} 
                            x {{ $item->quantity }} guests @ ₦{{ number_format($item->unit_price) }}
                        </small>
                    </td>
                    <td class="text-end">₦{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span style="float:left;">Subtotal:</span>
            <span style="float:right;">₦{{ number_format($order->total_revenue, 2) }}</span>
            <div style="clear:both;"></div>
        </div>
        <div class="totals-row">
            <span style="float:left;">Total Paid:</span>
            <span style="float:right;">(₦{{ number_format($order->paid_amount, 2) }})</span>
            <div style="clear:both;"></div>
        </div>
        <div class="totals-row grand">
            <span style="float:left;">Balance Due:</span>
            <span style="float:right;">₦{{ number_format($order->balance_due, 2) }}</span>
            <div style="clear:both;"></div>
        </div>
    </div>

    <div class="footer">
        Thank you for choosing The Brick Hall. Please make all cheques payable to The Brick Hall Ltd.
    </div>

</body>
</html>