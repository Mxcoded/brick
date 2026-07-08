@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Page Not Found'))
@section('description', __('The page you are looking for could not be found. It may have been moved, deleted, or the URL may be incorrect.'))
@section('navigation')
    @parent
@endsection
