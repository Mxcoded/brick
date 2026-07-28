@extends('layouts.master')

@section('title', "Folio {$folio->folio_number}")
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Folio: {{ $folio->folio_number }}</h4>
        <div>
            <a href="{{ route('frontdesk.folios.index', $folio->registration_id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>All Folios
            </a>
            @if($folio->status === 'open')
            <button class="btn btn-sm btn-outline-success ms-2" data-bs-toggle="modal" data-bs-target="#postChargeModal">
                <i class="fas fa-plus me-1"></i>Post Charge
            </button>
            <button class="btn btn-sm btn-outline-info ms-1" data-bs-toggle="modal" data-bs-target="#splitFolioModal">
                <i class="fas fa-code-branch me-1"></i>Split
            </button>
            <button class="btn btn-sm btn-outline-primary ms-1" onclick="document.getElementById('generateInvoiceForm').submit()">
                <i class="fas fa-file-invoice me-1"></i>Generate Invoice
            </button>
            <form id="generateInvoiceForm" action="{{ route('frontdesk.invoices.create-from-folio', $folio) }}" method="POST" class="d-none">
                @csrf
            </form>
            <form action="{{ route('frontdesk.folios.close', $folio) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Close this folio?')">
                @csrf
                <button class="btn btn-sm btn-outline-secondary ms-1"><i class="fas fa-check me-1"></i>Close</button>
            </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6>Folio Name</h6>
                    <h5 class="mb-0">{{ $folio->folio_name }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-{{ $folio->status === 'open' ? 'success' : 'secondary' }} text-white">
                <div class="card-body">
                    <h6>Status</h6>
                    <h5 class="mb-0">{{ ucfirst($folio->status) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>Items</h6>
                    <h5 class="mb-0">{{ $folio->items->count() }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6>Balance</h6>
                    <h5 class="mb-0">{{ number_format($folio->balance, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Folio Items</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folio->items as $item)
                    <tr>
                        <td><input type="checkbox" class="item-checkbox" value="{{ $item->id }}" form="splitForm"></td>
                        <td>{{ $item->post_date->format('M d, Y') }}</td>
                        <td>{{ ucfirst($item->charge_type) }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->amount + $item->tax_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No items on this folio.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($folio->status === 'open')
<form id="splitForm" action="{{ route('frontdesk.folios.split', $folio) }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="folio_name" value="Split Folio">
</form>

<div class="modal fade" id="postChargeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('frontdesk.folios.post-charge', $folio) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post Charge to Folio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Charge Type <span class="text-danger">*</span></label>
                        <select name="charge_type" class="form-select" required>
                            <option value="room">Room</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="restaurant">Restaurant</option>
                            <option value="service">Service</option>
                            <option value="extension">Extension</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required min="0">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Code</label>
                            <input type="text" name="tax_code" class="form-control" placeholder="e.g. VAT">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control" min="0" max="100" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Post Date</label>
                        <input type="date" name="post_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Post Charge</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="splitFolioModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('frontdesk.folios.split', $folio) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Split Folio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Folio Name <span class="text-danger">*</span></label>
                        <input type="text" name="folio_name" class="form-control" required placeholder="e.g. Incidentals">
                    </div>
                    <p class="text-muted small mb-0">Select items to move to the new folio using the checkboxes in the table above.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Split Folio</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSelectAll(source) {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = source.checked);
}
</script>
@endif
@endsection
