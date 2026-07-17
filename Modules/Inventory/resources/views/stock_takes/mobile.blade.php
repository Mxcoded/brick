@extends('layouts.master')

@section('title', "Stock Take - $stockTake->id (Mobile)")

@section('page-content')
<div class="container-fluid p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Stock Take #{{ $stockTake->id }}</h4>
            <small class="text-muted">{{ $stockTake->store->name ?? 'N/A' }} &middot; {{ $stockTake->status }}</small>
        </div>
        <div>
            <span class="badge bg-{{ $stockTake->status === 'approved' ? 'success' : ($stockTake->status === 'in_progress' ? 'warning text-dark' : 'secondary') }} fs-6">
                {{ str_replace('_', ' ', ucfirst($stockTake->status)) }}
            </span>
        </div>
    </div>

    <div class="d-grid gap-2 mb-3">
        <input type="search" id="searchInput" class="form-control form-control-lg" placeholder="Search items...">
    </div>

    <div id="itemsContainer">
        @php
            $nonZero = $stockTake->items->filter(fn($i) => $i->counted_qty !== null);
            $pending = $stockTake->items->filter(fn($i) => $i->counted_qty === null);
        @endphp

        @if ($pending->count() > 0)
            <h6 class="text-muted mb-2">Pending Count ({{ $pending->count() }})</h6>
            @foreach ($pending as $item)
                <div class="card shadow-sm border-0 mb-2 searchable-item" data-name="{{ strtolower($item->item->description ?? '') }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>{{ $item->item->description ?? 'N/A' }}</strong>
                                <div class="small text-muted">
                                    SKU: {{ $item->item->sku ?? 'N/A' }} &middot; Current: <strong>{{ $item->current_qty }}</strong>
                                </div>
                            </div>
                            <div class="ms-3" style="min-width: 90px;">
                                <input type="number" class="form-control form-control-sm counted-input" data-id="{{ $item->id }}" placeholder="Count" min="0" style="font-size: 1.1rem; height: 38px;">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if ($nonZero->count() > 0)
            <h6 class="text-muted mb-2 mt-3">Counted ({{ $nonZero->count() }})</h6>
            @foreach ($nonZero as $item)
                <div class="card shadow-sm border-0 mb-2 searchable-item" data-name="{{ strtolower($item->item->description ?? '') }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>{{ $item->item->description ?? 'N/A' }}</strong>
                                <div class="small text-muted">
                                    SKU: {{ $item->item->sku ?? 'N/A' }} &middot; Current: <strong>{{ $item->current_qty }}</strong>
                                </div>
                            </div>
                            <div class="ms-3" style="min-width: 90px;">
                                <input type="number" class="form-control form-control-sm counted-input" data-id="{{ $item->id }}" value="{{ $item->counted_qty }}" min="0" style="font-size: 1.1rem; height: 38px;">
                            </div>
                        </div>
                        @if ($item->counted_qty != $item->current_qty)
                            <small class="text-danger mt-1 d-block">
                                <i class="fas fa-exclamation-circle me-1"></i>Variance: {{ $item->counted_qty - $item->current_qty }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @if ($stockTake->status === 'in_progress')
        <div class="mt-3">
            <button id="completeCountBtn" class="btn btn-success btn-lg w-100">
                <i class="fas fa-check me-2"></i>Complete Count
            </button>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    let timeout;

    $('#searchInput').on('keyup', function() {
        const q = $(this).val().toLowerCase();
        $('.searchable-item').each(function() {
            $(this).toggle($(this).data('name').includes(q));
        });
    });

    $('.counted-input').on('input', function() {
        clearTimeout(timeout);
        const input = $(this);
        timeout = setTimeout(function() {
            $.post('{{ route("inventory.stock-takes.update-item", $stockTake) }}', {
                _token: '{{ csrf_token() }}',
                item_id: input.data('id'),
                counted_qty: input.val() || 0,
            });
        }, 600);
    });

    $('#completeCountBtn').on('click', function() {
        if (confirm('Mark this stock take as complete? This cannot be undone.')) {
            $.post('{{ route("inventory.stock-takes.complete", $stockTake) }}', {
                _token: '{{ csrf_token() }}',
            }).then(function() { window.location.reload(); });
        }
    });
});
</script>
@endsection
