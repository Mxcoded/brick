@extends('staff::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">My profile</li>
@endsection


@section('page-content')
    <div class="container">
   
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h2 class="h5 mb-0">Profile Settings</h2>
                    </div>

                    <div class="card-body p-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Personal Info Section -->
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h3 class="h6 text-dark mb-3 d-flex align-items-center">
                                            Personal Information
                                            <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Update your basic account information"></i>
                                        </h3>

                                        <div class="mb-3">
                                            <label for="name" class="form-label fw-medium">Full Name</label>
                                            <div class="input-group">
                                                <input type="text" name="name" id="name"
                                                    value="{{ old('name', $user->name) }}"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    placeholder="Enter your full name">
                                                <span class="input-group-text">
                                                    <i class="bi bi-person-fill"></i>
                                                </span>
                                                @error('name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-medium">Email Address</label>
                                            <div class="input-group">
                                                <input type="email" name="email" id="email"
                                                    value="{{ old('email', $user->email) }}"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    placeholder="Enter your email" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="This email will be used for account verification">
                                                <span class="input-group-text">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </span>
                                                @error('email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Password Section -->
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h3 class="h6 text-dark mb-3 d-flex align-items-center">
                                            Password Settings
                                            <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Leave blank if you don't want to change password"></i>
                                        </h3>

                                        <div class="mb-3">
                                            <label for="current_password" class="form-label fw-medium">Current
                                                Password</label>
                                            <div class="input-group">
                                                <input type="password" name="current_password" id="current_password"
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    placeholder="Enter current password">
                                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                                @error('current_password')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="new_password" class="form-label fw-medium">New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" id="new_password"
                                                    class="form-control @error('new_password') is-invalid @enderror"
                                                    placeholder="Enter new password">
                                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                                @error('new_password')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="new_password_confirmation" class="form-label fw-medium">Confirm
                                                Password</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password_confirmation"
                                                    id="new_password_confirmation" class="form-control"
                                                    placeholder="Confirm new password">
                                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Bootstrap tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                // Password visibility toggle
                document.querySelectorAll('.password-toggle').forEach(button => {
                    button.addEventListener('click', function() {
                        const input = this.previousElementSibling
                        const icon = this.querySelector('i')
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password'

                        input.setAttribute('type', type)
                        icon.classList.toggle('bi-eye-slash')
                        icon.classList.toggle('bi-eye')
                    })
                })
            })
        </script>
    @endpush
@endsection
