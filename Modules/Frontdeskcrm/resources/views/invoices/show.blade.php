@extends('layouts.master')

@section('title', "Invoice {$invoice->invoice_number}")
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Invoice: {{ $invoice->invoice_number }}</h4>
        <div>
            <a href="{{ route('frontdesk.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>All Invoices
            </a>
            @if($invoice->status === 'draft')
            <form action="{{ route('frontdesk.invoices.issue', $invoice) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-success ms-1"><i class="fas fa-check me-1"></i>Issue</button>
            </form>
            <button class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#voidInvoiceModal">
                <i class="fas fa-ban me-1"></i>Void
            </button>
            @endif
            @if(in_array($invoice->status, ['issued', 'paid']))
            <button class="btn btn-sm btn-outline-warning ms-1" data-bs-toggle="modal" data-bs-target="#creditNoteModal">
                <i class="fas fa-undo me-1"></i>Credit Note
            </button>
            <a href="{{ route('frontdesk.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary ms-1">
                <i class="fas fa-file-pdf me-1"></i>Download PDF
            </a>
            <button class="btn btn-sm btn-outline-info ms-1" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
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
                    <h6>Subtotal</h6>
                    <h4 class="mb-0">{{ number_format($invoice->subtotal, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>Tax Total</h6>
                    <h4 class="mb-0">{{ number_format($invoice->tax_total, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6>Total</h6>
                    <h4 class="mb-0">{{ number_format($invoice->total, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6>Paid / Balance</h6>
                    <h4 class="mb-0">{{ number_format($invoice->paid_amount, 2) }} / {{ number_format($invoice->total - $invoice->paid_amount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Invoice Items</h5>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoice->creditNotes->count())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Credit Notes</h5>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Credit Note #</th>
                        <th>Date</th>
                        <th>Reason</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->creditNotes as $cn)
                    <tr>
                        <td>{{ $cn->credit_note_number }}</td>
                        <td>{{ $cn->issue_date->format('M d, Y') }}</td>
                        <td>{{ $cn->reason }}</td>
                        <td class="text-end text-danger">{{ number_format($cn->amount, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('frontdesk.invoices.credit-note.pdf', $cn) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($invoice->receipts->count())
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Receipts</h5>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Prints</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->receipts as $rct)
                    <tr>
                        <td>{{ $rct->receipt_number }}</td>
                        <td>{{ $rct->receipted_at->format('M d, Y H:i') }}</td>
                        <td>{{ $rct->payment_method }}</td>
                        <td class="text-end">{{ number_format($rct->amount, 2) }}</td>
                        <td class="text-center">{{ $rct->print_count }}</td>
                        <td class="text-center">
                            <a href="{{ route('frontdesk.invoices.receipt.pdf', $rct) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@if($invoice->status === 'draft')
<div class="modal fade" id="voidInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('frontdesk.invoices.void', $invoice) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Void Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Void Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@if(in_array($invoice->status, ['issued', 'paid']))
<div class="modal fade" id="creditNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('frontdesk.invoices.credit-note', $invoice) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Credit Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required min="0.01" max="{{ $invoice->total }}">
                        <small class="text-muted">Max: {{ number_format($invoice->total, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Create Credit Note</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
