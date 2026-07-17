@extends('layouts.master')

@section('title', 'Edit PR ' . $purchaseRequest->pr_number)

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h1 class="display-5 text-dark mb-0">Edit: {{ $purchaseRequest->pr_number }}</h1>
            <span class="badge bg-info text-dark fs-6">Flagged / Draft</span>
        </div>
        <a href="{{ route('inventory.procurement.requests.show', $purchaseRequest) }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('inventory.procurement.requests.update', $purchaseRequest) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" class="form-control" value="{{ old('department', $purchaseRequest->department) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Urgency</label>
                                <select name="urgency" class="form-select" required>
                                    <option value="normal" {{ ($purchaseRequest->urgency ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ ($purchaseRequest->urgency ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="emergency" {{ ($purchaseRequest->urgency ?? '') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Justification / Purpose</label>
                                <textarea name="justification" class="form-control" rows="4" required>{{ old('justification', $purchaseRequest->justification) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="fas fa-plus me-1"></i>Add Item
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="itemsContainer">
                            @foreach($purchaseRequest->items as $i => $item)
                            <div class="item-row border rounded p-3 mb-3 bg-light">
                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Item Name</label>
                                        <input type="text" name="items[{{ $i }}][item_name]" class="form-control" value="{{ $item->item_name }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Quantity</label>
                                        <input type="number" name="items[{{ $i }}][quantity]" class="form-control" step="0.01" min="0.01" value="{{ $item->quantity }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Est. Unit Price (₦)</label>
                                        <input type="number" name="items[{{ $i }}][estimated_unit_price]" class="form-control" step="0.01" min="0" value="{{ $item->estimated_unit_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="items[{{ $i }}][notes]" class="form-control" value="{{ $item->notes }}">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-item">
                                    <i class="fas fa-trash me-1"></i>Remove
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-save me-2"></i>Update</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">After updating, you can submit the request again.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let nextIdx = {{ $purchaseRequest->items->count() }};

    document.getElementById('addItemBtn')?.addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const template = document.querySelector('.item-row').cloneNode(true);
        template.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name').replace(/\[\d+\]/, `[${nextIdx}]`);
            input.setAttribute('name', name);
            if (input.type !== 'hidden') input.value = '';
        });
        template.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', () => template.remove());
        });
        container.appendChild(template);
        nextIdx++;
    });

    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                this.closest('.item-row').remove();
            }
        });
    });
</script>
@endsection
