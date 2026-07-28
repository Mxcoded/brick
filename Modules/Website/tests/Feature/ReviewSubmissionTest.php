<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\ReviewSubmitted;
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
}
