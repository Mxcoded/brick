<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Readings Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; color: #C8A165; font-size: 18px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #C8A165; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { text-align: center; color: #999; font-size: 8px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        .summary { margin-bottom: 15px; }
        .summary td { border: none; padding: 3px 15px 3px 0; }
        .badge-gen { color: #856404; }
        .badge-diesel { color: #6c757d; }
        .badge-water { color: #0c5460; }
        .badge-cold { color: #004085; }
    </style>
</head>
<body>
    <h1>BRICKSPOINT</h1>
    <div class="subtitle">Daily Readings Report &mdash; Generated {{ now()->format('M d, Y') }}</div>

    <table class="summary">
        <tr><td><strong>Total Entries:</strong></td><td>{{ $readings->count() }}</td></tr>
        <tr><td><strong>Date Range:</strong></td><td>{{ $readings->count() ? $readings->min('reading_date')->format('M d, Y').' — '.$readings->max('reading_date')->format('M d, Y') : 'N/A' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th class="text-right">Reading</th>
                <th class="text-right">Capacity</th>
                <th class="text-right">Calculated</th>
                <th>Notes</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($readings as $r)
            <tr>
                <td>{{ $r->reading_date->format('M d, Y') }}</td>
                <td>
                    @if($r->reading_type === 'generator') Generator
                    @elseif($r->reading_type === 'diesel_reservoir') Diesel Reservoir
                    @elseif($r->reading_type === 'water_tank') Water Tank
                    @elseif($r->reading_type === 'cold_room') Cold Room
                    @else {{ $r->reading_type }}
                    @endif
                </td>
                <td>{{ $r->category ? ucfirst(str_replace('_', ' ', $r->category)) : '—' }}</td>
                <td class="text-right">
                    @if($r->reading_type === 'cold_room') {{ number_format($r->reading_value, 1) }}&deg;C
                    @elseif($r->reading_type === 'diesel_reservoir') {{ number_format($r->reading_value, 0) }}L
                    @else {{ number_format($r->reading_value, 1) }}%
                    @endif
                </td>
                <td class="text-right">{{ $r->capacity ? number_format($r->capacity) : '—' }}</td>
                <td class="text-right">{{ $r->reading_type === 'diesel_reservoir' ? '—' : ($r->calculated_value ? number_format($r->calculated_value, $r->reading_type === 'generator' ? 2 : 0) : '—') }}</td>
                <td>{{ $r->notes ?: '—' }}</td>
                <td>{{ $r->recorder?->name ?: '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align: center; color: #999;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} BRICKSPOINT. All rights reserved. &bull; Developed with love by IT Team
    </div>
</body>
</html>