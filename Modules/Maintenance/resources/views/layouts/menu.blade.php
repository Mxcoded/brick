<a href="{{ route('maintenance.index') }}"
   class="list-group-item list-group-item-action p-3 {{ request()->routeIs('maintenance.*') ? 'active' : '' }}"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <i class="fas fa-tools fa-fw me-3"></i> Maintenance
</a>