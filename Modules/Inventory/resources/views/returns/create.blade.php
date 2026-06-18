@extends('layouts.master')

@section('title', 'Record Item Return')

@section('page-content')
<div class="container-fluid p-4">
    <div class="mb-4">
        <h1 class="display-5 text-dark">Record Item Return</h1>
        <p class="text-muted mb-0">Return unused or surplus items from a department back to a store.</p>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('inventory.returns.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="item_id" class="form-select" required>
                            <option value="">Select item</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->description }} ({{ $item->sku ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Store <span class="text-danger">*</span></label>
                        <select name="store_id" id="storeSelect" class="form-select" required>
                            <option value="">Select store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select name="department_id" id="departmentSelect" class="form-select">
                            <option value="">—</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantity Returned <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_returned" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="e.g., RET-001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Returned By</label>
                        <input type="text" name="returned_by" class="form-control" value="{{ old('returned_by', auth()->user()->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Received By</label>
                        <input type="text" name="received_by" class="form-control" value="{{ old('received_by') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g., Over-issued, unused surplus, wrong item">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-undo me-2"></i>Record Return</button>
                    <a href="{{ route('inventory.returns.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#storeSelect').on('change', function() {
    const storeId = $(this).val();
    const deptSelect = $('#departmentSelect');
    deptSelect.html('<option value="">Loading...</option>');
    if (!storeId) { deptSelect.html('<option value="">—</option>'); return; }
    $.get('/inventory/api/stores/' + storeId + '/departments', function(data) {
        deptSelect.html('<option value="">—</option>');
        data.forEach(function(d) { deptSelect.append('<option value="' + d.id + '">' + d.name + '</option>'); });
    });
});
</script>
@endsection
