<div class="list-group-item list-group-item-action bg-light text-uppercase fw-bold text-muted small mt-3">
    Website & Bookings
</div>
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
    data-bs-toggle="collapse" href="#websiteAdminSubmenu" role="button"
    aria-expanded="{{ request()->routeIs('website.admin.*') ? 'true' : 'false' }}" aria-controls="websiteAdminSubmenu"
    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-user-shield fa-fw me-3"></i>Website & Bookings</span>
    <i class="fas fa-chevron-down small"></i>
</a>

<div class="collapse {{ request()->routeIs('website.admin.*') ? 'show' : '' }}" id="websiteAdminSubmenu">
    <a href="{{ route('website.admin.dashboard') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i> Overview
    </a>

    <a href="{{ route('website.admin.rooms.index') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.rooms.*') ? 'active' : '' }}">
        <i class="fas fa-bed me-2"></i> Room Inventory
    </a>

    <a href="{{ route('website.admin.bookings.index') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.bookings.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check me-2"></i> Web Bookings
        {{-- Optional: Show badge for pending bookings --}}
        @if (\Modules\Website\Models\Booking::where('status', 'pending')->count() > 0)
            <span class="badge bg-danger rounded-pill float-end">
                {{ \Modules\Website\Models\Booking::where('status', 'pending')->count() }}
            </span>
        @endif
    </a>

    <a href="{{ route('website.admin.amenities.index') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}">
        <i class="fas fa-wifi me-2"></i> Amenities
    </a>

    <a href="{{ route('website.admin.contact-messages.index') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.contact-messages.*') ? 'active' : '' }}">
        <i class="fas fa-envelope me-2"></i> Messages
         @if (\Modules\Website\Models\ContactMessage::where('status', 'unread')->count() > 0)
            <span class="badge bg-danger rounded-pill float-end">
                {{ \Modules\Website\Models\ContactMessage::where('status', 'unread')->count() }}
            </span>
        @endif
    </a>

    <a href="{{ route('website.admin.settings.index') }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.settings.*') ? 'active' : '' }}">
        <i class="fas fa-cog me-2"></i> CMS Settings
    </a>
</div>
