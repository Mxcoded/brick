@php
    use App\Support\ThemeSettings;

    $themes = ThemeSettings::THEMES;
@endphp

<div
    x-data="{ theme: $wire.$entangle('{{ $getStatePath() }}', true) }"
    class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
>
    @foreach ($themes as $slug => $meta)
        <button
            type="button"
            wire:key="theme-option-{{ $slug }}"
            x-on:click.prevent="
                theme = '{{ $slug }}';
                document.documentElement.dataset.theme = '{{ $slug }}';
                document.documentElement.classList.toggle('dark', {{ $meta['dark'] ? 'true' : 'false' }});
            "
            x-bind:class="{
                'ring-2 ring-primary-500 ring-offset-2 border-transparent': theme === '{{ $slug }}',
                'border-gray-200 dark:border-gray-700 hover:border-primary-500/60': theme !== '{{ $slug }}',
            }"
            x-bind:aria-pressed="theme === '{{ $slug }}'"
            aria-pressed="false"
            class="group relative cursor-pointer rounded-xl border bg-white text-left shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:bg-gray-900"
        >
            <span
                x-show="theme === '{{ $slug }}'"
                x-cloak
                class="absolute end-3 top-3 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-primary-500 text-white shadow"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </span>

            <span class="block overflow-hidden rounded-t-xl border-b border-gray-200 dark:border-gray-700">
                <span class="block h-10" style="background-color: {{ $meta['accent'] }}; opacity: 0.15;"></span>
                <span class="flex h-24">
                    <span class="w-8" style="background-color: {{ $meta['sidebar'] }};"></span>
                    <span class="flex-1 p-2" style="background-color: {{ $meta['body'] }};">
                        <span class="mb-2 block h-3 w-20 rounded-full" style="background-color: {{ $meta['accent'] }}; opacity: 0.9;"></span>
                        <span class="mb-1 block h-1.5 w-full rounded-full" style="background-color: {{ $meta['accent'] }}; opacity: 0.25;"></span>
                        <span class="block h-1.5 w-2/3 rounded-full" style="background-color: {{ $meta['accent'] }}; opacity: 0.25;"></span>
                    </span>
                </span>
            </span>

            <span class="block p-4">
                <span class="block font-medium text-gray-900 dark:text-white">{{ $meta['label'] }}</span>
                <span class="mt-1 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                    {{ $meta['description'] ?? '' }}
                </span>
            </span>
        </button>
    @endforeach
</div>

@if (isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag && $errors->has($getStatePath()))
    <p data-validation-error class="mt-2 text-sm text-danger-600 dark:text-danger-400">
        {{ $errors->first($getStatePath()) }}
    </p>
@endif