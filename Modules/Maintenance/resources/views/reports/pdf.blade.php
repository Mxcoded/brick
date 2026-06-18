<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maintenance Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; color: #C8A165; font-size: 18px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #C8A165; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .badge-new { color: #856404; }
        .badge-in_progress { color: #004085; }
        .badge-completed { color: #155724; }
        .badge-cancelled { color: #dc3545; }
        .footer { text-align: center; color: #999; font-size: 8px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        .summary { margin-bottom: 15px; }
        .summary table { width: auto; }
        .summary td { border: none; padding: 3px 15px 3px 0; }
    </style>
</head>
<body>
    <h1>BRICKSPOINT</h1>
    <div class="subtitle">Maintenance Report — Generated {{ now()->format('M d, Y') }}</div>

    <table class="summary">
        <tr><td><strong>Total Entries:</strong></td><td>{{ $logs->count() }}</td></tr>
        <tr><td><strong>Open:</strong></td><td>{{ $logs->whereIn('status', ['new', 'in_progress'])->count() }}</td></tr>
        <tr><td><strong>Completed:</strong></td><td>{{ $logs->where('status', 'completed')->count() }}</td></tr>
        <tr><td><strong>Total Cost:</strong></td><td>NGN {{ number_format($logs->sum('cost_of_fixing'), 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Location</th>
                <th>Department</th>
                <th>Complaint</th>
                <th>Lodged By</th>
                <th>Status</th>
                <th>Date</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->location }}</td>
                    <td>{{ $log->department }}</td>
                    <td>{{ Str::limit($log->nature_of_complaint, 50) }}</td>
                    <td>{{ $log->lodged_by }}</td>
                    <td class="badge-{{ $log->status }}">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</td>
                    <td>{{ $log->complaint_datetime->format('M d, Y') }}</td>
                    <td>{{ $log->cost_of_fixing ? number_format($log->cost_of_fixing, 2) : '--' }}</td>
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
