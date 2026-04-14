@extends('layouts.master')

@section('title', 'Room Availability Calendar')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                {{-- Simply include the component! --}}
                @include('components.room-calendar')
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                {{-- Simply include the component! --}}
                @include('components.room-rack')
            </div>
        </div>
    </div>

@endsection
