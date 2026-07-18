<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Frontdeskcrm\Database\Seeders\MessageTemplateSeeder;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestMessage;
use Modules\Frontdeskcrm\Models\MessageTemplate;
use Modules\Frontdeskcrm\Models\Registration;
use Tests\TestCase;

class PreArrivalCommandsTest extends TestCase
{
    use RefreshDatabase;

    private Property $property;

    private RoomType $roomType;

    private Guest $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->property = Property::factory()->create(['is_active' => true]);
        $this->roomType = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'is_active' => true,
        ]);

        $this->guest = Guest::create([
            'full_name' => 'Guest User',
            'email' => 'guest@example.com',
            'contact_number' => '+234800000000',
            'nationality' => 'NG',
        ]);
    }

    private function createRegistration(array $overrides = []): Registration
    {
        return Registration::create(array_merge([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'reserved',
            'reservation_code' => 'CMD-'.str()->random(8),
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
            'property_id' => $this->property->id,
            'full_name' => 'Guest User',
            'contact_number' => '+234800000000',
            'email' => 'guest@example.com',
            'no_of_guests' => 1,
            'no_of_nights' => 2,
            'room_rate' => 20000,
            'total_amount' => 40000,
            'agreed_to_policies' => true,
            'registration_date' => now()->format('Y-m-d'),
            'pre_arrival_token' => 'cmd-token-'.str()->random(20),
        ], $overrides));
    }

    public function test_pre_arrival_reminders_sends_to_upcoming_guests(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration();

        $this->artisan('hotel:send-pre-arrival-reminders')
            ->assertSuccessful()
            ->expectsOutputToContain('1 of 1 pre-arrival reminders');
    }

    public function test_pre_arrival_reminders_skips_completed(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration(['pre_arrival_completed_at' => now()]);

        $this->artisan('hotel:send-pre-arrival-reminders')
            ->assertSuccessful()
            ->expectsOutputToContain('No pending pre-arrivals found');
    }

    public function test_pre_arrival_reminders_skips_draft(): void
    {
        $this->createRegistration(['stay_status' => 'draft']);

        $this->artisan('hotel:send-pre-arrival-reminders')
            ->assertSuccessful()
            ->expectsOutputToContain('No pending pre-arrivals found');
    }

    public function test_pre_arrival_reminders_no_template(): void
    {
        $this->createRegistration();

        $this->artisan('hotel:send-pre-arrival-reminders')
            ->assertSuccessful();
    }

    public function test_review_requests_sends_to_checked_out_guests(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::yesterday()->subDay()->format('Y-m-d'),
            'check_out' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $this->artisan('hotel:send-review-requests')
            ->assertSuccessful()
            ->expectsOutputToContain('Sent 1 review requests');
    }

    public function test_review_requests_skips_non_matching_checkout(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'check_out' => Carbon::now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->artisan('hotel:send-review-requests')
            ->assertSuccessful()
            ->expectsOutputToContain('No recently checked-out guests found');
    }

    public function test_review_requests_skips_if_already_sent(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $registration = $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::yesterday()->subDay()->format('Y-m-d'),
            'check_out' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $template = MessageTemplate::where('event', 'review_request')->first();
        GuestMessage::create([
            'registration_id' => $registration->id,
            'guest_id' => $this->guest->id,
            'template_id' => $template->id,
            'channel' => 'email',
            'recipient' => 'guest@example.com',
            'subject' => 'Review Request',
            'body' => 'How was your stay?',
            'status' => 'sent',
        ]);

        $this->artisan('hotel:send-review-requests')
            ->assertSuccessful()
            ->expectsOutputToContain('No recently checked-out guests found');
    }

    public function test_re_engagement_sends_to_long_term_past_guests(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::now()->subDays(100)->format('Y-m-d'),
            'check_out' => Carbon::now()->subDays(95)->format('Y-m-d'),
        ]);

        $this->artisan('hotel:re-engagement-campaign')
            ->assertSuccessful()
            ->expectsOutputToContain('Sent 1 re-engagement messages');
    }

    public function test_re_engagement_skips_recent_guests(): void
    {
        $this->seed(MessageTemplateSeeder::class);

        $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'check_out' => Carbon::now()->subDays(28)->format('Y-m-d'),
        ]);

        $this->artisan('hotel:re-engagement-campaign')
            ->assertSuccessful()
            ->expectsOutputToContain('No past guests eligible for re-engagement');
    }

    public function test_re_engagement_skips_if_no_template(): void
    {
        $this->createRegistration([
            'stay_status' => 'checked_out',
            'check_in' => Carbon::now()->subDays(100)->format('Y-m-d'),
            'check_out' => Carbon::now()->subDays(95)->format('Y-m-d'),
        ]);

        $this->artisan('hotel:re-engagement-campaign')
            ->assertSuccessful();
    }

    public function test_all_three_commands_are_registered(): void
    {
        $this->artisan('list')->expectsOutputToContain('hotel:send-pre-arrival-reminders');
        $this->artisan('list')->expectsOutputToContain('hotel:send-review-requests');
        $this->artisan('list')->expectsOutputToContain('hotel:re-engagement-campaign');
    }
}
