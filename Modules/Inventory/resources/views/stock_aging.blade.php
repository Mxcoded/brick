@extends('layouts.master')

@section('title', 'Stock Aging / Expiry Report')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Stock Aging & Expiry</h1>
            <p class="text-muted mb-0">Monitor items approaching or past their expiry dates.</p>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="store_id" class="form-select">
                <option value="">All Stores</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
            <a href="{{ route('inventory.stock-aging') }}" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Clear</a>
        </div>
    </form>

    @php
        $groups = [
            'expired' => ['label' => 'Expired', 'icon' => 'fa-times-circle', 'class' => 'table-danger', 'badge' => 'bg-danger', 'items' => $expired],
            'expiring30' => ['label' => 'Expiring within 30 days', 'icon' => 'fa-exclamation-triangle', 'class' => 'table-warning', 'badge' => 'bg-warning text-dark', 'items' => $expiring30],
            'expiring60' => ['label' => 'Expiring 31–60 days', 'icon' => 'fa-clock', 'class' => 'table-info', 'badge' => 'bg-info', 'items' => $expiring60],
            'expiring90' => ['label' => 'Expiring 61–90 days', 'icon' => 'fa-clock', 'class' => '', 'badge' => 'bg-secondary', 'items' => $expiring90],
            'farFuture' => ['label' => 'More than 90 days', 'icon' => 'fa-check-circle', 'class' => '', 'badge' => 'bg-success', 'items' => $farFuture],
        ];
    @endphp

    @foreach ($groups as $key => $group)
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas {{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
                    <span class="badge {{ $group['badge'] }} ms-2">{{ $group['items']->count() }} lots</span>
                </h5>
                <span class="text-muted small">{{ $group['items']->sum('quantity') }} total units</span>
            </div>
            @if ($group['items']->isNotEmpty())
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0 {{ $group['class'] }}">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Store</th>
                                <th>Lot #</th>
                                <th class="text-center">Qty</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['items'] as $si)
                                @php
                                    $days = $now = now()->startOfDay();
                                    $daysLeft = $now->diffInDays($si->expiry_date, false);
                                @endphp
                                <tr>
                                    <td>{{ $si->item->description }}</td>
                                    <td>{{ $si->store->name }}</td>
                                    <td><code>{{ $si->lot_number }}</code></td>
                                    <td class="text-center">{{ number_format($si->quantity) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($si->expiry_date)->format('Y-m-d') }}</td>
                                    <td>
                                        @if ($daysLeft < 0)
                                            <span class="badge bg-danger">{{ abs($daysLeft) }} days ago</span>
                                        @else
                                            {{ $daysLeft }} days
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body text-center text-muted py-3">
                    <i class="fas fa-check-circle me-1"></i> No items in this category.
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection
