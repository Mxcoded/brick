@can('access_admin_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#adminSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}" aria-controls="adminSubmenu">
    <i class="fas fa-user-shield fa-fw"></i>
    <span>Administration</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">

    <a href="{{ route('admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Access Control</div>

    @can('manage_users')
    <a href="{{ route('admin.users.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Users
    </a>
    @endcan

    @can('manage_roles')
    <a href="{{ route('admin.roles.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag fa-fw me-2"></i> Roles
    </a>
    @endcan

    @can('permissions.read')
    <a href="{{ route('admin.permissions.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <i class="fas fa-key fa-fw me-2"></i> Permissions
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">System</div>

    @can('manage_settings')
    <a href="{{ route('admin.modules.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
        <i class="fas fa-cubes fa-fw me-2"></i> Modules
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Audit &amp; Logs</div>

    <a href="{{ route('admin.activity-logs.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
        <i class="fas fa-history fa-fw me-2"></i> Activity Logs
    </a>

    <a href="{{ route('admin.login-logs.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.login-logs.*') ? 'active' : '' }}">
        <i class="fas fa-sign-in-alt fa-fw me-2"></i> Login History
    </a>

</div>
@endcan

@can('access_website_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#websiteSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('website.admin.*') ? 'true' : 'false' }}" aria-controls="websiteSubmenu">
    <i class="fas fa-globe fa-fw"></i>
    <span>Website</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('website.admin.*') ? 'show' : '' }}" id="websiteSubmenu">

    <a href="{{ route('website.admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Content</div>

    @canany(['access_website_dashboard', 'website.testimonials'])
    <a href="{{ route('website.admin.testimonials.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.testimonials.*') ? 'active' : '' }}">
        <i class="fas fa-star fa-fw me-2"></i> Testimonials
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.amenities'])
    <a href="{{ route('website.admin.amenities.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}">
        <i class="fas fa-swimming-pool fa-fw me-2"></i> Amenities
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.dining'])
    <a href="{{ route('website.admin.dining.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dining.*') ? 'active' : '' }}">
        <i class="fas fa-utensils fa-fw me-2"></i> Dining
    </a>
    @endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Pages</div>

    @canany(['access_website_dashboard', 'website.meeting'])
    <a href="{{ route('website.admin.meeting.edit') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.meeting.*') ? 'active' : '' }}">
        <i class="fas fa-handshake fa-fw me-2"></i> Meeting &amp; Events
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.facilities'])
    <a href="{{ route('website.admin.facilities.edit') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.facilities.*') ? 'active' : '' }}">
        <i class="fas fa-building fa-fw me-2"></i> Facilities
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.offers'])
    <a href="{{ route('website.admin.offers.edit') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.offers.*') ? 'active' : '' }}">
        <i class="fas fa-tags fa-fw me-2"></i> Offers
    </a>
    @endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Inventory</div>

    @canany(['access_website_dashboard', 'website.room-types'])
    <a href="{{ route('website.admin.room-types.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.room-types.*') ? 'active' : '' }}">
        <i class="fas fa-bed fa-fw me-2"></i> Room Types
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.bookings'])
    <a href="{{ route('website.admin.bookings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.bookings.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check fa-fw me-2"></i> Bookings
    </a>
    @endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Communications</div>

    @canany(['access_website_dashboard', 'website.contact-messages'])
    <a href="{{ route('website.admin.contact-messages.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.contact-messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope fa-fw me-2"></i> Contact Messages
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.newsletter'])
    <a href="{{ route('website.admin.newsletter.campaigns.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.campaigns.*') ? 'active' : '' }}">
        <i class="fas fa-mail-bulk fa-fw me-2"></i> Newsletter Campaigns
    </a>
    @endcanany

    @canany(['access_website_dashboard', 'website.subscribers'])
    <a href="{{ route('website.admin.newsletter.subscribers') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.newsletter.subscribers*') ? 'active' : '' }}">
        <i class="fas fa-address-book fa-fw me-2"></i> Subscribers
    </a>
    @endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Settings</div>

    @canany(['access_website_dashboard', 'website.settings'])
    <a href="{{ route('website.admin.settings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.settings.*') ? 'active' : '' }}">
        <i class="fas fa-cog fa-fw me-2"></i> Settings
    </a>
    @endcanany

</div>
@endcan
