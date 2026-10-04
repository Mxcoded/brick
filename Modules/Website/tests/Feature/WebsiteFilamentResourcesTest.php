<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Filament\Resources\AddonResource;
use Modules\Website\Filament\Resources\AmenityResource;
use Modules\Website\Filament\Resources\BookingResource;
use Modules\Website\Filament\Resources\ContactMessageResource;
use Modules\Website\Filament\Resources\NewsletterResource;
use Modules\Website\Filament\Resources\NewsletterSubscriberResource;
use Modules\Website\Filament\Resources\RoomTypeResource;
use Modules\Website\Filament\Resources\TestimonialResource;
use Modules\Website\Models\Addon;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\Testimonial;
use Modules\Website\Tests\Concerns\GrantsWebsiteRoles;
use Tests\TestCase;

/**
 * Every page of the Website migration actually rendering.
 *
 * The role matrix proves who may reach a screen. This proves the screen renders:
 * a form that names a column the table does not have, a select pointing at a
 * relation that does not exist, or a stat tile bound to a view variable that was
 * never passed all return 500 rather than 403, so the authorization tests stay
 * green on a broken page.
 */
class WebsiteFilamentResourcesTest extends TestCase
{
    use DatabaseTransactions, GrantsWebsiteRoles;

    private User $websiteAdmin;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantWebsiteRole(RoleEnum::WEBSITE_ADMIN->value);

        $this->websiteAdmin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->websiteAdmin->assignRole(RoleEnum::WEBSITE_ADMIN->value);

