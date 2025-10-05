<!DOCTYPE html>
<html>
<head>
    <title>Guest Registration - {{ $registration->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
        .field { display: grid; grid-template-columns: 1fr 2fr; margin-bottom: 5px; }
        .label { font-weight: bold; }
        .signature { width: 200px; height: 100px; border: 1px solid #000; margin-top: 10px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h2>Brickspoint Aparthotel - Guest Registration Form</h2>
<p><strong>ID:</strong> {{ $registration->id }} | <strong>Date:</strong> {{ $registration->registration_date->format('M d, Y') }} | <strong>Agent:</strong> {{ $registration->front_desk_agent ?? 'N/A' }}</p>

    <div class="section">
        <h3>Guest Details</h3>
        <div class="field"><span class="label">Title:</span> {{ $registration->title ?? 'N/A' }}</div>
        <div class="field"><span class="label">Full Name:</span> {{ $registration->full_name }}</div>
        {{-- Add all fields: nationality, contact, etc. --}}
        <div class="field"><span class="label">Email:</span> {{ $registration->email ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <h3>Booking Information</h3>
        <div class="field"><span class="label">Room Type:</span> {{ $registration->room_type }}</div>
        <div class="field"><span class="label">Room Rate:</span> ${{ number_format($registration->room_rate, 2) }}</div>
        <div class="field"><span class="label">Check-in:</span> {{ $registration->check_in->format('M d, Y H:i') }} (Assigned by {{ $registration->front_desk_agent }})</div>
        <div class="field"><span class="label">Check-out:</span> {{ $registration->check_out->format('M d, Y') }}</div>
        <div class="field"><span class="label">No. of Nights:</span> {{ $registration->no_of_nights }}</div>
        <div class="field"><span class="label">Bed & Breakfast:</span> {{ $registration->bed_breakfast ? 'Yes' : 'No' }}</div>
        <div class="field"><span class="label">Total Amount:</span> ${{ number_format($registration->total_amount, 2) }}</div>
    </div>

    <div class="section">
        <h3>Emergency Contact</h3>
        <div class="field"><span class="label">Name:</span> {{ $registration->emergency_name ?? 'N/A' }}</div>
        <div class="field"><span class="label">Relationship:</span> {{ $registration->emergency_relationship ?? 'N/A' }}</div>
        <div class="field"><span class="label">Contact:</span> {{ $registration->emergency_contact ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <h3>Policy Agreement</h3>
        <p>I agree to the hotel policies as outlined. <strong>Signed:</strong> [Embedded Signature Image Below]</p>
       @if($registration->guest_signature) and replace it
<div class="section">
    <h3>Policy Agreement</h3>
    <p>I agree to the hotel policies as outlined. <strong>Signed:</strong> [Embedded Signature Image Below]</p>
    @if($registration->guest_signature)
        {{-- FIX: Use asset() or Storage::url() to correctly display the image from the storage link --}}
        <img src="{{ asset('storage/' . $registration->guest_signature) }}" alt="Guest Signature" class="signature">
    @else
        <p>No Guest Signature Recorded.</p>
    @endif
</div>

    @if($registration->is_group_lead)
        <div class="section">
            <h3>Group Members</h3>
            <ul>
                @foreach($registration->groupMembers as $member)
                    <li>{{ $member->full_name }} ({{ $member->contact_number }}) - Room: {{ $member->room_assignment ?? 'TBD' }}</li>
                @endforeach
            </ul>
</div>

    @if($registration->is_group_lead)
        <div class="section">
            <h3>Group Members</h3>
            <ul>
                @foreach($registration->groupMembers as $member)
                    <li>{{ $member->full_name }} ({{ $member->contact_number }}) - Room: {{ $member->room_assignment ?? 'TBD' }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <footer style="text-align: center; margin-top: 40px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px;">
    @if($registration->stay_status !== 'draft_by_guest')
        <p style="font-size: 11px; margin-bottom: 5px;">**Finalized by Front Desk Agent:** {{ $registration->front_desk_agent ?? 'N/A' }}</p>
    @endif
    {{-- Use the currently logged-in user for the print log, but use $registration->front_desk_agent for the official agent --}}
    Printed on {{ now()->format('M d, Y H:i') }} by {{ Auth::user()->name }} | Brickspoint Aparthotel
</footer>
</body>
</html>