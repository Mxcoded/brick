<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Welcome, {{ auth()->user()->name }}
        </p>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            Where would you like to go?
        </h1>
    </div>

    @forelse ($destinationGroups as $group)
        <div class="mb-6">
            <h2 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ $group['group'] }}
            </h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($group['links'] as $link)
                    <a href="{{ $link['url'] }}"
                       class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:bg-white/5 dark:ring-primary-500/10">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $link['label'] }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-6 text-sm text-gray-600 shadow-sm ring-1 ring-primary-100/50 dark:bg-white/5 dark:text-gray-300 dark:ring-primary-500/10">
            You do not have access to any module areas yet. Contact an administrator if that looks wrong.
        </div>
    @endforelse
</x-filament-panels::page>
