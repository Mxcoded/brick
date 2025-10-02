@extends('layouts.landing')

@section('content')

    {{-- The Hero section introduces the brand and key value proposition. --}}
    @include('components.hero')

    {{-- The Features section (System Overview) details the core functionalities. --}}
    @include('components.features')

    {{-- The Modules section provides a deeper look into specific product capabilities. --}}
    @include('components.modules')
    
    {{-- The Call-to-Action section prompts the user to take the next step. --}}
    @include('components.cta')  

@endsection