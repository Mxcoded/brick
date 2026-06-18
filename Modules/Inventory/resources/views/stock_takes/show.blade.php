@extends('layouts.master')

@section('title', 'Stock Take #'.$stockTake->id)

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">
                Stock Take #{{ $stockTake->id }}
                @if ($stockTake->status === 'in_progress')
                    <span class="badge bg-warning text-dark fs-6 align-middle">In Progress</span>
                @elseif ($stockTake->status === 'completed')
                    <span class="badge bg-info fs-6 align-middle">Completed</span>
                @elseif ($stockTake->status === 'approved')
                    <span class="badge bg-success fs-6 align-middle">Approved</span>
                @endif
            </h1>
            <p class="text-muted mb-0">{{ $stockTake->store->name }} — Started by {{ $stockTake->taker->name ?? 'N/A' }} on {{ $stockTake->taken_at->format('Y-m-d H:i') }}</p>
        </div>
        <div>
            @if ($stockTake->status === 'in_progress')
                <form method="POST" action="{{ route('inventory.stock-takes.complete', $stockTake) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Mark this stock take as complete? Variances will be calculated.')">
                        <i class="fas fa-check me-2"></i>Complete Count
                    </button>
                </form>
            @elseif ($stockTake->status === 'completed')
                <form method="POST" action="{{ route('inventory.stock-takes.approve', $stockTake) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Apply all variances as inventory adjustments? This cannot be undone.')">
                        <i class="fas fa-check-double me-2"></i>Approve & Apply
                    </button>
                </form>
            @endif
            <a href="{{ route('inventory.stock-takes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    @if ($stockTake->notes)
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>{{ $stockTake->notes }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Items Counted</div>
                    <div class="h3 mb-0">{{ $stockTake->items->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">With Variance</div>
                    <div class="h3 mb-0">{{ $stockTake->items->where('variance', '!=', 0)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Variance (Qty)</div>
                    <div class="h3 mb-0 {{ $stockTake->items->sum('variance') < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $stockTake->items->sum('variance') > 0 ? '+' : '' }}{{ number_format($stockTake->items->sum('variance')) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Expected Total</div>
                    <div class="h3 mb-0">{{ number_format($stockTake->items->sum('expected_quantity')) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Count Items</h5>
            @if ($stockTake->status === 'in_progress')
                <span class="text-muted small">Click the actual quantity value to edit</span>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>SKU</th>
                        <th class="text-center">Expected</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Variance</th>
                        <th class="text-center">Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockTake->items as $item)
                        @php
                            $variance = $item->actual_quantity - $item->expected_quantity;
                        @endphp
                        <tr class="{{ $variance != 0 ? ($stockTake->status === 'approved' ? '' : 'table-warning') : '' }}">
                            <td>{{ $item->item->description }}</td>
                            <td><code>{{ $item->item->sku ?? '—' }}</code></td>
                            <td class="text-center">{{ number_format($item->expected_quantity) }}</td>
                            <td class="text-center">
                                @if ($stockTake->status === 'in_progress')
                                    <input type="number" class="form-control form-control-sm text-center actual-qty-input"
                                           style="width: 100px; display: inline;"
                                           value="{{ $item->actual_quantity }}" min="0"
                                           data-url="{{ route('inventory.stock-takes.update-item', $stockTake) }}"
                                           data-item-id="{{ $item->item_id }}">
                                @else
                                    {{ number_format($item->actual_quantity) }}
                                @endif
                            </td>
                            <td class="text-center fw-bold {{ $variance > 0 ? 'text-success' : ($variance < 0 ? 'text-danger' : '') }}">
                                {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }}
                            </td>
                            <td class="text-center">
                                @if ($variance == 0)
                                    <span class="badge bg-success">Match</span>
                                @else
                                    <span class="badge bg-warning text-dark">Variance</span>
                                @endif
                            </td>
                            <td>
                                @if ($stockTake->status === 'in_progress')
                                    <input type="text" class="form-control form-control-sm notes-input"
                                           value="{{ $item->notes }}"
                                           placeholder="Optional note"
                                           data-url="{{ route('inventory.stock-takes.update-item', $stockTake) }}"
                                           data-item-id="{{ $item->item_id }}">
                                @else
                                    {{ $item->notes ?? '—' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.actual-qty-input, .notes-input').on('change', function () {
            const input = $(this);
            const url = input.data('url');
            const itemId = input.data('item-id');
            const isQty = input.hasClass('actual-qty-input');
            const field = isQty ? 'actual_quantity' : 'notes';
            const value = input.val();

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    _token: '{{ csrf_token() }}',
                    item_id: itemId,
                    [field]: value,
                },
                dataType: 'json',
                success: function (res) {
                    if (isQty) {
                        const row = input.closest('tr');
                        row.find('.fw-bold').text(res.variance > 0 ? '+' + res.variance : res.variance);
                    }
                },
            });
        });
    });
</script>
@endsection
