@extends('errors::minimal')

@section('title', __('Payment Required'))
@section('code', '402')
@section('message', __('Payment Required'))
@section('description', __('This resource requires a valid payment method.'))
@section('navigation')
    @parent
@endsection
