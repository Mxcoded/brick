@extends('layouts.master')

@section('title', 'Item Returns')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Item Returns</h1>
            <p class="text-muted mb-0">Track items returned from departments back to stores.</p>
        </div>
        <a href="{{ route('inventory.returns.create') }}" class="btn btn-primary">
            <i class="fas fa-undo me-2"></i>Record Return
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" id="returnsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reference</th>
                        <th>Item</th>
                        <th>Store</th>
                        <th>Department</th>
                        <th>Qty</th>
                        <th>Returned By</th>
                        <th>Received By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td><code>{{ $r->reference ?? '—' }}</code></td>
                            <td>{{ $r->item->description ?? 'N/A' }}</td>
                            <td>{{ $r->store->name ?? 'N/A' }}</td>
                            <td>{{ $r->department->name ?? '—' }}</td>
                            <td>{{ $r->quantity_returned }}</td>
                            <td>{{ $r->returned_by ?? '—' }}</td>
                            <td>{{ $r->received_by ?? '—' }}</td>
                            <td>{{ $r->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No returns recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $returns->links() }}</div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>$(document).ready(function() { $('#returnsTable').DataTable({ pageLength: 25, order: [[0, 'desc']] }); });</script>
@endsection
