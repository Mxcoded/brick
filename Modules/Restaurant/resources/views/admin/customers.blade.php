@extends('restaurant::layouts.adminMaster')
@section('title', 'Customers')
@section('admin-content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Customers</h4>
        <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#customerModal">
            <i class="bi bi-plus-lg me-1"></i>Add Customer
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Name</th>
                            <th class="py-3">Phone</th>
                            <th class="py-3">Email</th>
                            <th class="py-3 text-end">Visits</th>
                            <th class="py-3 text-end">Total Spent</th>
                            <th class="py-3 text-end">Loyalty Points</th>
                            <th class="py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="fw-medium">{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td class="text-end">{{ $customer->visit_count }}</td>
                            <td class="text-end fw-bold">₦{{ number_format($customer->total_spent, 2) }}</td>
                            <td class="text-end"><span class="badge bg-warning text-dark">{{ $customer->loyalty_points }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('restaurant.admin.customer.show', $customer) }}" class="btn btn-outline-primary btn-sm">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No customers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">{{ $customers->links() }}</div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('restaurant.admin.customer.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h6 class="modal-title">Add Customer</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label small fw-medium">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label small fw-medium">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col"><label class="form-label small fw-medium">Email</label><input type="email" name="email" class="form-control"></div>
                    </div>
                    <div><label class="form-label small fw-medium">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endSection
