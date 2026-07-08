@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Session Expired'))
@section('description', __('Your session has expired due to inactivity. Please sign in again to continue.'))
@section('navigation')
    <a href="{{ route('login') }}" class="btn btn-gold">
        <i class="fas fa-sign-in-alt"></i> Sign In Again
    </a>
    <a href="{{ route('home') }}" class="btn btn-outline">
        <i class="fas fa-home"></i> Back to Home
    </a>
@endsection
