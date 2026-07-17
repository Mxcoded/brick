@extends('layouts.master')

@section('title', 'Stock Alerts')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Stock Alerts</h1>
            <p class="text-muted mb-0">Unresolved low stock, expiring, and expired item notifications.</p>
        </div>
        <form action="{{ route('inventory.alerts.resolve-all') }}" method="POST">
            @csrf
            <button class="btn btn-success" onclick="return confirm('Resolve all alerts?')">
                <i class="fas fa-check-double me-2"></i>Resolve All
            </button>
        </form>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0">{{ $alerts->where('severity', 'warning')->count() }}</h3>
                    <small class="text-muted">Low Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0">{{ $alerts->where('severity', 'danger')->count() }}</h3>
                    <small class="text-muted">Expired</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0">{{ $alerts->where('severity', 'info')->count() }}</h3>
                    <small class="text-muted">Expiring Soon</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0">{{ $resolvedCount }}</h3>
                    <small class="text-muted">Resolved</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Item</th>
                        <th>Store</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alerts as $alert)
                        <tr class="{{ $alert->severity === 'danger' ? 'table-danger' : ($alert->severity === 'warning' ? 'table-warning' : 'table-info') }}">
                            <td>{{ $alert->id }}</td>
                            <td><span class="badge bg-{{ $alert->severity === 'danger' ? 'danger' : ($alert->severity === 'warning' ? 'warning text-dark' : 'info') }}">{{ str_replace('_', ' ', ucfirst($alert->type)) }}</span></td>
                            <td>{{ $alert->message }}</td>
                            <td>{{ $alert->item->description ?? 'N/A' }}</td>
                            <td>{{ $alert->store->name ?? '—' }}</td>
                            <td>{{ $alert->created_at->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('inventory.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-check-circle fa-2x mb-2 d-block"></i>No unresolved alerts. Everything looks good!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $alerts->links() }}</div>
</div>
@endsection
