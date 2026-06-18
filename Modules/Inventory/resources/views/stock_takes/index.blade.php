@extends('layouts.master')

@section('title', 'Stock Takes')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Stock Takes</h1>
            <p class="text-muted mb-0">Physical inventory counts to reconcile actual vs expected stock.</p>
        </div>
        <a href="{{ route('inventory.stock-takes.create') }}" class="btn btn-primary">
            <i class="fas fa-clipboard-list me-2"></i>New Stock Take
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Store</th>
                        <th>Status</th>
                        <th>Items Counted</th>
                        <th>Taken By</th>
                        <th>Taken At</th>
                        <th>Completed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockTakes as $st)
                        <tr>
                            <td>{{ $st->id }}</td>
                            <td>{{ $st->store->name }}</td>
                            <td>
                                @if ($st->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif ($st->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                @elseif ($st->status === 'completed')
                                    <span class="badge bg-info">Completed</span>
                                @elseif ($st->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                            <td>{{ $st->items->count() }}</td>
                            <td>{{ $st->taker->name ?? 'N/A' }}</td>
                            <td>{{ $st->taken_at ? $st->taken_at->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td>{{ $st->completed_at ? $st->completed_at->format('Y-m-d H:i') : '—' }}</td>
                            <td>
                                <a href="{{ route('inventory.stock-takes.show', $st) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('inventory.stock-takes.mobile', $st) }}" class="btn btn-sm btn-outline-info" title="Mobile Count"><i class="fas fa-mobile-alt"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 d-block"></i>
                                No stock takes yet. Start your first physical count.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
