<div class="col-lg-3">
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body text-center py-5 bg-light">
            <div class="avatar-circle mx-auto mb-3 shadow-sm d-flex align-items-center justify-content-center text-white fw-bold fs-3"
                style="width: 80px; height: 80px; background: linear-gradient(135deg, #1a1a1a 0%, #444 100%); border-radius: 50%;">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <h5 class="mb-1 fw-bold text-dark">{{ Auth::user()->name }}</h5>
            <p class="text-muted small mb-0">{{ Auth::user()->email }}</p>
        </div>
        <div class="list-group list-group-flush guest-sidebar-nav">
            <a href="{{ route('guest.dashboard') }}"
                class="list-group-item list-group-item-action py-3 px-4 border-0 
               {{ ($active ?? 'dashboard') === 'dashboard' ? 'active-link' : '' }}">
                <i class="fas fa-th-large me-3 sidebar-icon"></i> Dashboard
            </a>

            <a href="{{ route('guest.bookings') }}"
                class="list-group-item list-group-item-action py-3 px-4 border-0 
               {{ ($active ?? 'dashboard') === 'bookings' ? 'active-link' : '' }}">
                <i class="fas fa-calendar-check me-3 sidebar-icon"></i> My Bookings
            </a>

            <a href="{{ route('guest.profile') }}"
                class="list-group-item list-group-item-action py-3 px-4 border-0 
               {{ ($active ?? 'dashboard') === 'profile' ? 'active-link' : '' }}">
                <i class="fas fa-user-cog me-3 sidebar-icon"></i> My Profile
            </a>

            <form action="{{ route('logout') }}" method="POST" class="d-block border-top">
                @csrf
                <button type="submit"
                    class="list-group-item list-group-item-action py-3 px-4 border-0 text-danger w-100 text-start">
                    <i class="fas fa-sign-out-alt me-3 sidebar-icon"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
