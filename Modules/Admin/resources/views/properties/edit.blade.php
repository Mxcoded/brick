@extends('layouts.master')

@section('title', 'Edit Property')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Property</h4>
        <a href="{{ route('admin.properties.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('admin.properties.update', $property) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Property Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $property->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $property->slug) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $property->code) }}" required maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $property->address) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $property->city) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $property->state) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $property->country) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $property->contact_email) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $property->contact_phone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control" value="{{ old('currency', $property->currency) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $property->timezone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" @checked($property->is_active)>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Update Property</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
