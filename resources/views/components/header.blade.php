<header
    class="sticky top-0 z-50 bg-card/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-colors duration-500">
    <nav class="container mx-auto flex items-center justify-between py-4 px-4">

        <!-- Branding -->
        <div class="flex items-center space-x-3">
            <div
                class="rounded-xl px-5 py-3 border border-[var(--glass-border)] bg-[var(--glass-effect)] shadow-[4px_4px_15px_rgba(228,149,4,0.5),-4px_-4px_15px_rgba(255,255,255,0.1)] transform perspective-[600px] rotate-x-[2deg] transition-[var(--transition)]">
                <a href="/home"
                    class="font-extrabold text-[1.4rem] text-[var(--text-primary)] no-underline tracking-tight text-shadow-sm">
                    BRICKSPOINT<sup>&trade;</sup><sub class="text-xs">ERP</sub> <sub class="text-[8pt]">v1.0</sub>
                </a>
            </div>
        </div>

        <!-- Navigation Links -->
        <div
            class="hidden md:flex flex-1 justify-center space-x-10 text-[18px] font-bold text-[var(--text-primary)]">
            <a href="#overview" class="relative group hover:text-[var(--accent-color)] transition">
                Overview
                <span
                    class="absolute left-0 -bottom-1 w-0 h-[2px] bg-[var(--accent-color)] transition-all duration-300 group-hover:w-full"></span>
            </a>

            <a href="#modules" class="relative group hover:text-[var(--accent-color)] transition">
                Modules
                <span
                    class="absolute left-0 -bottom-1 w-0 h-[2px] bg-[var(--accent-color)] transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#contact" class="relative group hover:text-[var(--accent-color)] transition">
                Contact
                <span
                    class="absolute left-0 -bottom-1 w-0 h-[2px] bg-[var(--accent-color)] transition-all duration-300 group-hover:w-full"></span>
            </a>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-4">
            <!-- Theme toggle -->
            <button id="theme-toggle"
                class="text-xl text-gray-600 dark:text-gray-400 hover:text-[var(--accent-color)] transition">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Mobile menu -->
            <button class="md:hidden text-xl text-gray-600 dark:text-gray-400 focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Auth links -->
            @if (Route::has('login'))
                <div class="hidden md:flex items-center space-x-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-4 py-2 rounded-full text-sm font-semibold ring-1 ring-gray-900 hover:var(--accent-color) hover:text-white dark:text-gray-200 dark:ring-gray-200 dark:hover:bg-gray-200 dark:hover:text-gray-900 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 rounded-full text-sm font-semibold bg-gray-900 text-white hover:var(--accent-color) dark:bg-gray-200 dark:text-gray-900 dark:hover:var(--accent-color) transition">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-4 py-2 rounded-full text-sm font-semibold bg-gray-900 text-white hover:var(--accent-color) dark:bg-gray-200 dark:text-gray-900 dark:hover:var(--accent-color) transition">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>
</header>
