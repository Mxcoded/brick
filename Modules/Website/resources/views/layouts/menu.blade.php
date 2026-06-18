<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
    data-bs-toggle="collapse" href="#websiteAdminSubmenu" role="button"
    aria-expanded="{{ request()->routeIs('website.admin.*') ? 'true' : 'false' }}" aria-controls="websiteAdminSubmenu"
    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-globe fa-fw me-3"></i>Website & Bookings</span>
    <i class="fas fa-chevron-down small"></i>
</a>

<div class="collapse {{ request()->routeIs('website.admin.*') ? 'show' : '' }}" id="websiteAdminSubmenu">

    {{-- ═══ OVERVIEW (always visible) ═══ --}}
    <a href="{{ route('website.admin.dashboard') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    {{-- ═══ OPERATIONS ═══ --}}
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.bookings.*') || request()->routeIs('website.admin.contact-messages.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#opsSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('website.admin.bookings.*') || request()->routeIs('website.admin.contact-messages.*') ? 'true' : 'false' }}">
        <span><i class="fas fa-tasks fa-fw me-2"></i> Operations</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.bookings.*') || request()->routeIs('website.admin.contact-messages.*') ? 'show' : '' }}" id="opsSubmenu">
        <a href="{{ route('website.admin.bookings.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.bookings.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check fa-fw me-2"></i> Web Bookings
            @if (\Modules\Website\Models\Booking::where('status', 'pending')->count() > 0)
                <span class="badge bg-danger rounded-pill float-end">{{ \Modules\Website\Models\Booking::where('status', 'pending')->count() }}</span>
            @endif
        </a>
        <a href="{{ route('website.admin.contact-messages.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.contact-messages.*') ? 'active' : '' }}">
            <i class="fas fa-envelope fa-fw me-2"></i> Messages
            @if (\Modules\Website\Models\ContactMessage::where('status', 'unread')->count() > 0)
                <span class="badge bg-danger rounded-pill float-end">{{ \Modules\Website\Models\ContactMessage::where('status', 'unread')->count() }}</span>
            @endif
        </a>
    </div>

    {{-- ═══ CONTENT PAGES ═══ --}}
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.facilities.*') || request()->routeIs('website.admin.offers.*') || request()->routeIs('website.admin.meeting.*') || request()->routeIs('website.admin.dining.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#contentSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('website.admin.facilities.*') || request()->routeIs('website.admin.offers.*') || request()->routeIs('website.admin.meeting.*') || request()->routeIs('website.admin.dining.*') ? 'true' : 'false' }}">
        <span><i class="fas fa-edit fa-fw me-2"></i> Content Pages</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.facilities.*') || request()->routeIs('website.admin.offers.*') || request()->routeIs('website.admin.meeting.*') || request()->routeIs('website.admin.dining.*') ? 'show' : '' }}" id="contentSubmenu">
        <a href="{{ route('website.admin.facilities.edit') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.facilities.*') ? 'active' : '' }}">
            <i class="fas fa-th-large fa-fw me-2"></i> Facilities Page
        </a>
        <a href="{{ route('website.admin.offers.edit') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.offers.*') ? 'active' : '' }}">
            <i class="fas fa-tag fa-fw me-2"></i> Offers Page
        </a>
        <a href="{{ route('website.admin.meeting.edit') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.meeting.*') ? 'active' : '' }}">
            <i class="fas fa-building fa-fw me-2"></i> Meetings Page
        </a>
        <a href="{{ route('website.admin.dining.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dining.*') ? 'active' : '' }}">
            <i class="fas fa-utensils fa-fw me-2"></i> On-site Restaurant
        </a>
    </div>

    {{-- ═══ ACCOMMODATION ═══ --}}
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.room-types.*') || request()->routeIs('website.admin.inventory.*') || request()->routeIs('website.admin.rooms.*') || request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#accommodationSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('website.admin.room-types.*') || request()->routeIs('website.admin.inventory.*') || request()->routeIs('website.admin.rooms.*') || request()->routeIs('website.admin.amenities.*') ? 'true' : 'false' }}">
        <span><i class="fas fa-bed fa-fw me-2"></i> Accommodation</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.room-types.*') || request()->routeIs('website.admin.inventory.*') || request()->routeIs('website.admin.rooms.*') || request()->routeIs('website.admin.amenities.*') ? 'show' : '' }}" id="accommodationSubmenu">
        <a href="{{ route('website.admin.room-types.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.room-types.*') ? 'active' : '' }}">
            <i class="fas fa-bed fa-fw me-2"></i> Room Types
        </a>
        <a href="{{ route('website.admin.inventory.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.inventory.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt fa-fw me-2"></i> Inventory Calendar
        </a>
        <a href="{{ route('website.admin.rooms.calendar') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.rooms.calendar') ? 'active' : '' }}">
            <i class="fas fa-th fa-fw me-2"></i> Room Schedule
        </a>
        <a href="{{ route('website.admin.rooms.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.rooms.index', 'website.admin.rooms.create', 'website.admin.rooms.edit', 'website.admin.rooms.show') ? 'active' : '' }}">
            <i class="fas fa-door-open fa-fw me-2"></i> Rooms (Legacy)
        </a>
        <a href="{{ route('website.admin.amenities.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}">
            <i class="fas fa-wifi fa-fw me-2"></i> Amenities
        </a>
    </div>

    {{-- ═══ MARKETING ═══ --}}
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.newsletter.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#marketingSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('website.admin.newsletter.*') ? 'true' : 'false' }}">
        <span><i class="fas fa-bullhorn fa-fw me-2"></i> Marketing</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.newsletter.*') ? 'show' : '' }}" id="marketingSubmenu">
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.newsletter.campaigns.*') || request()->routeIs('website.admin.newsletter.subscribers*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#newsletterSubmenu" role="button"
            aria-expanded="{{ request()->routeIs('website.admin.newsletter.campaigns.*') || request()->routeIs('website.admin.newsletter.subscribers*') ? 'true' : 'false' }}">
            <span><i class="fas fa-newspaper fa-fw me-2"></i> Newsletter</span>
            <i class="fas fa-chevron-down small"></i>
        </a>
        <div class="collapse {{ request()->routeIs('website.admin.newsletter.campaigns.*') || request()->routeIs('website.admin.newsletter.subscribers*') ? 'show' : '' }}" id="newsletterSubmenu">
            <a href="{{ route('website.admin.newsletter.campaigns.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.campaigns.*') ? 'active' : '' }}">
                <i class="fas fa-paper-plane fa-fw me-2"></i> Campaigns
                @php $draftCount = \Modules\Website\Models\Newsletter::where('status', 'draft')->count(); @endphp
                @if ($draftCount > 0)
                    <span class="badge bg-secondary rounded-pill float-end">{{ $draftCount }}</span>
                @endif
            </a>
            <a href="{{ route('website.admin.newsletter.subscribers') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.subscribers*') ? 'active' : '' }}">
                <i class="fas fa-users fa-fw me-2"></i> Subscribers
                @php $activeSubscribers = \Modules\Website\Models\NewsletterSubscriber::where('is_active', true)->count(); @endphp
                @if ($activeSubscribers > 0)
                    <span class="badge bg-success rounded-pill float-end">{{ $activeSubscribers }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- ═══ SETTINGS ═══ --}}
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('website.admin.settings.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#settingsSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('website.admin.settings.*') ? 'true' : 'false' }}">
        <span><i class="fas fa-cog fa-fw me-2"></i> Settings</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.settings.*') ? 'show' : '' }}" id="settingsSubmenu">
        <a href="{{ route('website.admin.settings.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.settings.*') ? 'active' : '' }}">
            <i class="fas fa-cog fa-fw me-2"></i> CMS Settings
        </a>
    </div>

</div>
