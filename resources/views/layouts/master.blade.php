@extends('layouts.base')

@section('content')
    <div class="d-flex" id="wrapper">

        @include('layouts.sidebar')

        {{-- These new classes create a flex container that takes up the full viewport height --}}
        <div id="page-content-wrapper" class="d-flex flex-column min-vh-100">

            @include('layouts.navbar')

            {{-- This new div will grow to fill all available space, pushing the footer down --}}
            <div class="container-fluid p-4 flex-grow-1">
                @yield('page-content')
            </div>

            <footer class="bg-light p-3 mt-auto border-top">
                <div class="container-fluid text-center">
                    <div
                        style="display: inline-block; padding: 10px 20px;   border-radius: 12px; background: var(--glass-effect); border: 1px solid var(--glass-border);
                box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2), 
                            -4px -4px 15px rgba(255, 255, 255, 0.05); transform: perspective(600px) rotateX(2deg); transition: var(--transition); margin-right: 15px;">
                        <p class="mb-0 text-muted">&copy; {{ date('Y') }}

                            <a href="home"
                                style="                font-weight: 800;
                font-size: 1.4rem;
                color: #130707;
                text-decoration: none;
                letter-spacing: -0.5px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            ">
                                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub> <sub
                                    style="font-size:8pt;">v1.0</sub>
                            </a>
                    . All rights reserved.</div></p> 
                    <p class="mb-0 text-muted">™ Developed with ❤️ by IT Team </p>
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
