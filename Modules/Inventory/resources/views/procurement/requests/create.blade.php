@extends('layouts.master')

@section('title', 'New Purchase Request')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:46px;height:46px;">
                <i class="fas fa-file-invoice fa-lg"></i>
            </span>
            <div>
                <h1 class="display-6 text-dark mb-0">New Purchase Request</h1>
                <p class="text-muted mb-0 small">Raise a procurement request for approval</p>
            </div>
        </div>
        <a href="{{ route('inventory.procurement.dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-route text-primary"></i>
                <h6 class="mb-0 fw-bold">Approval Flow</h6>
                <span class="badge rounded-pill bg-primary-subtle text-primary ms-1">6 approval levels</span>
            </div>
            <p class="text-muted small mb-3">Your request moves through these stages before it is fully approved and converted to a purchase order.</p>

            <div class="approval-flow">
                @foreach([
                    ['icon' => 'fa-user', 'label' => 'You', 'desc' => 'Submit the request', 'state' => 'active'],
                    ['icon' => 'fa-shopping-cart', 'label' => 'Purchaser', 'desc' => 'Reviews & sources supplier'],
                    ['icon' => 'fa-gavel', 'label' => 'GM', 'desc' => 'Approves the budget'],
                    ['icon' => 'fa-calculator', 'label' => 'Finance', 'desc' => 'Verifies available funds'],
                    ['icon' => 'fa-shield-alt', 'label' => 'Auditor', 'desc' => 'Audits for compliance'],
                    ['icon' => 'fa-crown', 'label' => 'GGM', 'desc' => 'Gives final sign-off'],
                    ['icon' => 'fa-check-circle', 'label' => 'Approved', 'desc' => 'Converted to PO'],
                ] as $step)
                    <div class="flow-step {{ ($step['state'] ?? '') === 'active' ? 'active' : '' }}">
                        <div class="flow-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                        <div class="flow-label">{{ $step['label'] }}</div>
                        <div class="flow-desc">{{ $step['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            <p class="text-muted small mb-0 mt-3">
                <i class="fas fa-info-circle me-1"></i>
                On submit, the request goes straight to the <strong>Purchaser</strong> for review.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.procurement.requests.store') }}" id="prForm" novalidate>
        @csrf
        <div class="row align-items-start">
            <div class="col-lg-8">

                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle text-primary"></i>
                        <h5 class="mb-0 fw-bold">Request Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Department</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                                    <input type="text" name="department" class="form-control border-start-0" placeholder="e.g. Kitchen, Housekeeping, Front Desk" value="{{ old('department') }}">
                                </div>
                                <div class="form-text">Which department is raising this request?</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2">Urgency</label>
                                <div class="btn-group w-100" role="group" aria-label="Urgency">
                                    <input type="radio" class="btn-check" name="urgency" id="urg_normal" value="normal" autocomplete="off" {{ (old('urgency', 'normal') === 'normal') ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary" for="urg_normal"><i class="fas fa-clock me-1"></i>Normal</label>
                                    <input type="radio" class="btn-check" name="urgency" id="urg_urgent" value="urgent" autocomplete="off" {{ (old('urgency') === 'urgent') ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="urg_urgent"><i class="fas fa-exclamation-circle me-1"></i>Urgent</label>
                                    <input type="radio" class="btn-check" name="urgency" id="urg_emergency" value="emergency" autocomplete="off" {{ (old('urgency') === 'emergency') ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="urg_emergency"><i class="fas fa-bolt me-1"></i>Emergency</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Justification / Purpose</label>
                                <textarea name="justification" class="form-control" rows="4" placeholder="Explain why these items are needed..." required>{{ old('justification') }}</textarea>
                                <div class="form-text">A clear justification helps approvers move faster.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-list text-primary"></i>Items
                            <span id="itemCount" class="badge bg-primary-subtle text-primary rounded-pill">0</span>
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="fas fa-plus me-1"></i>Add Item
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="rowError" class="alert alert-warning alert-dismissible fade show d-none mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Please provide an <strong>Item Name</strong> and a <strong>Quantity &gt; 0</strong> for every item row before submitting.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="itemsContainer">
                            @php
                                $items = old('items');
                                if (empty($items)) {
                                    $items = [['item_name' => '', 'quantity' => '', 'estimated_unit_price' => '', 'notes' => '']];
                                }
                            @endphp
                            @foreach($items as $i => $item)
                                <div class="item-row border rounded p-3 mb-3 bg-light position-relative">
                                    <span class="item-index badge bg-primary text-white position-absolute top-0 start-0 translate-middle rounded-circle" style="width:26px;height:26px;font-size:.75rem;">{{ $i + 1 }}</span>
                                    <div class="row g-2 align-items-end ps-2">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Item Name</label>
                                            <input type="text" name="items[{{ $i }}][item_name]" class="form-control" placeholder="Item name" value="{{ $item['item_name'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold small">Quantity</label>
                                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control" step="0.01" min="0.01" placeholder="1" value="{{ $item['quantity'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Est. Unit Price (₦)</label>
                                            <input type="number" name="items[{{ $i }}][estimated_unit_price]" class="form-control" step="0.01" min="0" placeholder="Optional" value="{{ $item['estimated_unit_price'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Notes</label>
                                            <input type="text" name="items[{{ $i }}][notes]" class="form-control" placeholder="Optional" value="{{ $item['notes'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 ps-2">
                                        <span class="small text-muted">Subtotal: <span class="item-subtotal fw-semibold text-dark">₦0.00</span></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" {{ $loop->first ? 'style="display:none;"' : '' }}>
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" style="position:sticky; top:90px; align-self:start;">
                <div class="card shadow border-0 mb-4 border-start border-primary border-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                        <i class="fas fa-paper-plane text-primary"></i>
                        <h5 class="mb-0 fw-bold">Submit</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2 small">
                            <span class="text-muted">Item types</span>
                            <span class="fw-semibold" id="sumItems">0</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2 small">
                            <span class="text-muted">Total quantity</span>
                            <span class="fw-semibold" id="sumQty">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-muted">Estimated total</span>
                            <span class="fw-bold text-primary" id="sumCost">₦0.00</span>
                        </div>
                        <p class="text-muted small mb-3">Your request will first go to the <strong>Purchaser</strong> for review after submission.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save as Draft
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="submitAndSend">
                                <i class="fas fa-paper-plane me-2"></i>Submit for Review
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<style>
    .approval-flow { display: flex; align-items: flex-start; overflow-x: auto; padding-bottom: 4px; }
    .flow-step { flex: 1 0 120px; text-align: center; position: relative; padding: 0 6px; }
    .flow-dot {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eef2ff; color: #0d6efd; font-size: 1rem;
        position: relative; z-index: 1;
    }
    .flow-step.active .flow-dot { background: #0d6efd; color: #fff; box-shadow: 0 0 0 4px rgba(13,110,253,.15); }
    .flow-step:not(:last-child)::after {
        content: ''; position: absolute; top: 22px;
        left: calc(50% + 22px); right: calc(-50% + 22px);
        height: 2px; background: #e3e6f0; z-index: 0;
    }
    .flow-label { font-weight: 600; font-size: .85rem; margin-top: 8px; }
    .flow-desc { font-size: .72rem; color: #6c757d; margin-top: 2px; line-height: 1.2; }
</style>
<script>
    let itemIndex = document.querySelectorAll('.item-row').length;

    const naira = n => '₦' + Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recalc() {
        let types = 0, qty = 0, cost = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const q = parseFloat(row.querySelector('input[name*="[quantity]"]')?.value) || 0;
            const p = parseFloat(row.querySelector('input[name*="[estimated_unit_price]"]')?.value) || 0;
            const sub = q * p;
            const st = row.querySelector('.item-subtotal');
            if (st) st.textContent = naira(sub);
            if (q > 0) { types++; qty += q; }
            cost += sub;
        });
        const ic = document.getElementById('itemCount');
        if (ic) ic.textContent = document.querySelectorAll('.item-row').length;
        const si = document.getElementById('sumItems');
        const sq = document.getElementById('sumQty');
        const sc = document.getElementById('sumCost');
        if (si) si.textContent = types;
        if (sq) sq.textContent = qty;
        if (sc) sc.textContent = naira(cost);
    }

    const rowError = document.getElementById('rowError');
    function showRowError() { if (rowError) rowError.classList.remove('d-none'); }
    function hideRowError() { if (rowError) rowError.classList.add('d-none'); }
    rowError?.querySelector('.btn-close')?.addEventListener('click', hideRowError);

    document.getElementById('addItemBtn')?.addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const template = document.querySelector('.item-row').cloneNode(true);
        template.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name').replace(/\[\d+\]/, `[${itemIndex}]`);
            input.setAttribute('name', name);
            input.value = '';
            input.removeAttribute('required');
        });
        const idx = template.querySelector('.item-index');
        if (idx) idx.textContent = itemIndex + 1;
        template.classList.remove('border-danger');
        const btn = template.querySelector('.remove-item');
        btn.style.display = 'inline-block';
        container.appendChild(template);
        itemIndex++;
        recalc();
        hideRowError();
    });

    document.getElementById('itemsContainer')?.addEventListener('click', function(e) {
        const rm = e.target.closest('.remove-item');
        if (rm) {
            if (document.querySelectorAll('.item-row').length > 1) {
                rm.closest('.item-row').remove();
                reindex();
            }
            recalc();
            hideRowError();
        }
    });

    document.getElementById('itemsContainer')?.addEventListener('input', function() {
        recalc();
    });

    function reindex() {
        document.querySelectorAll('.item-row').forEach((row, i) => {
            row.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name').replace(/items\[\d+\]/, `items[${i}]`);
                input.setAttribute('name', name);
            });
            const idx = row.querySelector('.item-index');
            if (idx) idx.textContent = i + 1;
            const btn = row.querySelector('.remove-item');
            if (btn) btn.style.display = (i === 0) ? 'none' : 'inline-block';
        });
        itemIndex = document.querySelectorAll('.item-row').length;
    }

    function cleanupEmptyRows() {
        document.querySelectorAll('.item-row').forEach(row => {
            const name = row.querySelector('input[name*="[item_name]"]');
            const qty = row.querySelector('input[name*="[quantity]"]');
            const empty = (!name || !name.value.trim()) && (!qty || !qty.value.trim());
            if (empty) row.remove();
        });
        reindex();
    }

    function validateRows() {
        let ok = true;
        let firstInvalid = null;
        document.querySelectorAll('.item-row').forEach(row => {
            const name = row.querySelector('input[name*="[item_name]"]');
            const qty = row.querySelector('input[name*="[quantity]"]');
            const nameOk = name && name.value.trim() !== '';
            const qtyOk = qty && qty.value.trim() !== '' && parseFloat(qty.value) > 0;
            row.classList.remove('border-danger');
            if (!nameOk || !qtyOk) {
                ok = false;
                row.classList.add('border-danger');
                if (!firstInvalid) firstInvalid = row;
            }
        });
        return { ok, firstInvalid };
    }

    function guardSubmit() {
        cleanupEmptyRows();
        const { ok, firstInvalid } = validateRows();
        if (!ok) {
            showRowError();
            if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        hideRowError();
        return true;
    }

    document.getElementById('prForm')?.addEventListener('submit', function(e) {
        if (!guardSubmit()) e.preventDefault();
    });

    document.getElementById('submitAndSend')?.addEventListener('click', function() {
        const form = document.getElementById('prForm');
        if (!guardSubmit()) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'submit_and_send';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    });

    recalc();
</script>
@endsection
