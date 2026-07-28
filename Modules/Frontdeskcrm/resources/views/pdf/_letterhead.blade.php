@php
    $settings = \Modules\Website\Models\Settings::pluck('value', 'key')->toArray();
    $hotelName = $settings['hotel_name'] ?? 'Brickspoint Boutique Aparthotel';
    $address = $settings['address'] ?? '24 Jose Marti Crescent Asokoro, Abuja, Nigeria';
    $phone = $settings['phone'] ?? '+234 809 999 9627';
    $email = $settings['email'] ?? 'rsv@brickspoint.com';
    $logo = $settings['logo'] ?? null;
@endphp
<table style="width: 100%; border: 0; border-bottom: 2px solid #b8860b; padding-bottom: 10px; margin-bottom: 15px;">
    <tr>
        <td style="width: 80px; border: 0; vertical-align: top;">
            @if($logo)
                @if(str_starts_with($logo, 'http'))
                    <img src="{{ $logo }}" alt="Logo" style="width: 70px; height: auto;">
                @else
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/' . $logo))) }}" alt="Logo" style="width: 70px; height: auto;">
                @endif
            @endif
        </td>
        <td style="border: 0; padding-left: 15px;">
            <h1 style="margin: 0; font-size: 18px; color: #1a1a2e; font-weight: bold;">{{ $hotelName }}</h1>
            <p style="margin: 2px 0; font-size: 9px; color: #555;">{{ $address }}</p>
            <p style="margin: 2px 0; font-size: 9px; color: #555;">Tel: {{ $phone }} | {{ $email }}</p>
        </td>
        <td style="border: 0; text-align: right; vertical-align: top;">
            <h2 style="margin: 0; font-size: 14px; color: #b8860b; font-weight: bold;">{{ $docTitle ?? 'Document' }}</h2>
            <p style="margin: 2px 0; font-size: 9px; color: #555;"><strong>{{ $docNumber ?? '' }}</strong></p>
            <p style="margin: 2px 0; font-size: 9px; color: #555;">Date: {{ $docDate ?? now()->format('M d, Y') }}</p>
        </td>
    </tr>
</table>
