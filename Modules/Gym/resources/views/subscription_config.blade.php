@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-cog me-2"></i>Manage Subscription Configurations</h1>
            <p>FITNESSZONE BY BRICKSPOINT</p>
        </div>
        <a href="{{ route('gym.index') }}" class="btn btn-danger mt-2 mb-3"><i class="fas fa-arrow-circle-left"></i> Back to Dashboard</a>

        <div class="form-body">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Subscription Config Form -->
            <form method="POST" action="{{ route('gym.subscription-config.update') }}">
                @csrf
                @method('PUT')

                <div class="form-card">
                    <h3 class="section-title">Subscription Plans</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Monthly Fee (₦)</label>
                            <input type="number" class="form-control" name="monthly_fee" value="{{ old('monthly_fee', $config->monthly_fee) }}" required step="0.01" min="0">
                            @error('monthly_fee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Quarterly Fee (₦)</label>
                            <input type="number" class="form-control" name="quarterly_fee" value="{{ old('quarterly_fee', $config->quarterly_fee) }}" required step="0.01" min="0">
                            @error('quarterly_fee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">6 Months Fee (₦)</label>
                            <input type="number" class="form-control" name="six_months_fee" value="{{ old('six_months_fee', $config->six_months_fee) }}" required step="0.01" min="0">
                            @error('six_months_fee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Yearly Fee (₦)</label>
                            <input type="number" class="form-control" name="yearly_fee" value="{{ old('yearly_fee', $config->yearly_fee) }}" required step="0.01" min="0">
                            @error('yearly_fee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Session Fee (₦)</label>
                            <input type="number" class="form-control" name="session_fee" value="{{ old('session_fee', $config->session_fee) }}" required step="0.01" min="0">
                            @error('session_fee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Update Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection