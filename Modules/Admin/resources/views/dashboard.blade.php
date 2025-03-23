@extends('admin::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('page-content')
    <h1>Admin Dashboard</h1>
    <p>Welcome to the admin panel.</p>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Statistics</h5>
                </div>
                <div class="card-body">
                    <p>Some statistics here...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p>Recent activity here...</p>
                </div>
            </div>
        </div>
    </div>
@endsection