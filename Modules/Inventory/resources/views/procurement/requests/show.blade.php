@extends('layouts.master')

@section('title', 'PR ' . $purchaseRequest->pr_number)

@section('page-content')
<div class="container-fluid p-4">
    @php
        $user = auth()->user();
        $pr = $purchaseRequest;
        $isRequester = $pr->requester_id === $user->id;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-3">
                <h1 class="display-5 text-dark mb-0">PR: {{ $pr->pr_number }}</h1>
                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::statusBadge($pr->status) }} fs-6">
                    {{ \Modules\Inventory\Models\PurchaseRequest::statusLabel($pr->status) }}
                </span>
                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::urgencyBadge($pr->urgency) }} fs-6">
                    {{ ucfirst($pr->urgency) }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($pr->status, ['draft', 'flagged']) && $isRequester)
                <a href="{{ route('inventory.procurement.requests.edit', $pr) }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
            <a href="{{ route('inventory.procurement.dashboard') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
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

    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i>Request Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">PR Number</small>
                            <span class="fw-bold">{{ $pr->pr_number }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Requester</small>
                            <span class="fw-bold">{{ $pr->requester->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Department</small>
                            <span>{{ $pr->department ?? '—' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Supplier</small>
                            <span>{{ $pr->supplier->name ?? '—' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">GL Code</small>
                            <span>{{ $pr->gl_code ?? '—' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Cost Center</small>
                            <span>{{ $pr->cost_center ?? '—' }}</span>
                        </div>
                        @if($pr->invoice_path)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Invoice</small>
                            <a href="{{ Storage::url($pr->invoice_path) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i>View Invoice
                            </a>
                        </div>
                        @endif
                        <div class="col-12">
                            <small class="text-muted d-block">Justification</small>
                            <p class="mb-0">{{ $pr->justification }}</p>
                        </div>
                        @if($pr->procurement_notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Procurement Notes</small>
                            <p class="mb-0">{{ $pr->procurement_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Requested Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Est. Unit Price</th>
                                    <th class="text-end pe-4">Est. Total</th>
                                    @if($pr->status === 'pending_purchaser' && $user->hasRole('purchaser'))
                                    <th class="text-end pe-4">Unit Price</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pr->items as $item)
                                <tr>
                                    <td class="ps-4">{{ $item->item_name }}
                                        @if($item->notes)
                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-end">
                                        @if($item->estimated_unit_price)
                                            ₦{{ number_format($item->estimated_unit_price, 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 fw-bold">
                                        @if($item->estimated_unit_price)
                                            ₦{{ number_format($item->quantity * $item->estimated_unit_price, 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    @if($pr->status === 'pending_purchaser' && $user->hasRole('purchaser'))
                                    <td class="text-end pe-4" style="width:140px;">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm item-price text-end"
                                               data-item-id="{{ $item->id }}" placeholder="0.00" value="{{ $item->estimated_unit_price ?? '' }}">
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            @if($pr->items->where('estimated_unit_price')->count())
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end ps-4">Estimated Total:</th>
                                    <th class="text-end pe-4">
                                        ₦{{ number_format($pr->items->sum(fn($i) => ($i->estimated_unit_price ?? 0) * $i->quantity), 2) }}
                                    </th>
                                    @if($pr->status === 'pending_purchaser' && $user->hasRole('purchaser'))
                                    <th></th>
                                    @endif
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Action Forms --}}
            @if($pr->status === 'draft' && $isRequester)
            <div class="card shadow border-0 mb-4 border-start border-success border-4">
                <div class="card-body p-4 text-center">
                    <h5><i class="fas fa-paper-plane text-success me-2"></i>Ready to Submit?</h5>
                    <p class="text-muted">Submit this request to the Purchaser for review.</p>
                    <form action="{{ route('inventory.procurement.submit', $pr) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-5" onclick="return confirm('Submit this request for review?')">
                            <i class="fas fa-paper-plane me-2"></i>Submit for Review
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Purchaser Review Form --}}
            @if($pr->status === 'pending_purchaser' && $user->hasRole('purchaser'))
            <div class="card shadow border-0 mb-4 border-start border-warning border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-warning"><i class="fas fa-clipboard-check me-2"></i>Purchaser Review</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('inventory.procurement.review', $pr) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Supplier</label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $pr->supplier_id === $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">GL Code</label>
                                <input type="text" name="gl_code" class="form-control" value="{{ $pr->gl_code }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cost Center</label>
                                <input type="text" name="cost_center" class="form-control" value="{{ $pr->cost_center }}">
                            </div>
                            <div class="col-12">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Invoice (PDF/JPG/PNG)</label>
                                        <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-outline-info" formaction="{{ route('inventory.procurement.upload-invoice', $pr) }}">
                                            <i class="fas fa-upload me-1"></i>Upload Invoice
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Procurement Notes</label>
                                <textarea name="procurement_notes" class="form-control" rows="3" placeholder="Vendor selection notes, delivery terms...">{{ $pr->procurement_notes }}</textarea>
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="items" id="pricingItems">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>Set unit prices for each item in the table above before submitting.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success" onclick="return collectPrices()">
                                        <i class="fas fa-check me-1"></i>Submit to GM
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#flagModal">
                                        <i class="fas fa-flag me-1"></i>Flag as Incomplete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- GM Approval Card --}}
            @if($pr->status === 'pending_gm' && $user->hasRole('gm'))
            <div class="card shadow border-0 mb-4 border-start border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-gavel me-2"></i>GM Review</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2">
                        <form action="{{ route('inventory.procurement.approve', $pr) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Approve this request?')">
                                <i class="fas fa-check me-2"></i>Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                        <button type="button" class="btn btn-outline-info btn-lg" data-bs-toggle="modal" data-bs-target="#flagModal">
                            <i class="fas fa-flag me-2"></i>Flag as Duplicate
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Finance Approval Card --}}
            @if($pr->status === 'pending_finance' && $user->hasRole('finance'))
            <div class="card shadow border-0 mb-4 border-start border-success border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="fas fa-calculator me-2"></i>Finance Verification</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2">
                        <form action="{{ route('inventory.procurement.approve', $pr) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Confirm financial integrity?')">
                                <i class="fas fa-check me-2"></i>Confirm &amp; Send to Auditor
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Auditor Approval Card --}}
            @if($pr->status === 'pending_auditor' && $user->hasRole('auditor'))
            <div class="card shadow border-0 mb-4 border-start border-info border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-info"><i class="fas fa-shield-alt me-2"></i>Audit Review</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2">
                        <form action="{{ route('inventory.procurement.approve', $pr) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Confirm compliance and accuracy?')">
                                <i class="fas fa-check me-2"></i>Confirm &amp; Send to GGM
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- GGM Final Approval Card --}}
            @if($pr->status === 'pending_ggm' && $user->hasRole('ggm'))
            <div class="card shadow border-0 mb-4 border-start border-dark border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-crown me-2"></i>GGM Final Approval</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2">
                        <form action="{{ route('inventory.procurement.approve', $pr) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Give final approval? This will complete the approval process.')">
                                <i class="fas fa-check me-2"></i>Final Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Convert to PO (GGM after approval) --}}
            @if($pr->status === 'approved' && $user->hasRole('ggm'))
            <div class="card shadow border-0 mb-4 border-start border-success border-4">
                <div class="card-body p-4 text-center">
                    <h5 class="text-success"><i class="fas fa-check-circle me-2"></i>Fully Approved</h5>
                    <p class="text-muted">Convert this request to a Purchase Order to begin procurement execution.</p>
                    <form action="{{ route('inventory.procurement.convert-to-po', $pr) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-5" onclick="return confirm('Convert to Purchase Order?')">
                            <i class="fas fa-shopping-cart me-2"></i>Convert to Purchase Order
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Approval Log --}}
            @if($pr->approvals->isNotEmpty())
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i>Approval Log</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Role</th>
                                    <th>Action</th>
                                    <th>By</th>
                                    <th>Notes</th>
                                    <th class="text-end pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pr->approvals as $log)
                                <tr>
                                    <td class="ps-4">{{ ucfirst(str_replace('_', ' ', $log->role)) }}</td>
                                    <td>
                                        <span class="badge {{ $log->action === 'rejected' ? 'bg-danger' : ($log->action === 'flagged' ? 'bg-info' : 'bg-success') }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->user->name ?? 'N/A' }}</td>
                                    <td class="text-truncate" style="max-width:200px;">{{ $log->notes ?? '—' }}</td>
                                    <td class="text-end pe-4">{{ $log->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-timeline me-2"></i>Status Timeline</h5>
                </div>
                <div class="card-body p-4">
                    @php
                        $stages = ['draft', 'pending_purchaser', 'pending_gm', 'pending_finance', 'pending_auditor', 'pending_ggm', 'approved'];
                        $stageLabels = ['Draft', 'Purchaser', 'GM', 'Finance', 'Auditor', 'GGM', 'Approved'];
                        $currentIdx = array_search($pr->status, $stages);
                        if ($currentIdx === false && $pr->status === 'ordered') $currentIdx = 6;
                        if ($currentIdx === false) $currentIdx = array_search($pr->status, ['rejected', 'flagged']) !== false ? -1 : -1;
                    @endphp
                    <ul class="timeline">
                        @foreach($stages as $i => $stage)
                            @php
                                $isActive = $i <= $currentIdx;
                                $isCurrent = $i === $currentIdx;
                                $isRejected = in_array($pr->status, ['rejected', 'flagged']);
                            @endphp
                            <li class="timeline-item">
                                <div class="timeline-badge {{ $isActive ? ($isCurrent ? 'bg-warning' : 'bg-success') : 'bg-secondary' }}">
                                    <i class="fas {{ $isActive ? 'fa-check' : 'fa-clock' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>{{ $stageLabels[$i] }}</strong>
                                    @if($isCurrent && !$isRejected)
                                        <small class="text-warning d-block fw-bold">Current Stage</small>
                                    @elseif($isActive && !$isCurrent)
                                        <small class="text-success d-block">Completed</small>
                                    @elseif($isRejected)
                                        <small class="text-danger d-block">—</small>
                                    @else
                                        <small class="text-muted d-block">Pending</small>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                        @if($pr->status === 'rejected')
                            <li class="timeline-item">
                                <div class="timeline-badge bg-danger"><i class="fas fa-times"></i></div>
                                <div class="timeline-content">
                                    <strong>Rejected</strong>
                                    <small class="text-danger d-block fw-bold">Returned for correction</small>
                                </div>
                            </li>
                        @endif
                        @if($pr->status === 'flagged')
                            <li class="timeline-item">
                                <div class="timeline-badge bg-info"><i class="fas fa-flag"></i></div>
                                <div class="timeline-content">
                                    <strong>Flagged</strong>
                                    <small class="text-info d-block fw-bold">Needs clarification</small>
                                </div>
                            </li>
                        @endif
                        @if(in_array($pr->status, ['approved', 'ordered']))
                            <li class="timeline-item">
                                <div class="timeline-badge bg-success"><i class="fas fa-check-double"></i></div>
                                <div class="timeline-content">
                                    <strong>Approved</strong>
                                    @if($pr->status === 'ordered')
                                        <small class="text-success d-block">Converted to PO</small>
                                    @else
                                        <small class="text-success d-block">Fully Approved</small>
                                    @endif
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Items:</span>
                        <span class="fw-bold">{{ $pr->items->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Qty:</span>
                        <span class="fw-bold">{{ $pr->items->sum('quantity') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Est. Total:</span>
                        <span class="fw-bold">₦{{ number_format($pr->items->sum(fn($i) => ($i->estimated_unit_price ?? 0) * $i->quantity), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Created:</span>
                        <span>{{ $pr->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-times-circle me-2"></i>Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.procurement.reject', $pr) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-semibold">Reason for Rejection</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Explain why this request is being rejected..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-times me-2"></i>Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Flag Modal --}}
<div class="modal fade" id="flagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info"><i class="fas fa-flag me-2"></i>Flag Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.procurement.flag', $pr) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-semibold">Reason for Flagging</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Explain what needs clarification..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-4 text-white">
                        <i class="fas fa-flag me-2"></i>Flag Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .timeline { list-style: none; padding: 0; margin: 0; position: relative; }
    .timeline::before { content: ''; position: absolute; left: 18px; top: 0; bottom: 0; width: 2px; background: #e3e6f0; }
    .timeline-item { position: relative; padding-left: 50px; padding-bottom: 20px; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-badge { position: absolute; left: 0; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.85rem; z-index: 1; }
    .timeline-content strong { display: block; margin-bottom: 2px; }
    .timeline-content small { font-size: 0.8rem; }
</style>
@endsection

@section('scripts')
<script>
function collectPrices() {
    const items = [];
    document.querySelectorAll('.item-price').forEach(input => {
        if (input.value) {
            items.push({ id: input.dataset.itemId, unit_price: input.value });
        }
    });
    if (items.length === 0) {
        alert('Please set unit prices for all items before submitting.');
        return false;
    }
    document.getElementById('pricingItems').value = JSON.stringify(items);
    return true;
}
</script>
@endsection
