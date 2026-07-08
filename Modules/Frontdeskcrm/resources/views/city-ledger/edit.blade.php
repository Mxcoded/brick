@extends('layouts.master')

@section('title', 'Edit '.$corporateAccount->company_name)

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.city-ledger.index') }}">City Ledger</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.city-ledger.show', $corporateAccount) }}">{{ $corporateAccount->company_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Edit: {{ $corporateAccount->company_name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontdesk.city-ledger.update', $corporateAccount) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $corporateAccount->company_name) }}" required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                <select name="payment_terms" class="form-select">
                                    @foreach(['net_15', 'net_30', 'net_45', 'net_60', 'on_demand'] as $term)
                                        <option value="{{ $term }}" @selected(old('payment_terms', $corporateAccount->payment_terms) == $term)>{{ ucfirst(str_replace('_', ' ', $term)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $corporateAccount->contact_person) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $corporateAccount->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $corporateAccount->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credit Limit <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" step="0.01" name="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror" value="{{ old('credit_limit', $corporateAccount->credit_limit) }}" min="0" required>
                                    @error('credit_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $corporateAccount->address) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes', $corporateAccount->notes) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" @checked(old('is_active', $corporateAccount->is_active))>
                                    <label class="form-check-label" for="isActive">Account is active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Account</button>
                            <a href="{{ route('frontdesk.city-ledger.show', $corporateAccount) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
