@extends('layouts.master')

@section('title', 'Create Corporate Account')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.city-ledger.index') }}">City Ledger</a></li>
    <li class="breadcrumb-item active">New Account</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-primary"></i>New Corporate Account</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontdesk.city-ledger.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                <select name="payment_terms" class="form-select">
                                    <option value="net_15">Net 15</option>
                                    <option value="net_30" selected>Net 30</option>
                                    <option value="net_45">Net 45</option>
                                    <option value="net_60">Net 60</option>
                                    <option value="on_demand">On Demand</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credit Limit <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" step="0.01" name="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror" value="{{ old('credit_limit', 0) }}" min="0" required>
                                    @error('credit_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Account</button>
                            <a href="{{ route('frontdesk.city-ledger.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
