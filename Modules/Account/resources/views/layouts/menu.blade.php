<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#accountSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('account.*') ? 'true' : 'false' }}" aria-controls="accountSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-user-shield fa-fw me-3"></i> Account</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('account.*') ? 'show' : '' }}" id="accountSubmenu">
    <a href="{{ route('account.invoice') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account.invoice') ? 'active' : '' }}" style="color: #ddd; border: none;">Invoices</a>
</div>
<div class="collapse {{ request()->routeIs('account.*') ? 'show' : '' }}" id="accountSubmenu">
    <a href="{{ route('account.order') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account.order') ? 'active' : '' }}" style="color: #ddd; border: none;">Orders</a>
</div>
