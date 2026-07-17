@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('Service Unavailable'))
@section('description', __('We are currently undergoing maintenance or experiencing high traffic. Please check back shortly.'))
@section('navigation')
    @parent
@endsection
