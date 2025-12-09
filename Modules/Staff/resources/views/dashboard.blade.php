@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">My Dashboard</li>
@endsection

@section('page-content')
    <h1>Staff Dashboard</h1>
    <p>Welcome, {{ Auth::user()->name }}!</p>
   @include('banquet::partials.upcomingevent')
    {{-- <p>Your roles: {{ implode(', ', session('user_roles', [])) }}</p> --}}
    {{-- @include('tasks::index'); --}}
    <iframe width="853" height="480" src="https://my.matterport.com/show/?m=TkgqEGMsTqz" frameborder="0" allowfullscreen allow="autoplay; fullscreen; web-share; xr-spatial-tracking;"></iframe>
@endsection
