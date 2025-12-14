@can('access_banquet_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#banquetSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('banquet.*') ? 'true' : 'false' }}" aria-controls="banquetSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-glass-cheers fa-fw me-3"></i> Banquet</span>
    <i class="fas fa-chevron-down small"></i>
</a>

<div class="collapse {{ request()->routeIs('banquet.*') ? 'show' : '' }}" id="banquetSubmenu">
    {{-- 1. DASHBOARD / OVERVIEW --}}
    <a href="{{ route('banquet.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.index') || request()->routeIs('banquet.orders.index') ? 'active' : '' }}" 
       style="color: #ddd; border: none; padding-left: 3rem;">
        Overview
    </a>

    {{-- 2. CREATE ORDER (Managers Only) --}}
    @can('manage_banquet')
    <a href="{{ route('banquet.orders.create') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.orders.create') ? 'active' : '' }}" 
       style="color: #ddd; border: none; padding-left: 3rem;">
        New Event
    </a>
    @endcan

    {{-- 3. REPORTS --}}
    <a href="{{ route('banquet.reports.form') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.reports.*') ? 'active' : '' }}" 
       style="color: #ddd; border: none; padding-left: 3rem;">
        Reports
    </a>
</div>
@endcan