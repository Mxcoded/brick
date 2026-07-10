<nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom py-2">
    <div class="container-fluid">
        <!-- Sidebar Toggle -->
        <button class="btn btn-outline-dark me-3 shadow-sm" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
            <div class="d-inline-flex align-items-center gap-2">
                @if($logoSetting ?? null)
                    <img src="{{ Storage::url($logoSetting) }}" alt="Logo" style="height: 40px; width: auto; object-fit: contain;">
                @endif
                <span class="fw-bold" style="font-size: 1.3rem; color: var(--sidebar-brand); letter-spacing: -0.5px;">
                    BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub>
                </span>
            </div>
        </a>

        <!-- Right Side -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Theme Toggle -->
            <li class="nav-item me-3">
                <button class="btn theme-toggle d-inline-flex align-items-center gap-2 border-0" id="theme-toggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode">
                    <span class="theme-toggle-track">
                        <span class="theme-toggle-thumb d-flex align-items-center justify-content-center">
                            <i class="fas fa-sun" id="theme-icon-sun"></i>
                            <i class="fas fa-moon d-none" id="theme-icon-moon"></i>
                        </span>
                    </span>
                    <span class="theme-toggle-label small fw-semibold" id="theme-label">Light</span>
                </button>
            </li>

            <!-- Live Clock -->
            <li id="liveClock" class="nav-item me-3 text-dark fw-semibold" style="font-family: 'Courier New', monospace; color: #333333;">
                --
            </li>

            <!-- User Dropdown -->
            @auth
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #333333;">
                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-alt me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('staff.leaves.index') }}"><i class="fas fa-calendar-alt me-2"></i>My Leaves</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
            @endauth
        </ul>
    </div>
</nav>
<script>
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>