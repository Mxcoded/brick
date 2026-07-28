@extends('layouts.master')

@section('title', 'Edit City Ledger Account')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Edit: {{ $account->name }}</h4>
        <a href="{{ route('frontdesk.city-ledger.show', $account) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('frontdesk.city-ledger.update', $account) }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company / Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $account->contact_person) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $account->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $account->phone) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $account->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax ID / VAT</label>
                        <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id', $account->tax_id) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Credit Limit</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $account->credit_limit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                        <select name="payment_terms" class="form-select" required>
                            <option value="net15" {{ old('payment_terms', $account->payment_terms) === 'net15' ? 'selected' : '' }}>Net 15</option>
                            <option value="net30" {{ old('payment_terms', $account->payment_terms) === 'net30' ? 'selected' : '' }}>Net 30</option>
                            <option value="net45" {{ old('payment_terms', $account->payment_terms) === 'net45' ? 'selected' : '' }}>Net 45</option>
                            <option value="net60" {{ old('payment_terms', $account->payment_terms) === 'net60' ? 'selected' : '' }}>Net 60</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $account->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $account->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save"></i> Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
