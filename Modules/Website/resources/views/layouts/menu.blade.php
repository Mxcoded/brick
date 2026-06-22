@can('access_website_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#websiteAdminSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('website.admin.*') ? 'true' : 'false' }}" aria-controls="websiteAdminSubmenu">
    <i class="fas fa-globe fa-fw"></i>
    <span>Website &amp; Bookings</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('website.admin.*') ? 'show' : '' }}" id="websiteAdminSubmenu">

    <a href="{{ route('website.admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Operations</div>

    <a href="{{ route('website.admin.bookings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.bookings.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check fa-fw me-2"></i> Web Bookings
        @php $pendingBookings = \Modules\Website\Models\Booking::where('status', 'pending')->count(); @endphp
        @if ($pendingBookings > 0)
            <span class="badge bg-danger rounded-pill">{{ $pendingBookings }}</span>
        @endif
    </a>

    <a href="{{ route('website.admin.contact-messages.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.contact-messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope fa-fw me-2"></i> Messages
        @php $unreadMessages = \Modules\Website\Models\ContactMessage::where('status', 'unread')->count(); @endphp
        @if ($unreadMessages > 0)
            <span class="badge bg-danger rounded-pill">{{ $unreadMessages }}</span>
        @endif
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Content Pages</div>

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

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Accommodation</div>

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
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.rooms.index') || request()->routeIs('website.admin.rooms.create') || request()->routeIs('website.admin.rooms.edit') || request()->routeIs('website.admin.rooms.show') ? 'active' : '' }}">
        <i class="fas fa-door-open fa-fw me-2"></i> Rooms (Legacy)
    </a>

    <a href="{{ route('website.admin.amenities.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}">
        <i class="fas fa-wifi fa-fw me-2"></i> Amenities
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Marketing</div>

    <a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#newsletterSubmenu" role="button"
       aria-expanded="{{ request()->routeIs('website.admin.newsletter.*') ? 'true' : 'false' }}">
        <i class="fas fa-newspaper fa-fw me-2"></i>
        <span>Newsletter</span>
        <i class="fas fa-chevron-down"></i>
    </a>
    <div class="collapse {{ request()->routeIs('website.admin.newsletter.*') ? 'show' : '' }}" id="newsletterSubmenu">
        <a href="{{ route('website.admin.newsletter.campaigns.index') }}"
           class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.campaigns.*') ? 'active' : '' }}">
            <i class="fas fa-paper-plane fa-fw me-2"></i> Campaigns
            @php $draftCount = \Modules\Website\Models\Newsletter::where('status', 'draft')->count(); @endphp
            @if ($draftCount > 0)
                <span class="badge bg-secondary rounded-pill">{{ $draftCount }}</span>
            @endif
        </a>
        <a href="{{ route('website.admin.newsletter.subscribers') }}"
           class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.subscribers*') ? 'active' : '' }}">
            <i class="fas fa-users fa-fw me-2"></i> Subscribers
            @php $activeSubscribers = \Modules\Website\Models\NewsletterSubscriber::where('is_active', true)->count(); @endphp
            @if ($activeSubscribers > 0)
                <span class="badge bg-success rounded-pill">{{ $activeSubscribers }}</span>
            @endif
        </a>
    </div>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Settings</div>

    <a href="{{ route('website.admin.settings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.settings.*') ? 'active' : '' }}">
        <i class="fas fa-cog fa-fw me-2"></i> CMS Settings
    </a>

</div>
@endcan
