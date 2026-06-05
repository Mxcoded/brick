@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
    {{ __($exception->getMessage() ?: 'You do not have permission to access this page.') }}
    <br>
    <a href="{{ url('/home') }}" class="mt-4 inline-block text-sm text-blue-500 hover:text-blue-700 underline" style="margin-top:1rem;display:inline-block;font-size:0.875rem;color:#3b82f6;text-decoration:underline;">
        &larr; Go to Dashboard
    </a>
@endsection