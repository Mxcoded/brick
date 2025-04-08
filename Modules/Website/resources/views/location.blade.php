@extends('website::layouts.master')

@section('title', 'Location')

@section('content')
<section class="location-section py-5 py-lg-7">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Our Location</h1>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Conveniently located in the heart of Abuja city, close to major attractions and business districts.
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Google Map Embed -->
                <div class="ratio ratio-16x9 mb-5">
                   <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.245471836331!2d7.515760620783751!3d9.041358819331956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104e0be6749dba69%3A0x8be5a894805903b9!2s24%20Jose%20Marti%20St%2C%20Asokoro%2C%20Crescent%20900110%2C%20Federal%20Capital%20Territory!5e0!3m2!1sen!2sng!4v1743448389956!5m2!1sen!2sng" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
               </div>
                <div class="text-center">
                    <h2 class="h4 mb-3">Address</h2>
                    <p>24 Jose Marti St, Asokoro, Crescent 900110, Federal Capital Territory</p>
                    <h2 class="h4 mb-3">Nearby Attractions</h2>
                    <ul class="list-unstyled">
                        <li>- Famous Museum: 0.5 miles</li>
                        <li>- Shopping District: 1 mile</li>
                        <li>- Airport: 15 miles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection