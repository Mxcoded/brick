<!DOCTYPE html>
<html>
<head>
    <title>Guest Registration - {{ $registration->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 15px; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; font-size: 11px; }
        .logo { max-height: 150px; max-width: 200px; text-align: left;margin-bottom: 10px; }
        .section { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        .section h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px; color: #000; }
        .field { display: grid; grid-template-columns: 150px 1fr; margin-bottom: 8px; }
        .label { font-weight: bold; }
        .signature { max-width: 200px; height: auto; border-bottom: 1px solid #000000; margin-top: 10px; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        footer { text-align: center; margin-top: 40px; font-size: 10px; color: #777; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
       @if($logoBase64)
       <div class="logo">
        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Brickspoint Logo" style="max-height: 150px; max-width: 200px;">
       </div>
       @endif
        <h2>Guest Registration Card</h2>
        <p><strong>ID:</strong> 000{{ $registration->id }} | <strong>Date:</strong> {{ $registration->registration_date->format('M d, Y') }}</p>
    </div>

    <div class="section">
        <h3>Guest Details</h3>
        <div class="field"><span class="label">Full Name:</span> {{ $registration->title }} {{ $registration->full_name }}</div>
        <div class="field"><span class="label">Email:</span> {{ $registration->email ?? 'N/A' }}</div>
        <div class="field"><span class="label">Contact:</span> {{ $registration->contact_number ?? 'N/A' }}</div>
        <div class="field"><span class="label">Nationality:</span> {{ $registration->guest->nationality ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <h3>Booking Information</h3>
        <div class="field"><span class="label">Room Allocation:</span> {{ $registration->room_allocation ?? 'TBD' }}</div>
        <div class="field"><span class="label">Room Rate:</span> &#8358;{{ number_format($registration->room_rate, 2) }} / night</div>
        <div class="field"><span class="label">Check-in:</span> {{ $registration->check_in ? $registration->check_in->format('M d, Y') : 'N/A' }}</div>
        <div class="field"><span class="label">Check-out:</span> {{ $registration->check_out ? $registration->check_out->format('M d, Y') : 'N/A' }}</div>
        <div class="field"><span class="label">Nights:</span> {{ $registration->no_of_nights }}</div>
        <div class="field"><span class="label">Total Amount:</span> &#8358;{{ number_format($registration->total_amount, 2) }}</div>
    </div>

    <div class="section">
        <h3>Policy Agreement & Signature</h3>
        <p>I, <u>{{ $registration->full_name }}</u>, have read and agreed to the hotel policies.</p>
        @if($guestSignatureBase64)
            <img src="data:image/png;base64,{{ $guestSignatureBase64 }}" alt="Guest Signature" class="signature">
        @else
            <p><strong>No Guest Signature was recorded.</strong></p>
        @endif
    </div>

    @if($registration->is_group_lead && $groupMembers->count() > 0)
        <div class="section">
            <h3>Group Members</h3>
            <table>
                <thead><tr><th>Name</th><th>Contact</th><th>Room</th></tr></thead>
                <tbody>
                    @foreach($groupMembers as $member)
                        <tr>
                            <td>{{ $member->full_name }}</td>
                            <td>{{ $member->contact_number }}</td>
                            <td>{{ $member->room_allocation ?? 'TBD' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    <footer>
        @if($registration->stay_status !== 'draft_by_guest' && $registration->finalized_by_agent_id)
            <p><strong>Finalized by Agent:</strong> {{ App\Models\User::find($registration->finalized_by_agent_id)->name ?? 'N/A' }}</p>
        @endif
        <p>Printed on {{ now()->format('M d, Y H:i A') }} by {{ Auth::user()->name }} | Brickspoint Aparthotel</p>
    </footer>

</body>
</html>

