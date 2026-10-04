<?php

namespace Modules\Website\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Website\Filament\Resources\BookingResource;
use Modules\Website\Filament\Resources\ContactMessageResource;
use Modules\Website\Filament\Resources\NewsletterResource;
use Modules\Website\Filament\Resources\NewsletterSubscriberResource;
use Modules\Website\Filament\Resources\TestimonialResource;
use Modules\Website\Models\Addon;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\Testimonial;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

/**
 * Operational summary of the public website.
 *
 * Read-only and permission-filtered: every figure is gated on the same capability
 * that lets the user open the screen behind it, so the dashboard cannot become a
 * way of learning a total that the list would refuse to show.
 */
class WebsiteOverview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Overview';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'website';

    protected static ?string $title = 'Website overview';

    protected string $view = 'website::filament.pages.overview';

    public static function canAccess(): bool
    {
        // The module gate plus at least one real capability. Holding the gate alone
        // is not enough: a role seeded with the gate but no capability would get an
        // overview with every section hidden and no explanation of why, which reads
        // as a broken screen rather than a correctly closed one.
        if (! WebsitePermission::allows(WebsitePermission::DASHBOARD_ACCESS)) {
            return false;
        }

        return static::anyCapability();
    }

    /**
     * Does this user hold anything at all in the Website taxonomy?
     */
    protected static function anyCapability(): bool
    {
        return WebsitePermission::allowsAny(...static::capabilityNames());
    }

    /**
     * Every capability the overview draws a figure from.
     *
     * @return array<int, string>
     */
    protected static function capabilityNames(): array
    {
        return [
            WebsitePermission::BOOKINGS_READ,
            WebsitePermission::BOOKINGS_UPDATE,
            WebsitePermission::CONTACT_MESSAGES_READ,
            WebsitePermission::CONTACT_MESSAGES_UPDATE,
            WebsitePermission::TESTIMONIALS_READ,
            WebsitePermission::TESTIMONIALS_UPDATE,
            WebsitePermission::NEWSLETTER_READ,
            WebsitePermission::NEWSLETTER_UPDATE,
            WebsitePermission::SUBSCRIBERS_READ,
            WebsitePermission::SUBSCRIBERS_UPDATE,
            WebsitePermission::ROOM_TYPES_READ,
            WebsitePermission::AMENITIES_READ,
            WebsitePermission::ADDONS_READ,
        ];
    }

    public function canViewBookings(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::BOOKINGS_READ,
            WebsitePermission::BOOKINGS_UPDATE,
        );
    }

    public function canViewEnquiries(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::CONTACT_MESSAGES_READ,
            WebsitePermission::CONTACT_MESSAGES_UPDATE,
        );
    }

    public function canViewTestimonials(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::TESTIMONIALS_READ,
            WebsitePermission::TESTIMONIALS_UPDATE,
        );
    }

    public function canViewSubscribers(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::SUBSCRIBERS_READ,
            WebsitePermission::SUBSCRIBERS_UPDATE,
        );
    }

    public function canViewNewsletters(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::NEWSLETTER_READ,
            WebsitePermission::NEWSLETTER_UPDATE,
        );
    }

    public function canViewCatalogue(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::ROOM_TYPES_READ,
            WebsitePermission::ROOM_TYPES_UPDATE,
            WebsitePermission::AMENITIES_READ,
            WebsitePermission::ADDONS_READ,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        return [
            'canViewBookings' => $this->canViewBookings(),
            'canViewEnquiries' => $this->canViewEnquiries(),
            'canViewTestimonials' => $this->canViewTestimonials(),
            'canViewSubscribers' => $this->canViewSubscribers(),
            'canViewNewsletters' => $this->canViewNewsletters(),
            'canViewCatalogue' => $this->canViewCatalogue(),
            'bookings' => $this->canViewBookings() ? $this->bookingFigures() : null,
            'enquiries' => $this->canViewEnquiries() ? $this->enquiryFigures() : null,
            'testimonials' => $this->canViewTestimonials() ? $this->testimonialFigures() : null,
            'audience' => $this->canViewSubscribers() || $this->canViewNewsletters()
                ? $this->audienceFigures()
                : null,
            'catalogue' => $this->canViewCatalogue() ? $this->catalogueFigures() : null,
            'arrivals' => $this->canViewBookings() ? $this->upcomingArrivals() : collect(),
            'unrepliedEnquiries' => $this->canViewEnquiries() ? $this->unrepliedEnquiries() : collect(),

            // Built here rather than in the view so a URL is only produced for an
            // area the viewer can actually open. Calling ::getUrl() on a resource the
            // user cannot access would still return a route, and the tile would link
            // to a 403.
            'bookingsUrl' => BookingResource::getUrl(),
            'enquiriesUrl' => ContactMessageResource::getUrl(),
            'testimonialsUrl' => TestimonialResource::getUrl(),
            'newslettersUrl' => NewsletterResource::getUrl(),
            'subscribersUrl' => NewsletterSubscriberResource::getUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingFigures(): array
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        return [
            'arrivingToday' => Booking::query()
                ->whereNull('deleted_at')
                ->whereDate('check_in_date', $today)
                ->count(),
            'departingToday' => Booking::query()
                ->whereNull('deleted_at')
                ->whereDate('check_out_date', $today)
                ->count(),
            // A cancelled booking is still a row and still a revenue figure the
            // ledger may have booked, so it stays in the money totals and only
            // drops out of the "live" count.
            'pending' => Booking::query()
                ->whereNull('deleted_at')
                ->where('status', 'pending')
                ->count(),
            'confirmed' => Booking::query()
                ->whereNull('deleted_at')
                ->where('status', 'confirmed')
                ->count(),
            'monthCount' => Booking::query()
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->count(),
            'monthRevenue' => (float) Booking::query()
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->sum('total_amount'),
            // The gap between what was charged and what has actually been paid is
            // the number a manager needs, so it is computed rather than left to be
            // worked out from two columns.
            'outstanding' => (float) Booking::query()
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['cancelled'])
                ->whereColumn('amount_paid', '<', 'total_amount')
                ->get(['total_amount', 'amount_paid'])
                ->sum(fn (Booking $booking): float => (float) $booking->total_amount - (float) $booking->amount_paid),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function enquiryFigures(): array
    {
        return [
            'unread' => ContactMessage::query()
                ->where('status', 'unread')
                ->where('is_archived', false)
                ->count(),
            'awaitingReply' => ContactMessage::query()
                ->where('is_archived', false)
                ->where('status', '!=', 'replied')
                ->count(),
            'thisWeek' => ContactMessage::query()
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function testimonialFigures(): array
    {
        return [
            'awaitingApproval' => Testimonial::query()
                ->where('approved', false)
                ->count(),
            'published' => Testimonial::query()
                ->where('approved', true)
                ->count(),
            // Averages over published reviews only: mixing in unpublished ones would
            // make the score a different number from the one the site shows.
            'averageRating' => round((float) Testimonial::query()
                ->where('approved', true)
                ->avg('rating'), 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function audienceFigures(): array
    {
        return [
            'subscribers' => NewsletterSubscriber::query()->where('is_active', true)->count(),
            'unsubscribed' => NewsletterSubscriber::query()->where('is_active', false)->count(),
            'draftCampaigns' => Newsletter::query()->where('status', Newsletter::STATUS_DRAFT)->count(),
            'scheduledCampaigns' => Newsletter::query()->where('status', Newsletter::STATUS_SCHEDULED)->count(),
            'sentCampaigns' => Newsletter::query()->where('status', Newsletter::STATUS_SENT)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogueFigures(): array
    {
        return [
            'liveRoomTypes' => RoomType::query()->where('is_active', true)->count(),
            'inactiveRoomTypes' => RoomType::query()->where('is_active', false)->count(),
            'featuredRoomTypes' => RoomType::query()->where('is_featured', true)->count(),
            'amenities' => Amenity::query()->count(),
            'activeAddons' => Addon::query()->where('is_active', true)->count(),
        ];
    }

    /**
     * Arrivals in the next three days, the daily question a front desk asks most.
     *
     * @return Collection<int, Booking>
     */
    private function upcomingArrivals()
    {
        return Booking::query()
            ->whereNull('deleted_at')
            ->whereBetween('check_in_date', [Carbon::today(), Carbon::today()->addDays(3)])
            ->with('roomType')
            ->orderBy('check_in_date')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, ContactMessage>
     */
    private function unrepliedEnquiries()
    {
        return ContactMessage::query()
            ->where('is_archived', false)
            ->where('status', '!=', 'replied')
            ->orderBy('created_at')
            ->limit(8)
            ->get();
    }
}
