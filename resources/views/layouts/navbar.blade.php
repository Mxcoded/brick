<nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom py-2">
    <div class="container-fluid">
        <!-- Sidebar Toggle -->
        <button class="btn btn-outline-dark me-3 shadow-sm" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand -->
        <div
            style="display: inline-block; padding: 10px 20px;   border-radius: 12px; background: var(--glass-effect); border: 1px solid var(--glass-border);
                box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2), 
                            -4px -4px 15px rgba(255, 255, 255, 0.1); transform: perspective(600px) rotateX(2deg); transition: var(--transition); margin-right: 15px;">

            <a href="home"
                style="
                font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
                font-weight: 800;
                font-size: 1.4rem;
                color: #C8A165;
                text-decoration: none;
                letter-spacing: -0.5px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            ">
                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub> <sub style="font-size:8pt;">v1.0</sub>
            </a>
        </div>

        <!-- Right Side -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Theme Toggle -->
            <li class="nav-item me-3">
                <button class="btn btn-outline-secondary" id="theme-toggle" type="button">
                    <i class="fas fa-sun" id="theme-icon-sun"></i>
                    <i class="fas fa-moon d-none" id="theme-icon-moon"></i>
                </button>
            </li>

            @auth
            @php
                $currentProperty = app(\App\Services\PropertyService::class)->current();
                $userProperties = Auth::user()->properties()->active()->get();
            @endphp
            @if($userProperties->count() > 0)
            <li class="nav-item dropdown me-3">
                <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #333;">
                    <i class="fas fa-building me-1"></i>
                    {{ $currentProperty?->name ?? 'Select Property' }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    @foreach($userProperties as $property)
                    <li>
                        <form action="{{ route('frontdesk.properties.switch', $property) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item {{ $currentProperty?->id === $property->id ? 'active' : '' }}">
                                <i class="fas fa-check-circle me-2 {{ $currentProperty?->id === $property->id ? '' : 'invisible' }}"></i>
                                {{ $property->name }}
                                @if($property->code)<small class="text-muted ms-1">({{ $property->code }})</small>@endif
                            </button>
                        </form>
                    </li>
                    @endforeach
                    @can('access_frontdesk_dashboard')
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('frontdesk.properties.index') }}"><i class="fas fa-cog me-2"></i>Manage Properties</a></li>
                    @endcan
                </ul>
            </li>
            @endif
            @endauth

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