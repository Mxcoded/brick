@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-user-clock fa-5x mb-4" style="color: #C8A165;"></i>
                    <h2 class="fw-bold mb-3">Account Pending Setup</h2>
                    <p class="text-muted mb-4" style="font-size: 1.1rem;">
                        Your staff account has been created but no roles have been assigned yet.
                        Please contact your administrator to set up your permissions.
                    </p>
                    <a href="{{ route('logout') }}" class="btn btn-outline-primary px-4"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i>Sign Out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
