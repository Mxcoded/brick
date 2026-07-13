@extends('layouts.master')

@section('title', 'New Purchase Request')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">New Purchase Request</h1>
            <p class="text-muted mb-0">Submit a procurement request for review</p>
        </div>
        <a href="{{ route('inventory.procurement.dashboard') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form method="POST" action="{{ route('inventory.procurement.requests.store') }}" id="prForm">
        @csrf
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
                                <input type="text" name="department" class="form-control" placeholder="e.g. Kitchen, Housekeeping" value="{{ old('department') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Urgency</label>
                                <select name="urgency" class="form-select" required>
                                    <option value="normal" {{ old('urgency') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Justification / Purpose</label>
                                <textarea name="justification" class="form-control" rows="4" placeholder="Explain why these items are needed..." required>{{ old('justification') }}</textarea>
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
                            <div class="item-row border rounded p-3 mb-3 bg-light">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Item Name</label>
                                        <input type="text" name="items[0][item_name]" class="form-control" placeholder="Item name" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control" step="0.01" min="0.01" placeholder="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Est. Unit Price (₦)</label>
                                        <input type="number" name="items[0][estimated_unit_price]" class="form-control" step="0.01" min="0" placeholder="Optional">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="items[0][notes]" class="form-control" placeholder="Optional">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-item" style="display:none;">
                                    <i class="fas fa-trash me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2"></i>Submit</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">Your request will first go to the Purchaser for review after submission.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save as Draft
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="submitAndSend">
                                <i class="fas fa-paper-plane me-2"></i>Submit for Review
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
    let itemIndex = 1;

    document.getElementById('addItemBtn')?.addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const template = document.querySelector('.item-row').cloneNode(true);
        template.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name').replace(/\[\d+\]/, `[${itemIndex}]`);
            input.setAttribute('name', name);
            input.value = '';
            input.removeAttribute('required');
        });
        const btn = template.querySelector('.remove-item');
        btn.style.display = 'inline-block';
        btn.addEventListener('click', () => template.remove());
        container.appendChild(template);
        itemIndex++;
    });

    document.querySelector('.remove-item')?.addEventListener('click', function() {
        if (document.querySelectorAll('.item-row').length > 1) {
            this.closest('.item-row').remove();
        }
    });

    document.getElementById('submitAndSend')?.addEventListener('click', function() {
        const form = document.getElementById('prForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'submit_and_send';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    });
</script>
@endsection
