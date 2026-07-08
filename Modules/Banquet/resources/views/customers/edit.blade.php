@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">Edit Customer</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    
    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-user-edit me-2 text-gold"></i>Edit Customer
            </h1>
            <p class="text-muted mb-0">Update customer information</p>
        </div>
        <a href="{{ route('banquet.customers.show', $customer->id) }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Profile
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-gold text-white py-3">
            <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('banquet.customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $customer->name) }}" required placeholder="Full Name">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $customer->email) }}" required placeholder="Email">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" name="phone" id="phone" class="form-control phone-input @error('phone') is-invalid @enderror" 
                                value="{{ old('phone', $customer->phone) }}" required placeholder="Phone">
                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="organization" id="organization" class="form-control @error('organization') is-invalid @enderror" 
                                value="{{ old('organization', $customer->organization) }}" placeholder="Organization">
                            <label for="organization">Organization (Optional)</label>
                            @error('organization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('banquet.customers.show', $customer->id) }}" class="btn btn-outline-charcoal btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-gold btn-lg">
                        <i class="fas fa-save me-2"></i>Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: #FFFFFF; }
    .form-control:focus { border-color: #C8A165; box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25); }
</style>
@endsection
