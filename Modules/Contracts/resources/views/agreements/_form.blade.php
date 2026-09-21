@php
    $editing = isset($agreement) && $agreement ? true : false;
    $agreement = $agreement ?? null;
    $selectedTemplate = $selectedTemplate ?? null;
    $ct = $agreement?->commercial_terms ?? [];

    $clauses = old('clauses', $editing
        ? $agreement->clauses->map(fn ($c) => ['title' => $c->title, 'content' => $c->content, 'sort_order' => $c->sort_order])->values()->all()
        : ($selectedTemplate ? $selectedTemplate->clauses->map(fn ($c) => ['title' => $c->title, 'content' => $c->content, 'sort_order' => $c->sort_order])->values()->all() : []));

    $obligations = old('obligations', $editing
        ? $agreement->obligations->map(fn ($o) => [
            'obligation_type' => $o->obligation_type,
            'title' => $o->title,
            'description' => $o->description,
            'responsible_party' => $o->responsible_party,
            'amount' => $o->amount,
            'due_date' => $o->due_date?->format('Y-m-d'),
        ])->values()->all()
        : []);
@endphp

@php
    $action = $editing ? route('contracts.agreements.update', $agreement) : route('contracts.agreements.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

<form method="POST" action="{{ $action }}" autocomplete="off">
    @csrf
    @method($method)

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            {{-- 1. AGREEMENT INFORMATION --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">1. Agreement Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $agreement?->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Agreement Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', $agreement?->type) === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="NGN" @selected(old('currency', $agreement?->currency ?? 'NGN') === 'NGN')>NGN – Naira</option>
                                <option value="USD" @selected(old('currency', $agreement?->currency) === 'USD')>USD – US Dollar</option>
                                <option value="GBP" @selected(old('currency', $agreement?->currency) === 'GBP')>GBP – Pound</option>
                                <option value="EUR" @selected(old('currency', $agreement?->currency) === 'EUR')>EUR – Euro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Property / Branch</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $agreement?->location) }}" placeholder="Brickspoint Asokoro">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department', $agreement?->department) }}" placeholder="Sales / F&amp;B / Ops">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', $agreement?->effective_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $agreement?->expiry_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agreement Value</label>
                            <input type="number" step="0.01" min="0" name="value_amount" class="form-control" value="{{ old('value_amount', $agreement?->value_amount) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Deposit</label>
                            <input type="number" step="0.01" min="0" name="deposit_amount" class="form-control" value="{{ old('deposit_amount', $agreement?->deposit_amount) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="auto_renew" value="0">
                                <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="autoRenew"
                                       @checked((bool) old('auto_renew', $agreement?->auto_renew))>
                                <label class="form-check-label" for="autoRenew">Auto-renewal eligible (creates a renewal task, never silently renews)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $agreement?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. COMMERCIAL TERMS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">2. Commercial Terms</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Room Type</label>
                            <input type="text" name="room_type" class="form-control" value="{{ old('room_type', $ct['room_type'] ?? '') }}" placeholder="Executive Apartment">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rooms</label>
                            <input type="number" min="0" name="number_of_rooms" class="form-control" value="{{ old('number_of_rooms', $ct['number_of_rooms'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate / Night</label>
                            <input type="number" step="0.01" min="0" name="rate_per_night" class="form-control" value="{{ old('rate_per_night', $ct['rate_per_night'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount %</label>
                            <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ old('discount_percent', $ct['discount_percent'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check-in</label>
                            <input type="date" name="check_in_date" class="form-control" value="{{ old('check_in_date', $ct['check_in_date'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check-out</label>
                            <input type="date" name="check_out_date" class="form-control" value="{{ old('check_out_date', $ct['check_out_date'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $ct['payment_terms'] ?? '') }}" placeholder="Monthly / 7 days">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cancellation Policy</label>
                            <input type="text" name="cancellation_policy" class="form-control" value="{{ old('cancellation_policy', $ct['cancellation_policy'] ?? '') }}" placeholder="14 days notice">
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tax_applicable" value="0">
                                <input class="form-check-input" type="checkbox" name="tax_applicable" value="1" id="taxApplicable"
                                       @checked((bool) old('tax_applicable', $ct['tax_applicable'] ?? false))>
                                <label class="form-check-label" for="taxApplicable">Tax applicable</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. DETAILED CLAUSES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">3. Detailed Clauses</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addClauseBtn">
                        <i class="fas fa-plus me-1"></i> Add Clause
                    </button>
                </div>
                <div class="card-body">
                    @if (! $editing && $selectedTemplate)
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-file-invoice me-1"></i>
                            Clauses pre-filled from template
                            <strong>"{{ $selectedTemplate->name }}"</strong> — edit or add more below.
                        </div>
                    @endif
                    <div id="clausesWrapper">
                        @foreach ($clauses as $index => $clause)
                            <div class="clause-row border rounded-3 p-3 mb-3 bg-light">
                                <div class="row g-2 align-items-start">
                                    <div class="col-md-4">
                                        <input type="text" name="clauses[{{ $index }}][title]" class="form-control form-control-sm"
                                               placeholder="Clause title, e.g. Payment" value="{{ $clause['title'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-center gap-2">
                                        <input type="hidden" name="clauses[{{ $index }}][sort_order]" value="{{ $loop->index }}">
                                        <button type="button" class="btn btn-sm btn-outline-secondary remove-clause" title="Move up"><i class="fas fa-arrow-up"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-clause" title="Remove clause"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="col-12">
                                        <textarea name="clauses[{{ $index }}][content]" rows="3" class="form-control" placeholder="Clause body text..." required>{{ $clause['content'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (empty($clauses))
                        <div id="noClauses" class="text-muted small py-2">No clauses yet — add clauses such as Accommodation, Payment, Cancellation, No-show, Liability, Force Majeure...</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- OPTIONAL TEMPLATE --}}
            @if (! $editing)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Start From Template</div>
                    <div class="card-body">
                        <select name="template_select" id="templateSelect" class="form-select">
                            <option value="">— Select template (optional) —</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pre-fills standard clauses. Rarely changes — use amendments later.</div>
                    </div>
                </div>
            @endif

            {{-- PARTIES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">4. Parties</div>
                <div class="card-body">
                    <h6 class="fw-semibold small text-uppercase">Party A — Hotel <span class="text-danger">*</span></h6>
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <input type="text" name="hotel_legal_name" class="form-control @error('hotel_legal_name') is-invalid @enderror"
                                   value="{{ old('hotel_legal_name', $agreement?->hotelParty()?->legal_name ?? 'Brickspoint Boutique Aparthotel') }}" placeholder="Legal name" required>
                            @error('hotel_legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <input type="text" name="hotel_contact_person" class="form-control" value="{{ old('hotel_contact_person', $agreement?->hotelParty()?->contact_person) }}" placeholder="Authorized representative">
                        </div>
                        <div class="col-12">
                            <input type="text" name="hotel_position" class="form-control" value="{{ old('hotel_position', $agreement?->hotelParty()?->position) }}" placeholder="Position, e.g. General Manager">
                        </div>
                        <div class="col-12">
                            <input type="text" name="hotel_address" class="form-control" value="{{ old('hotel_address', $agreement?->hotelParty()?->address) }}" placeholder="Address">
                        </div>
                        <div class="col-6">
                            <input type="email" name="hotel_email" class="form-control" value="{{ old('hotel_email', $agreement?->hotelParty()?->email) }}" placeholder="Email">
                        </div>
                        <div class="col-6">
                            <input type="text" name="hotel_phone" class="form-control" value="{{ old('hotel_phone', $agreement?->hotelParty()?->phone) }}" placeholder="Phone">
                        </div>
                    </div>

                    <h6 class="fw-semibold small text-uppercase">Party B — Client <span class="text-danger">*</span></h6>
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <input type="text" name="client_legal_name" class="form-control @error('client_legal_name') is-invalid @enderror"
                                   value="{{ old('client_legal_name', $agreement?->primaryClient()?->legal_name) }}" placeholder="Legal / company name" required>
                            @error('client_legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <input type="text" name="client_registration_no" class="form-control" value="{{ old('client_registration_no', $agreement?->primaryClient()?->registration_no) }}" placeholder="Company registration (RC/CAC)">
                        </div>
                        <div class="col-12">
                            <input type="text" name="client_contact_person" class="form-control" value="{{ old('client_contact_person', $agreement?->primaryClient()?->contact_person) }}" placeholder="Contact person">
                        </div>
                        <div class="col-12">
                            <input type="text" name="client_position" class="form-control" value="{{ old('client_position', $agreement?->primaryClient()?->position) }}" placeholder="Position / Title">
                        </div>
                        <div class="col-12">
                            <input type="text" name="client_address" class="form-control" value="{{ old('client_address', $agreement?->primaryClient()?->address) }}" placeholder="Address">
                        </div>
                        <div class="col-6">
                            <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $agreement?->primaryClient()?->email) }}" placeholder="Email">
                        </div>
                        <div class="col-6">
                            <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone', $agreement?->primaryClient()?->phone) }}" placeholder="Phone">
                        </div>
                    </div>
                </div>
            </div>

            {{-- OBLIGATIONS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">5. Obligations / Commitments</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addObligationBtn"><i class="fas fa-plus"></i></button>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-3">Agreement engine: due payments, room allocations, entitlements, renewals.</div>
                    <div id="obligationsWrapper">
                        @foreach ($obligations as $index => $obligation)
                            <div class="obligation-row border rounded-3 p-2 mb-2 bg-light">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <select name="obligations[{{ $index }}][obligation_type]" class="form-select form-select-sm">
                                            @foreach (['payment' => 'Payment', 'service' => 'Service', 'delivery' => 'Delivery / Rooms', 'entitlement' => 'Entitlement / Benefit', 'renewal' => 'Renewal', 'other' => 'Other'] as $key => $label)
                                                <option value="{{ $key }}" @selected(($obligation['obligation_type'] ?? '') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="obligations[{{ $index }}][title]" class="form-control form-control-sm" placeholder="e.g. Monthly invoice by the 5th" value="{{ $obligation['title'] ?? '' }}" required>
                                    </div>
                                    <div class="col-4">
                                        <select name="obligations[{{ $index }}][responsible_party]" class="form-select form-select-sm">
                                            <option value="client" @selected(($obligation['responsible_party'] ?? '') === 'client')>Client</option>
                                            <option value="hotel" @selected(($obligation['responsible_party'] ?? '') === 'hotel')>Hotel</option>
                                            <option value="other" @selected(($obligation['responsible_party'] ?? '') === 'other')>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" min="0" name="obligations[{{ $index }}][amount]" class="form-control form-control-sm" placeholder="Amount" value="{{ $obligation['amount'] ?? '' }}">
                                    </div>
                                    <div class="col-6 d-flex gap-2">
                                        <input type="date" name="obligations[{{ $index }}][due_date]" class="form-control form-control-sm" value="{{ $obligation['due_date'] ?? '' }}">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-obligation"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (empty($obligations))
                        <div id="noObligations" class="text-muted small py-2">No obligations tracked yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ $editing ? route('contracts.agreements.show', $agreement) : route('contracts.agreements.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i>{{ $editing ? 'Save Agreement' : 'Create Agreement' }}</button>
    </div>
</form>

<style>
    .contracts-form { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
</style>
<script>
    let clauseIndex = {{ count($clauses) }};
    let obligationIndex = {{ count($obligations) }};

    function addClause(title = '', content = '') {
        const wrapper = document.getElementById('clausesWrapper');
        const no = document.getElementById('noClauses');
        if (no) no.remove();
        const row = document.createElement('div');
        row.className = 'clause-row border rounded-3 p-3 mb-3 bg-light';
        row.innerHTML = `
            <div class="row g-2 align-items-start">
                <div class="col-md-4">
                    <input type="text" name="clauses[${clauseIndex}][title]" class="form-control form-control-sm" placeholder="Clause title, e.g. Payment" value="${title}" required>
                </div>
                <div class="col-md-8 d-flex align-items-center gap-2">
                    <input type="hidden" name="clauses[${clauseIndex}][sort_order]" value="${clauseIndex}">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-clause"><i class="fas fa-trash"></i></button>
                </div>
                <div class="col-12">
                    <textarea name="clauses[${clauseIndex}][content]" rows="3" class="form-control" placeholder="Clause body text..." required>${content}</textarea>
                </div>
            </div>`;
        wrapper.appendChild(row);
        clauseIndex++;
    }

    function addObligation() {
        const wrapper = document.getElementById('obligationsWrapper');
        const no = document.getElementById('noObligations');
        if (no) no.remove();
        const i = obligationIndex;
        const row = document.createElement('div');
        row.className = 'obligation-row border rounded-3 p-2 mb-2 bg-light';
        row.innerHTML = `
            <div class="row g-2">
                <div class="col-12">
                    <select name="obligations[${i}][obligation_type]" class="form-select form-select-sm">
                        <option value="payment">Payment</option>
                        <option value="service">Service</option>
                        <option value="delivery">Delivery / Rooms</option>
                        <option value="entitlement">Entitlement / Benefit</option>
                        <option value="renewal">Renewal</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-8">
                    <input type="text" name="obligations[${i}][title]" class="form-control form-control-sm" placeholder="e.g. Monthly invoice by the 5th" required>
                </div>
                <div class="col-4">
                    <select name="obligations[${i}][responsible_party]" class="form-select form-select-sm">
                        <option value="client">Client</option>
                        <option value="hotel">Hotel</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-6">
                    <input type="number" step="0.01" min="0" name="obligations[${i}][amount]" class="form-control form-control-sm" placeholder="Amount">
                </div>
                <div class="col-6 d-flex gap-2">
                    <input type="date" name="obligations[${i}][due_date]" class="form-control form-control-sm">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-obligation"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
        wrapper.appendChild(row);
        obligationIndex++;
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#addClauseBtn')) addClause();
        if (e.target.closest('#addObligationBtn')) addObligation();
        if (e.target.closest('.remove-clause')) e.target.closest('.clause-row').remove();
        if (e.target.closest('.remove-obligation')) e.target.closest('.obligation-row').remove();
    });

    @if (! $editing && isset($templates))
    document.getElementById('templateSelect').addEventListener('change', function () {
        if (this.value) {
            window.location.href = "{{ route('contracts.agreements.create') }}?template=" + this.value;
        }
    });
    @endif
</script>