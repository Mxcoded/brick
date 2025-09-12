@extends('staff::layouts.base')

@section('content')
    <div class="d-flex" id="wrapper">
        
        @include('staff::layouts.sidebar')

        {{-- These new classes create a flex container that takes up the full viewport height --}}
        <div id="page-content-wrapper" class="d-flex flex-column min-vh-100">
            
            @include('admin::layouts.navbar')

            {{-- This new div will grow to fill all available space, pushing the footer down --}}
            <div class="container-fluid p-4 flex-grow-1">
                @yield('page-content')
            </div>

            <footer class="bg-light p-3 mt-auto border-top">
                <div class="container-fluid">
                    <p class="mb-0 text-muted">&copy; {{ date('Y') }} BRICKSPOINT v1.0. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.querySelector('#wrapper').classList.toggle('toggled');
                });
            }
        });
    </script>
    @yield('page-scripts')
@endsection