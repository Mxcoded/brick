<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\ReviewSubmitted;
use Modules\Website\Models\Testimonial;
use Tests\TestCase;

class ReviewSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);
    }

    public function test_guest_can_submit_review_without_email_and_no_mail_sent()
    {
        Mail::fake();

        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'John Doe',
            'text' => 'Great stay! Loved the service.',
            'rating' => 5,
            'type' => 'stay',
            'stay_type' => 'Business',
        ]);

        $response->assertRedirect(route('website.testimonials'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'John Doe',
            'email' => null,
            'approved' => false,
        ]);

        Mail::assertNothingSent();
    }

    public function test_guest_can_submit_review_with_email_and_receives_confirmation()
    {
        Mail::fake();

        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'text' => 'Wonderful experience!',
            'rating' => 5,
            'type' => 'stay',
        ]);

        $response->assertRedirect(route('website.testimonials'));

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'stay',
        ]);

        Mail::assertSent(ReviewSubmitted::class, function ($mail) {
            return $mail->hasTo('jane@example.com')
                && $mail->testimonial->guest_name === 'Jane Doe';
        });
    }

    public function test_guest_can_submit_restaurant_review_with_email()
    {
        Mail::fake();

        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Restaurant Guest',
            'email' => 'diner@example.com',
            'text' => 'Delicious food!',
            'rating' => 4,
            'type' => 'restaurant',
            'dining_venue' => 'Sky Restaurant',
        ]);

        $response->assertRedirect(route('website.testimonials'));

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Restaurant Guest',
            'type' => 'restaurant',
            'dining_venue' => 'Sky Restaurant',
        ]);

        Mail::assertSent(ReviewSubmitted::class, function ($mail) {
            return $mail->hasTo('diner@example.com');
        });
    }

    public function test_guest_can_submit_event_review_with_email()
    {
        Mail::fake();

        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Event Attendee',
            'email' => 'attendee@example.com',
            'text' => 'Amazing event!',
            'rating' => 5,
            'type' => 'event',
            'event_name' => 'New Year Gala',
        ]);

        $response->assertRedirect(route('website.testimonials'));

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Event Attendee',
            'type' => 'event',
            'event_name' => 'New Year Gala',
        ]);

        Mail::assertSent(ReviewSubmitted::class, function ($mail) {
            return $mail->hasTo('attendee@example.com');
        });
    }

    public function test_review_validation_requires_type()
    {
        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'No Type',
            'text' => 'Test',
            'rating' => 3,
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_review_validation_rejects_invalid_email()
    {
        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Bad Email',
            'email' => 'not-an-email',
            'text' => 'Test',
            'rating' => 3,
            'type' => 'stay',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_honeypot_field_prevents_spam()
    {
        Mail::fake();

        $response = $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Bot',
            'email' => 'bot@example.com',
            'text' => 'Spam',
            'rating' => 5,
            'type' => 'stay',
            'website' => 'filled-by-bot',
        ]);

        $response->assertRedirect(route('website.testimonials'));
        $this->assertDatabaseMissing('testimonials', ['guest_name' => 'Bot']);

        Mail::assertNothingSent();
    }

    public function test_confirmation_email_contains_review_details()
    {
        Mail::fake();

        $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Test User',
            'email' => 'test@example.com',
            'text' => 'Awesome place!',
            'rating' => 4,
            'type' => 'stay',
            'stay_type' => 'Leisure',
        ]);

        Mail::assertSent(ReviewSubmitted::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && $mail->testimonial->guest_name === 'Test User'
                && $mail->testimonial->rating === 4
                && $mail->testimonial->text === 'Awesome place!'
                && $mail->testimonial->type === 'stay';
        });
    }

    public function test_submission_stores_category_ratings_and_location()
    {
        Mail::fake();

        $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Category Rater',
            'text' => 'Overall great, Wi-Fi was a bit slow.',
            'rating' => 4,
            'type' => 'stay',
            'cleanliness' => 5,
            'wifi_rating' => 3,
            'staff_rating' => 5,
            'food_rating' => 4,
            'maintenance_rating' => 4,
            'location' => 'Asokoro, Abuja',
        ]);

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Category Rater',
            'rating' => 4,
            'cleanliness' => 5,
            'wifi_rating' => 3,
            'staff_rating' => 5,
            'food_rating' => 4,
            'maintenance_rating' => 4,
            'location' => 'Asokoro, Abuja',
        ]);

        Mail::assertNothingSent();
    }

    public function test_submission_stores_omada_captive_portal_context()
    {
        $this->post(route('website.testimonials.store'), [
            'guest_name' => 'Portal Guest',
            'text' => 'Connected via Omada portal, great internet.',
            'rating' => 5,
            'type' => 'stay',
            'ap_name' => 'Lobby-AP',
            'ap_mac' => 'AA:BB:CC:DD:EE:FF',
            'ssid' => 'Brickspoint-Guest',
            'client_mac' => '11:22:33:44:55:66',
            'client_ip' => '192.168.1.42',
            'portal_session' => '17634012',
            'radio_id' => '1',
            'location' => 'Brickspoint Asokoro',
        ]);

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Portal Guest',
            'ap_name' => 'Lobby-AP',
            'ap_mac' => 'AA:BB:CC:DD:EE:FF',
            'ssid' => 'Brickspoint-Guest',
            'client_mac' => '11:22:33:44:55:66',
            'client_ip' => '192.168.1.42',
            'portal_session' => '17634012',
            'location' => 'Brickspoint Asokoro',
        ]);

        $testimonial = Testimonial::where('guest_name', 'Portal Guest')->first();
        $this->assertIsArray($testimonial->wifi_meta);
        $this->assertArrayHasKey('ssid', $testimonial->wifi_meta);
        $this->assertArrayHasKey('client_mac', $testimonial->wifi_meta);
        $this->assertSame('1', $testimonial->wifi_meta['radio_id']);
    }

    public function test_guest_feedback_alias_renders_portal_context_on_form()
    {
        $response = $this->get(route('website.guest-feedback', [
            'ssidName' => 'Brickspoint-Guest',
            'apName' => 'Lobby-AP',
            'clientMac' => 'AA:BB:CC:DD:EE:FF',
            'radioId' => '1',
            'site' => 'Asokoro',
        ]));

        $response->assertOk();
        $response->assertSee('Brickspoint-Guest');
        $response->assertSee('Lobby-AP');
        $response->assertSee('AA:BB:CC:DD:EE:FF');
        $response->assertSee('5 GHz');
        $response->assertSee('Asokoro');
    }

    public function test_guest_feedback_alias_accepts_submissions()
    {
        $this->post(route('website.guest-feedback.store'), [
            'guest_name' => 'Alias Guest',
            'text' => 'Sent from the captive portal alias.',
            'rating' => 4,
            'type' => 'stay',
            'ssid' => 'Brickspoint-Guest',
        ]);

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'Alias Guest',
            'ssid' => 'Brickspoint-Guest',
        ]);
    }

    public function test_guest_feedback_accepts_generic_qr_context()
    {
        $response = $this->get(route('website.guest-feedback', [
            'source' => 'qrcode',
            'site' => 'Brickspoint Asokoro',
            'ssid' => 'Brickspoint-Guest',
            'ap' => 'Lobby Guest Wi-Fi',
            'band' => '5G',
        ]));

        $response->assertOk();
        $response->assertSee('Brickspoint-Guest');
        $response->assertSee('Lobby Guest Wi-Fi');
        $response->assertSee('5 GHz');
        $response->assertSee('Brickspoint Asokoro');
        $response->assertSee('qrcode');
    }

    public function test_submission_persists_generic_qr_capture_source()
    {
        $this->post(route('website.guest-feedback.store'), [
            'guest_name' => 'QR Guest',
            'text' => 'Great WiFi via the QR card.',
            'rating' => 5,
            'type' => 'stay',
            'location' => 'Brickspoint Asokoro',
            'ssid' => 'Brickspoint-Guest',
            'ap_name' => 'Lobby Guest Wi-Fi',
            'capture_source' => 'qrcode',
        ]);

        $this->assertDatabaseHas('testimonials', [
            'guest_name' => 'QR Guest',
            'location' => 'Brickspoint Asokoro',
            'ssid' => 'Brickspoint-Guest',
            'ap_name' => 'Lobby Guest Wi-Fi',
        ]);

        $testimonial = Testimonial::where('guest_name', 'QR Guest')->first();
        $this->assertIsArray($testimonial->wifi_meta);
        $this->assertSame('qrcode', $testimonial->wifi_meta['capture_source']);
    }

    public function test_qr_link_preselects_review_type()
    {
        $response = $this->get(route('website.guest-feedback', [
            'type' => 'restaurant',
            'source' => 'qrcode',
            'site' => 'Asokoro',
            'ssid' => 'Brickspoint-Guest',
        ]));

        $response->assertOk();
        $response->assertSee('<option value="restaurant" selected>Restaurant / Dining</option>', false);
        $response->assertSee('Brickspoint-Guest');
    }
}
