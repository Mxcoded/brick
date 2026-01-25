@can('access_frontdesk_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#frontdeskSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('frontdesk.*') ? 'true' : 'false' }}" aria-controls="frontdeskSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-concierge-bell fa-fw me-3"></i> Front Desk</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('frontdesk.*') ? 'show' : '' }}" id="frontdeskSubmenu">
    
    <a href="{{ route('frontdesk.rooms.schedule') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rooms.schedule') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-calendar-alt me-2"></i> Calendar / D. Chart
    </a>
 
    {{-- Guest Management --}}
    <a href="{{ route('frontdesk.registrations.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-users me-2"></i> Guest List
    </a>
    
    {{-- Check-In Actions --}}
    @can('check_in_guest')
    <a href="{{ route('frontdesk.registrations.createWalkin') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.createWalkin') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-walking me-2"></i> Walk-In (VIP)
    </a>
    @endcan

   
</div>
@endcan