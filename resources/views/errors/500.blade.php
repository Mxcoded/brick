@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Something Went Wrong'))
@section('description', __('An unexpected error occurred on our end. Our team has been notified and we are working to fix it. Please try again later.'))
@section('navigation')
    <a href="{{ route('home') }}" class="btn btn-gold">
        <i class="fas fa-home"></i> Back to Home
    </a>
    <a href="mailto:support@brickspoint.com" class="btn btn-outline">
        <i class="fas fa-envelope"></i> Contact Support
    </a>
@endsection
