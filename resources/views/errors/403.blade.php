@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
    {{ __($exception->getMessage() ?: 'You do not have permission to access this page.') }}
@endsection
@section('description', __('Please contact your administrator if you believe this is an error.'))
@section('navigation')
    <a href="{{ route('home') }}" class="btn btn-gold">
        <i class="fas fa-home"></i> Go to Dashboard
    </a>
    <a href="{{ route('login') }}" class="btn btn-outline">
        <i class="fas fa-sign-in-alt"></i> Sign In
    </a>
@endsection
