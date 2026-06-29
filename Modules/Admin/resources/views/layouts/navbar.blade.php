<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container-fluid">
        <button class="btn btn-primary" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        
        <div class="collapse navbar-collapse"></div>

        <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
            <li class="nav-item me-2">
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
            <li id="liveClock" class="nav-item me-3" style="font-weight: bold; font-family: 'Courier New', monospace;">
                --
            </li>
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-alt me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.leaves.index') }}"><i class="fas fa-calendar-alt me-2"></i> My Leaves</a></li>
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