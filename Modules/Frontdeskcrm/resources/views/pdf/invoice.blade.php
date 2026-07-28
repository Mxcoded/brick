<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 15px; }
        table { width: 100%; border-collapse: collapse; }
        .items-table th { background-color: #1a1a2e; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        .items-table td { padding: 6px 8px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        .items-table tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-box { width: 300px; margin-left: auto; }
        .totals-box td { padding: 4px 8px; font-size: 10px; }
        .totals-box .grand-total td { border-top: 2px solid #1a1a2e; font-weight: bold; font-size: 12px; padding: 6px 8px; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #ffc107; color: #333; }
        .status-issued { background: #17a2b8; color: #fff; }
        .status-paid { background: #28a745; color: #fff; }
        .status-void { background: #dc3545; color: #fff; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
        .guest-info td { padding: 3px 0; font-size: 9px; }
    </style>
</head>
<body>
    @include('frontdeskcrm::pdf._letterhead', [
        'docTitle' => 'INVOICE',
        'docNumber' => $invoice->invoice_number,
        'docDate' => $invoice->issue_date?->format('M d, Y') ?? now()->format('M d, Y'),
    ])

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top; border: 0;">
                <h3 style="margin: 0 0 5px 0; font-size: 11px; color: #1a1a2e;">Bill To</h3>
                <table class="guest-info">
                    <tr><td><strong>{{ $invoice->registration->full_name ?? 'N/A' }}</strong></td></tr>
                    @if($invoice->registration->guest?->company_name)
                    <tr><td>{{ $invoice->registration->guest->company_name }}</td></tr>
                    @endif
                    @if($invoice->registration->email)
                    <tr><td>{{ $invoice->registration->email }}</td></tr>
                    @endif
                    @if($invoice->registration->contact_number)
                    <tr><td>{{ $invoice->registration->contact_number }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; border: 0;">
                <h3 style="margin: 0 0 5px 0; font-size: 11px; color: #1a1a2e;">Stay Details</h3>
                <table class="guest-info">
                    <tr><td><strong>Registration:</strong> #{{ $invoice->registration_id }}</td></tr>
                    @if($invoice->registration->room_allocation)
                    <tr><td><strong>Room:</strong> {{ $invoice->registration->room_allocation }}</td></tr>
                    @endif
                    <tr><td><strong>Check-in:</strong> {{ $invoice->registration->check_in?->format('M d, Y') ?? 'N/A' }}</td></tr>
                    <tr><td><strong>Check-out:</strong> {{ $invoice->registration->check_out?->format('M d, Y') ?? 'N/A' }}</td></tr>
                    @if($invoice->folio)
                    <tr><td><strong>Folio:</strong> {{ $invoice->folio->folio_number }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <h3 style="margin: 10px 0 5px 0; font-size: 11px; color: #1a1a2e;">Line Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Unit Price</th>
                <th class="text-right" style="width: 15%;">Tax</th>
                <th class="text-right" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right"><strong>{{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="padding: 20px; color: #888;">No items on this invoice.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-box" style="margin-top: 10px;">
        <table>
            <tr>
                <td style="text-align: right;">Subtotal:</td>
                <td class="text-right" style="width: 120px;">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align: right;">Tax:</td>
                <td class="text-right">{{ number_format($invoice->tax_total, 2) }}</td>
            </tr>
            @if($invoice->creditNotes->sum('amount') > 0)
            <tr>
                <td style="text-align: right; color: #dc3545;">Credit Notes:</td>
                <td class="text-right" style="color: #dc3545;">({{ number_format($invoice->creditNotes->sum('amount'), 2) }})</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td style="text-align: right;">TOTAL ({{ $invoice->currency }}):</td>
                <td class="text-right">{{ number_format($invoice->total, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align: right;">Paid:</td>
                <td class="text-right">{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            @if($invoice->total - $invoice->paid_amount > 0)
            <tr style="font-weight: bold; color: #dc3545;">
                <td style="text-align: right;">Balance Due:</td>
                <td class="text-right">{{ number_format($invoice->total - $invoice->paid_amount, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 15px; padding: 8px; background: #f8f9fa; border-left: 3px solid #b8860b;">
        <strong style="font-size: 9px;">Notes:</strong>
        <p style="margin: 3px 0; font-size: 9px; color: #555;">{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p style="margin: 0;">Thank you for staying with us. For inquiries, please contact us at the above address.</p>
        <p style="margin: 2px 0 0 0;">Generated by Brickspoint HMS on {{ now()->format('M d, Y H:i A') }}</p>
    </div>
</body>
</html>
