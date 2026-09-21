<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 100px 45px 80px 45px; }
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 10px; color: #222; line-height: 1.45; }
        h1, h2, h3, h4 { margin: 0 0 6px 0; color: #1a1a2e; }
        table { border-collapse: collapse; }
        td, th { vertical-align: top; }
        .doc-title { text-align: center; margin: 14px 0 18px 0; }
        .doc-title h1 { font-size: 19px; letter-spacing: 1px; text-transform: uppercase; color: #1a1a2e; }
        .doc-title .sub { font-size: 10px; color: #b8860b; font-weight: bold; letter-spacing: 2px; }
        .meta-table { width: 100%; margin-bottom: 16px; }
        .meta-table td { border: 1px solid #ddd; padding: 5px 8px; font-size: 9.5px; }
        .meta-table .k { background: #f7f5f0; color: #666; font-weight: bold; width: 105px; }
        .section { margin: 14px 0 8px 0; padding-bottom: 4px; border-bottom: 1.5px solid #C8A165; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .party-table { width: 100%; margin-bottom: 14px; }
        .party-table td { width: 50%; border: 1px solid #ddd; padding: 8px 10px; }
        .party-table .p-head { font-size: 10px; font-weight: bold; color: #b8860b; text-transform: uppercase; margin-bottom: 4px; }
        .party-table table { width: 100%; }
        .party-table table td { border: 0; border-bottom: 1px dotted #eee; padding: 3px 0; font-size: 9.5px; }
        .party-table table td.k { color: #777; width: 85px; }
        .clause { margin-bottom: 10px; text-align: justify; }
        .clause h4 { font-size: 10.5px; margin-bottom: 2px; }
        .clause .body { white-space: pre-wrap; }
        .obligations { width: 100%; margin-bottom: 14px; }
        .obligations th { background: #f7f5f0; text-align: left; font-size: 9px; text-transform: uppercase; color: #555; padding: 5px 7px; border: 1px solid #ddd; }
        .obligations td { border: 1px solid #ddd; padding: 5px 7px; font-size: 9px; }
        .signature-table { width: 100%; margin-top: 22px; }
        .signature-table td { width: 50%; padding: 8px 18px 8px 0; }
        .sig-block .s-head { font-size: 10px; font-weight: bold; color: #1a1a2e; text-transform: uppercase; }
        .sig-img { max-height: 48px; max-width: 240px; margin: 6px 0; }
        .sig-line { border-bottom: 1px solid #333; height: 26px; width: 100%; margin-bottom: 4px; }
        .sig-name { font-size: 9.5px; font-weight: bold; }
        .sig-meta { font-size: 8.5px; color: #666; }
        #footer { position: fixed; bottom: -62px; left: 0; right: 0; height: 40px; text-align: center; font-size: 8px; color: #777; border-top: 1px solid #ddd; padding-top: 6px; }
        #footer .page:after { content: counter(page); }
        #footer .pages:after { content: counter(pages); }
        .note { font-size: 8.5px; color: #666; }
    </style>
</head>
<body>
    @include('contracts::pdfs._letterhead', [
        'docTitle' => $agreement->typeEnum()->label().' Agreement',
        'docNumber' => $agreement->agreement_number.'  ·  v'.$agreement->current_version,
        'docDate' => $agreement->effective_date?->format('M d, Y'),
    ])

    <div class="doc-title">
        <h1>{{ $agreement->title }}</h1>
        <div class="sub">Agreement No. {{ $agreement->agreement_number }}</div>
    </div>

    @php
        $hotel = $agreement->hotelParty();
        $client = $agreement->primaryClient();
        $ct = $agreement->commercial_terms ?? [];
        $money = fn ($v) => $v === null || $v === '' ? '—' : $agreement->currency.' '.number_format((float) $v, 2);
        $signedBy = fn (string $role) => $agreement->signatures->firstWhere('party_role', $role);
    @endphp

    <table class="meta-table">
        <tr>
            <td class="k">Status</td>
            <td>{{ ucfirst(str_replace('_', ' ', $agreement->statusEnum()->label())) }}</td>
            <td class="k">Type</td>
            <td>{{ $agreement->typeEnum()->label() }}</td>
        </tr>
        <tr>
            <td class="k">Effective</td>
            <td>{{ $agreement->effective_date?->format('d M Y') ?? '—' }}</td>
            <td class="k">Expiry</td>
            <td>{{ $agreement->expiry_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="k">Auto-renew</td>
            <td>{{ $agreement->auto_renew ? 'Yes' : 'No' }}</td>
            <td class="k">Currency</td>
            <td>{{ $agreement->currency }}</td>
        </tr>
        @if ($agreement->template)
            <tr>
                <td class="k">Template</td>
                <td colspan="3">{{ $agreement->template->name }}</td>
            </tr>
        @endif
    </table>

    <h3 class="section">Parties</h3>
    <table class="party-table">
        <tr>
            <td>
                <div class="p-head">Party A — The Hotel</div>
                <table>
                    <tr><td class="k">Legal name</td><td>{{ $hotel?->legal_name ?? '—' }}</td></tr>
                    <tr><td class="k">Entity type</td><td>{{ $hotel?->entity_type ?? '—' }}</td></tr>
                    <tr><td class="k">Reg. no.</td><td>{{ $hotel?->registration_no ?? '—' }}</td></tr>
                    <tr><td class="k">Contact</td><td>{{ $hotel?->contact_person ? $hotel->contact_person.' ('.$hotel->position.')' : '—' }}</td></tr>
                    <tr><td class="k">Address</td><td>{{ $hotel?->address ?? '—' }}</td></tr>
                    <tr><td class="k">Email</td><td>{{ $hotel?->email ?? '—' }}</td></tr>
                    <tr><td class="k">Phone</td><td>{{ $hotel?->phone ?? '—' }}</td></tr>
                </table>
            </td>
            <td>
                <div class="p-head">Party B — The Client</div>
                <table>
                    <tr><td class="k">Legal name</td><td>{{ $client?->legal_name ?? '—' }}</td></tr>
                    <tr><td class="k">Entity type</td><td>{{ $client?->entity_type ?? '—' }}</td></tr>
                    <tr><td class="k">Reg. no.</td><td>{{ $client?->registration_no ?? '—' }}</td></tr>
                    <tr><td class="k">Contact</td><td>{{ $client?->contact_person ? $client->contact_person.' ('.$client->position.')' : '—' }}</td></tr>
                    <tr><td class="k">Address</td><td>{{ $client?->address ?? '—' }}</td></tr>
                    <tr><td class="k">Email</td><td>{{ $client?->email ?? '—' }}</td></tr>
                    <tr><td class="k">Phone</td><td>{{ $client?->phone ?? '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <h3 class="section">Commercial Terms</h3>
    <table class="meta-table">
        <tr>
            <td class="k">Agreement value</td>
            <td>{{ $money($agreement->value_amount) }}</td>
            <td class="k">Deposit</td>
            <td>{{ $money($agreement->deposit_amount) }}</td>
        </tr>
        <tr>
            <td class="k">Room type</td>
            <td>{{ $ct['room_type'] ?? '—' }}</td>
            <td class="k">Rooms</td>
            <td>{{ $ct['number_of_rooms'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="k">Rate / night</td>
            <td>{{ $money($ct['rate_per_night'] ?? null) }}</td>
            <td class="k">Discount</td>
            <td>{{ isset($ct['discount_percent']) && $ct['discount_percent'] !== '' ? $ct['discount_percent'].'%' : '—' }}</td>
        </tr>
        <tr>
            <td class="k">Tax applicable</td>
            <td>{{ ! empty($ct['tax_applicable']) ? 'Yes' : 'No' }}</td>
            <td class="k">Check-in / out</td>
            <td>{{ (string) ($ct['check_in_date'] ?? '—').' → '.(string) ($ct['check_out_date'] ?? '—') }}</td>
        </tr>
        <tr>
            <td class="k">Payment terms</td>
            <td>{{ $ct['payment_terms'] ?? '—' }}</td>
            <td class="k">Cancellation</td>
            <td>{{ $ct['cancellation_policy'] ?? '—' }}</td>
        </tr>
    </table>

    <h3 class="section">Terms &amp; Conditions</h3>
    @forelse ($agreement->clauses as $clause)
        <div class="clause">
            <h4>{{ $loop->iteration }}. {{ $clause->title }}</h4>
            <div class="body">{{ $clause->content }}</div>
        </div>
    @empty
        <p class="note">No clauses defined for this agreement.</p>
    @endforelse

    @if ($agreement->obligations->isNotEmpty())
        <h3 class="section">Obligations &amp; Commitments</h3>
        <table class="obligations">
            <thead>
                <tr>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 34%;">Obligation</th>
                    <th style="width: 12%;">Party</th>
                    <th style="width: 16%;">Amount</th>
                    <th style="width: 14%;">Due</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agreement->obligations as $obligation)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst($obligation->obligation_type)) }}</td>
                        <td>
                            <strong>{{ $obligation->title }}</strong>
                            @if ($obligation->description)<br><span class="note">{{ $obligation->description }}</span>@endif
                        </td>
                        <td>{{ ucfirst($obligation->responsible_party) }}</td>
                        <td>{{ $money($obligation->amount) }}</td>
                        <td>{{ $obligation->due_date?->format('d M Y') ?? '—' }}</td>
                        <td>{{ ucfirst($obligation->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3 class="section">Signatures</h3>
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-block">
                    <div class="s-head">For the Hotel — {{ $hotel?->legal_name ?? 'Party A' }}</div>
                    @php
                        $hotelSig = $signedBy('hotel');
                    @endphp
                    @if ($hotelSig?->signature_image_src)
                        <img class="sig-img" src="{{ $hotelSig->signature_image_src }}">
                    @else
                        <div class="sig-line"></div>
                    @endif
                    <div class="sig-name">{{ $hotelSig?->party_name ?? 'Name' }}</div>
                    <div class="sig-meta">{{ $hotelSig?->position ?? 'Position' }}
                        @if ($hotelSig) · {{ ucfirst($hotelSig->signature_type) }} · {{ $hotelSig->signed_at?->format('d M Y') ?? '' }}
                            @if ($hotelSig->verification) · {{ $hotelSig->verification }}@endif
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-block">
                    <div class="s-head">For the Client — {{ $client?->legal_name ?? 'Party B' }}</div>
                    @php
                        $clientSig = $signedBy('client');
                    @endphp
                    @if ($clientSig?->signature_image_src)
                        <img class="sig-img" src="{{ $clientSig->signature_image_src }}">
                    @else
                        <div class="sig-line"></div>
                    @endif
                    <div class="sig-name">{{ $clientSig?->party_name ?? 'Name' }}</div>
                    <div class="sig-meta">{{ $clientSig?->position ?? 'Position' }}
                        @if ($clientSig) · {{ ucfirst($clientSig->signature_type) }} · {{ $clientSig->signed_at?->format('d M Y') ?? '' }}
                            @if ($clientSig->verification) · {{ $clientSig->verification }}@endif
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <p class="note" style="margin-top: 20px;">
        This document was generated electronically from the Brick HMS contracts register on {{ now()->format('d M Y H:i') }} by {{ $generatedBy }}.
        Signature images (where shown) were captured electronically and are stored against agreement {{ $agreement->agreement_number }}.
    </p>

    <div id="footer">
        Generated by Brick HMS · {{ $agreement->agreement_number }} · Page <span class="page"></span> of <span class="pages"></span>
    </div>
</body>
</html>