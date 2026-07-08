@extends('restaurant::layouts.adminMaster')
@section('title', $customer->name)
@section('admin-content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('restaurant.admin.customers') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i>Back to Customers
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 text-center p-4">
                <div class="fs-1 mb-2">👤</div>
                <h5 class="fw-bold">{{ $customer->name }}</h5>
                <div class="text-muted small">{{ $customer->phone ?? 'No phone' }} · {{ $customer->email ?? 'No email' }}</div>
                @if($customer->notes)
                    <div class="mt-2 small text-muted">{{ $customer->notes }}</div>
                @endif
            </div>
        </div>
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-4">
                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                        <div class="text-muted small">Visits</div>
                        <div class="fs-3 fw-bold">{{ $customer->visit_count }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                        <div class="text-muted small">Total Spent</div>
                        <div class="fs-3 fw-bold">₦{{ number_format($customer->total_spent, 2) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                        <div class="text-muted small">Loyalty Points</div>
                        <div class="fs-3 fw-bold text-warning">{{ $customer->loyalty_points }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light py-3">
            <h6 class="fw-bold mb-0">Order History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Type</th>
                            <th class="py-3 text-end">Items</th>
                            <th class="py-3 text-end">Total</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>{{ ucfirst($order->type) }}</td>
                            <td class="text-end">{{ $order->orderItems->sum('quantity') }}</td>
                            <td class="text-end fw-bold">₦{{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endSection
