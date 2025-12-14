@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active">Order #{{ $order->order_id }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    
    {{-- 1. HEADER & ACTIONS --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-charcoal mb-0">
                Order #{{ $order->order_id }}
                @php
                    $statusColor = ['Pending'=>'warning', 'Confirmed'=>'primary', 'Completed'=>'success', 'Cancelled'=>'danger'][$order->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $statusColor }} fs-6 align-middle ms-2">{{ $order->status }}</span>
            </h1>
            <p class="text-muted mb-0">Created on {{ $order->created_at->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-charcoal">Back</a>
            
            {{-- FIX: Updated permission to use underscore --}}
            @can('manage_banquet')
                <a href="{{ route('banquet.orders.edit', $order->order_id) }}" class="btn btn-outline-gold"><i class="fas fa-edit me-1"></i> Edit Details</a>
                <a href="{{ route('banquet.orders.pdf', $order->order_id) }}" target="_blank" class="btn btn-gold"><i class="fas fa-file-pdf me-1"></i> Function Sheet</a>
            @endcan
        </div>
    </div>

    {{-- 2. FINANCIAL HIGHLIGHTS STRIP --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-4 p-4 border-end">
                    <small class="text-uppercase text-muted fw-bold">Total Revenue</small>
                    <h2 class="text-gold fw-bold mb-0">₦{{ number_format($order->total_revenue, 2) }}</h2>
                    <small class="text-muted">Includes Hall Fees & Menu</small>
                </div>
                <div class="col-md-4 p-4 border-end">
                    <small class="text-uppercase text-muted fw-bold">Total Expenses</small>
                    <h2 class="text-danger fw-bold mb-0">₦{{ number_format($order->expenses, 2) }}</h2>
                    <small class="text-muted">Operational Costs</small>
                </div>
                <div class="col-md-4 p-4 bg-light">
                    <small class="text-uppercase text-muted fw-bold">Net Profit Margin</small>
                    @php 
                        $margin = $order->profit_margin;
                        $mColor = $margin > 20 ? 'success' : ($margin > 0 ? 'warning' : 'danger');
                    @endphp
                    <h2 class="text-{{ $mColor }} fw-bold mb-0">
                        {{ $margin !== null ? number_format($margin, 1).'%' : 'N/A' }}
                    </h2>
                    <small class="text-muted">Target: >20%</small>
                </div>
            </div>
        </div>
    </div>
{{-- 3. PAYMENT & INVOICE ACTIONS --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-charcoal">
                            Payment Status: 
                            <span class="badge {{ $order->balance_due <= 0 ? 'bg-success' : ($order->paid_amount > 0 ? 'bg-warning' : 'bg-danger') }}">
                                {{ $order->payment_status }}
                            </span>
                        </h5>
                        <small class="text-muted">
                            Paid: <span class="text-success fw-bold">₦{{ number_format($order->paid_amount) }}</span> 
                            &bull; 
                            Balance: <span class="text-danger fw-bold">₦{{ number_format($order->balance_due) }}</span>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('banquet.orders.invoice', $order->order_id) }}" target="_blank" class="btn btn-outline-charcoal">
                            <i class="fas fa-file-invoice me-2"></i>Download Invoice
                        </a>
                        @can('manage_banquet')
                            @if($order->balance_due > 0)
                                <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. PAYMENT HISTORY TABLE (New Section) --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3 text-gold">Payment History</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end pe-3">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->payments as $payment)
                        <tr>
                            <td class="ps-3">{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td><small class="text-muted">{{ $payment->reference ?? '-' }}</small></td>
                            <td class="text-end fw-bold text-success pe-3">₦{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-end">
                                @can('manage_banquet')
                                    <form action="{{ route('banquet.orders.payment.destroy', [$order->order_id, $payment->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete this payment record?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAYMENT MODAL --}}
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gold text-white">
                    <h5 class="modal-title">Record New Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('banquet.orders.payment.store', $order->order_id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Amount (₦)</label>
                            <input type="number" name="amount" class="form-control form-control-lg fw-bold" 
                                   max="{{ $order->balance_due }}" value="{{ $order->balance_due }}" required>
                            <div class="form-text">Max due: ₦{{ number_format($order->balance_due) }}</div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted">Date</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Transfer">Bank Transfer</option>
                                    <option value="POS">POS Terminal</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Reference / Receipt No</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. TRF-12345678">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gold">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row g-4">
        {{-- 3. LEFT COLUMN: CLIENT INFO --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3 text-gold">Client Details</div>
                <div class="card-body text-charcoal">
                    <div class="mb-3">
                        <label class="small text-muted">Organization</label>
                        <div class="fw-bold">{{ $order->customer->organization ?? 'Private Event' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Primary Contact</label>
                        <div class="fw-bold">{{ $order->contact_person_name }}</div>
                        <div><a href="tel:{{ $order->contact_person_phone }}" class="text-gold text-decoration-none">{{ $order->contact_person_phone }}</a></div>
                        <div><a href="mailto:{{ $order->contact_person_email }}" class="text-gold text-decoration-none">{{ $order->contact_person_email }}</a></div>
                    </div>
                    @if($order->contact_person_name_ii)
                    <div class="border-top pt-3">
                        <label class="small text-muted">Secondary Contact</label>
                        <div class="fw-bold">{{ $order->contact_person_name_ii }}</div>
                        <div>{{ $order->contact_person_phone_ii }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3 text-gold">Internal Info</div>
                <div class="card-body text-charcoal">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Referred By</span>
                            <span class="fw-bold">{{ $order->referred_by ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Department</span>
                            <span class="fw-bold">{{ $order->department ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Hall Fee</span>
                            <span class="fw-bold">₦{{ number_format($order->hall_rental_fees) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- 4. RIGHT COLUMN: EVENT SCHEDULE --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-gold">Event Schedule</h5>
                    
                    {{-- FIX: Updated permission to use underscore --}}
                    @can('manage_banquet')
                        <a href="{{ route('banquet.orders.add-day', $order->order_id) }}" class="btn btn-sm btn-gold">
                            <i class="fas fa-plus me-1"></i> Add Day
                        </a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($order->eventDays->isEmpty())
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                            <p>No event days added yet.</p>
                        </div>
                    @else
                        <div class="accordion accordion-flush" id="eventAccordion">
                            @foreach ($order->eventDays as $index => $day)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#day{{ $day->id }}">
                                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                                <div>
                                                    <span class="fw-bold text-gold me-2">{{ $day->event_date->format('M d, Y') }}</span>
                                                    <span class="badge bg-light text-dark border">{{ $day->event_type }}</span>
                                                </div>
                                                <div class="small text-muted">
                                                    {{ $day->start_time }} - {{ $day->end_time }} ({{ $day->room }})
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="day{{ $day->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#eventAccordion">
                                        <div class="accordion-body bg-light">
                                            
                                            {{-- Day Details --}}
                                            <div class="row mb-3 text-charcoal">
                                                <div class="col-md-3"><strong>Setup:</strong> {{ $day->setup_style }}</div>
                                                <div class="col-md-3"><strong>Guests:</strong> {{ $day->guest_count }}</div>
                                                <div class="col-md-6 text-end">
                                                    
                                                    {{-- FIX: Updated permission to use underscore --}}
                                                    @can('manage_banquet')
                                                        {{-- Route: banquet.orders.event-days.edit (legacy route support from web.php) --}}
                                                        <a href="{{ route('banquet.orders.event-days.edit', [$order->order_id, $day->id]) }}" class="btn btn-xs btn-link text-decoration-none text-gold">Edit Details</a>
                                                        
                                                        {{-- Route: banquet.orders.event-days.destroy --}}
                                                        <form action="{{ route('banquet.orders.event-days.destroy', [$order->order_id, $day->id]) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-xs btn-link text-danger text-decoration-none" onclick="return confirm('Delete this day?')">Remove</button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>

                                            {{-- Menu Items Table --}}
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between align-items-center py-2 bg-white">
                                                    <small class="fw-bold text-uppercase text-charcoal">Menu Selections</small>
                                                    @can('manage_banquet')
                                                        <a href="{{ route('banquet.orders.add-menu-item', [$order->order_id, $day->id]) }}" class="btn btn-sm btn-outline-gold">
                                                            <i class="fas fa-utensils me-1"></i> Add Menu
                                                        </a>
                                                    @endcan
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm mb-0 align-middle">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Type</th>
                                                                <th>Details</th>
                                                                <th class="text-end">Qty</th>
                                                                <th class="text-end">Total</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($day->menuItems as $item)
                                                                <tr>
                                                                    <td><span class="badge bg-secondary">{{ ucfirst($item->meal_type) }}</span></td>
                                                                    <td class="text-charcoal">
                                                                        <small class="d-block text-truncate" style="max-width: 250px;">
                                                                            {{ implode(', ', json_decode($item->menu_items, true) ?? []) }}
                                                                        </small>
                                                                    </td>
                                                                    <td class="text-end text-charcoal">{{ $item->quantity }}</td>
                                                                    <td class="text-end fw-bold text-gold">₦{{ number_format($item->total_price) }}</td>
                                                                    <td class="text-end">
                                                                        
                                                                        {{-- FIX: Updated permission to use underscore --}}
                                                                        @can('manage_banquet')
                                                                            <a href="{{ route('banquet.orders.edit-menu-item', [$order->order_id, $day->id, $item->id]) }}" class="text-warning me-2"><i class="fas fa-pencil-alt"></i></a>
                                                                            <form action="{{ route('banquet.orders.menu-item.destroy', [$order->order_id, $day->id, $item->id]) }}" method="POST" class="d-inline">
                                                                                @csrf @method('DELETE')
                                                                                <button class="btn btn-link p-0 text-danger" onclick="return confirm('Remove item?')"><i class="fas fa-times"></i></button>
                                                                            </form>
                                                                        @endcan
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="5" class="text-center text-muted small py-3">No menu items added.</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .banquet-theme {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        color: #333333;
    }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .btn-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #FFFFFF;
    }
    .btn-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #FFFFFF;
    }
    .btn-outline-gold {
        color: #C8A165;
        border-color: #C8A165;
    }
    .btn-outline-gold:hover {
        background-color: #C8A165;
        color: #FFFFFF;
    }
    .btn-outline-charcoal {
        color: #333333;
        border-color: #333333;
    }
    .btn-outline-charcoal:hover {
        background-color: #333333;
        color: #FFFFFF;
    }
    .bg-light { background-color: #f9f8f6 !important; }
</style>
@endsection