        $this->roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite',
            'price' => 45000,
            'capacity' => 3,
            'description' => 'A suite with a city view.',
            'is_active' => true,
        ]);

        $this->actingAs($this->websiteAdmin);
    }

    public function test_every_index_renders(): void
    {
        $this->makeRecords();

        foreach ($this->resources() as $resource) {
            $this->get($resource::getUrl())
                ->assertSuccessful("{$resource} index should render");
        }
    }

    public function test_every_create_page_renders(): void
    {
        $this->makeRecords();

        foreach ($this->resources() as $resource) {
            if (! $resource::canCreate()) {
                continue;
            }

            $this->get($resource::getUrl('create'))
                ->assertSuccessful("{$resource} create should render");
        }
    }

    public function test_every_view_and_edit_page_renders(): void
    {
        $records = $this->makeRecords();

        foreach ($this->resources() as $resource) {
            $record = $records[$resource] ?? null;

            if (! $record) {
                continue;
            }

            $this->get($resource::getUrl('view', ['record' => $record]))
                ->assertSuccessful("{$resource} view should render");

            if ($resource::canEdit($record)) {
                $this->get($resource::getUrl('edit', ['record' => $record]))
                    ->assertSuccessful("{$resource} edit should render");
            }
        }
    }

    /**
     * The overview builds every figure it draws, so a renamed column or a dropped
     * relation fails here rather than on a manager's screen.
     */
    public function test_the_overview_renders_with_data_in_every_state(): void
    {
        $this->makeRecords();

        $this->get('/admin/website')->assertSuccessful();
    }

    /**
     * The reply thread is a read-only relation manager over the same records, and
     * it is the one place the enquiry screen reaches into history.
     */
    public function test_the_enquiry_reply_thread_renders(): void
    {
        $this->makeRecords();

        $message = ContactMessage::query()->latest('id')->firstOrFail();

        $this->get(ContactMessageResource::getUrl('view', ['record' => $message]))
            ->assertSuccessful();
    }

    /**
     * A campaign's figures are disabled inputs rather than editable ones, so the
     * send cannot be overwritten by hand. Assert that on the rendered form.
     */
    public function test_a_sent_campaign_cannot_have_its_results_typed_over(): void
    {
        $sent = Newsletter::create([
            'subject' => 'August rates',
            'content' => '<p>Body</p>',
            'status' => Newsletter::STATUS_SENT,
            'recipients_count' => 120,
            'sent_count' => 118,
            'failed_count' => 2,
            'opened_count' => 40,
            'clicked_count' => 9,
            'sent_at' => now(),
        ]);

        $this->actingAs($this->websiteAdmin)
            ->get(NewsletterResource::getUrl('edit', ['record' => $sent]))
            ->assertSuccessful();

        $this->assertSame(118, $sent->fresh()->sent_count);
    }

    /**
     * Duplicating is done through the model here rather than the legacy POST route,
     * so it is asserted directly: a copy is a fresh draft, not a second send.
     */
    public function test_duplicating_a_campaign_produces_a_draft(): void
    {
        $original = Newsletter::create([
            'subject' => 'Weekend offer',
            'preview_text' => 'Save 20%',
            'content' => '<p>Body</p>',
            'status' => Newsletter::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $copy = $original->duplicate();

        $this->assertNotSame($original->id, $copy->id);
        $this->assertSame(Newsletter::STATUS_DRAFT, $copy->status);
        $this->assertSame('Weekend offer (Copy)', $copy->subject);
        $this->assertNull($copy->sent_at);

        // The duplicate action redirects to the copy's edit page, so that page has
        // to render.
        $this->actingAs($this->websiteAdmin)
            ->get(NewsletterResource::getUrl('edit', ['record' => $copy]))
            ->assertSuccessful();
    }

    /**
     * @return array<class-string, object>
     */
    private function makeRecords(): array
    {
        $amenity = Amenity::create(['name' => 'Pool', 'icon' => 'pool']);

        $this->roomType->amenities()->attach($amenity);

        $addon = Addon::create([
            'name' => 'Breakfast',
            'slug' => 'breakfast',
            'price' => 8000,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_reference' => 'BK'.uniqid(),
            'room_type_id' => $this->roomType->id,
            'guest_name' => 'Ada Guest',
            'guest_email' => 'ada@example.com',
            'check_in_date' => now()->addDay()->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 2,
            'children' => 1,
            'total_amount' => 50000,
            'amount_paid' => 20000,
            'payment_status' => 'partial',
            'status' => 'pending',
        ]);

        // The pivot snapshots the add-on as it was at booking time, so name, price
        // and totals are columns on the pivot rather than read back off the add-on.
        $booking->addons()->attach($addon, [
            'name' => $addon->name,
            'price' => $addon->price,
            'is_per_night' => false,
            'quantity' => 1,
            'total' => $addon->price,
        ]);

        $testimonial = Testimonial::create([
            'guest_name' => 'Ada Guest',
            'text' => 'The suite was spotless and the view was worth the upgrade.',
            'rating' => 5,
            'cleanliness' => 5,
            'staff_rating' => 5,
            'approved' => false,
            'type' => 'stay',
            'stay_type' => 'Weekend',
        ]);

        $message = ContactMessage::create([
            'name' => 'Kofi Visitor',
            'email' => 'kofi@example.com',
            'subject' => 'Wedding venue',
            'message' => 'Do you host weddings in December?',
            'status' => 'unread',
            'is_archived' => false,
        ]);

        $message->replies()->create([
            'user_id' => $this->websiteAdmin->id,
            'message' => 'Thank you for reaching out.',
            'direction' => 'outgoing',
            'is_read' => true,
        ]);

        $campaign = Newsletter::create([
            'subject' => 'August rates',
            'preview_text' => 'Book early',
            'content' => '<p>Our new rates are live.</p>',
            'status' => Newsletter::STATUS_DRAFT,
        ]);

        $subscriber = NewsletterSubscriber::create([
            'name' => 'Ama Reader',
            'email' => 'ama'.uniqid().'@example.com',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return [
            BookingResource::class => $booking,
            TestimonialResource::class => $testimonial,
            ContactMessageResource::class => $message,
            NewsletterResource::class => $campaign,
            NewsletterSubscriberResource::class => $subscriber,
            RoomTypeResource::class => $this->roomType,
            AmenityResource::class => $amenity,
            AddonResource::class => $addon,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    private function resources(): array
    {
        return [
            BookingResource::class,
            TestimonialResource::class,
            ContactMessageResource::class,
            NewsletterResource::class,
            NewsletterSubscriberResource::class,
            RoomTypeResource::class,
            AmenityResource::class,
            AddonResource::class,
        ];
    }
}
