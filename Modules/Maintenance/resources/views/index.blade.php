@extends('maintenance::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('maintenance.name') !!}</p>
@endsection
