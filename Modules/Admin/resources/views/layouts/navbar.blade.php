<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div style="display: inline-block; padding: 10px 20px;  border-radius: 8px; background: linear-gradient(145deg, #f0f0f0, #dcdcdc);box-shadow: 4px 4px 10px rgba(0,0,0,0.2), -4px -4px 10px rgba(255,255,255,0.6);  border: 1px solid #e0e0e0;  transform: perspective(600px) rotateX(2deg);transition: transform 0.3s ease, box-shadow 0.3s ease;"
            class="px-2 ml-3">
            <a href="home" style="font-weight: bold; font-size: 1.2rem;   color: #333;  text-decoration: none; ">
                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub> <sub style="font-size:8pt;">v1.0</sub>
            </a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-pills">
                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('staff.dashboard', 'admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i> Home
                    </a>

                </li>
                <li class="nav-item ">

                    <a class="nav-link {{ request()->routeIs('staff.leaves.*') ? 'active' : '' }} disabled"
                        href="{{ route('staff.leaves.index') }}"><i class="fas fa-calendar-alt me-2"></i> My Leaves

                    </a>

                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}"
                        href="{{ route('tasks.index') }}">
                        <i class="fa fa-list-alt me-1"></i> Tasks
                    </a>
                </li>
                @can('staff-view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.*') && !request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                            href="{{ route('staff.index') }}">
                            <i class="fa fa-users me-1"></i> Staff list
                        </a>
                    </li>
                @endcan
                @can('manage-user')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-1"></i> Manage Users
                        </a>
                    </li>
                @endcan

                @can('manage-roles-permission')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}"
                            href="{{ route('admin.permissions.index') }}">
                            <i class="fas fa-key me-1"></i> Manage Permissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}"
                            href="{{ route('admin.roles.index') }}">
                            <i class="fas fa-user-tag me-1"></i> Manage Roles
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}"
                        href="{{ route('maintenance.index') }}">
                        <i class="fa fa-tools me-1"></i> Maintenance log
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('banquet.orders.*') ? 'active' : '' }}"
                        href="{{ route('banquet.orders.index') }}">
                        <i class="fa fa-users me-1"></i>Banquet
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gym.*') ? 'active' : '' }}"
                        href="{{ route('gym.index') }}">
                        <i class="fas fa-dumbbell me-1"></i>Gym
                    </a>
                </li>

            </ul>
            <div id="liveClock" style=" color: #fff;font-weight: bold;font-family: 'Courier New', monospace; font-size: 1rem;  padding: 6px 12px;border-radius: 6px;  background: linear-gradient(145deg, #1e1e1e, #2c2c2c);  box-shadow: inset 1px 1px 3px rgba(255,255,255,0.1), inset -1px -1px 3px rgba(0,0,0,0.5);margin-left: 15px;  min-width: 120px;  text-align: center;">
                --
            </div>


            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i
                                        class="fas fa-user-alt me-2"></i>My Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            <li><a class="dropdown-item" href="{{ route('staff.leaves.index') }}"><i
                                        class="fas fa-calendar-alt me-2"></i> My Leaves</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>
                                        Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<script>
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12 || 12; // Convert to 12-hour format

        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
        document.getElementById('liveClock').textContent = timeString;
    }

    setInterval(updateClock, 1000);
    updateClock(); // initial call
</script>
