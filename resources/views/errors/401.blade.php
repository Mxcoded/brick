@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized'))
@section('description', __('You need to be authenticated to access this resource. Please sign in to continue.'))
@section('navigation')
    <a href="{{ route('login') }}" class="btn btn-gold">
        <i class="fas fa-sign-in-alt"></i> Sign In
    </a>
    <a href="{{ route('home') }}" class="btn btn-outline">
        <i class="fas fa-home"></i> Back to Home
    </a>
@endsection
