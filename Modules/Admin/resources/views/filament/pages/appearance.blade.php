<x-filament-panels::page>
    @once
        @push('scripts')
            <script>
                window.brickspointThemePreview = (slug) => ({
                    'gold-legacy': { bg: '#f0f2f5', sidebar: '#1e1e2d', accent: '#C8A165', surface: '#ffffff', text: '#333333' },
                    'platinum-noir': { bg: '#121212', sidebar: '#0D0D0D', accent: '#E8E8E8', surface: '#1e1e1e', text: '#e0e0e0' },
                    'sapphire-regal': { bg: '#f0f2f5', sidebar: '#0F1A2E', accent: '#1B3A5C', surface: '#ffffff', text: '#2d3748' },
                })[slug];

                window.brickspointThemeLabel = (slug) => ({
                    'gold-legacy': 'Gold Legacy',
                    'platinum-noir': 'Platinum Noir',
                    'sapphire-regal': 'Sapphire Regal',
                })[slug];
            </script>
        @endpush
    @endonce

    @if ($this->hasUnsavedThemeChanges())
        <x-filament::section color="warning" compact class="!fi-ring-0">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="mt-0.5 h-5 w-5 text-warning-500" />
                <div>
                    <span class="font-medium text-gray-900 dark:text-white">Unsaved theme change.</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        You are previewing a theme that has not been saved yet — save to apply it permanently, or
                        <button
                            type="button"
                            class="font-medium text-primary-600 underline decoration-primary-300 underline-offset-2 hover:text-primary-700 dark:text-primary-400"
                            x-data
                            x-on:click="
                                $wire.set('data.theme', @js($this->savedTheme()), false);
                                document.documentElement.dataset.theme = @js($this->savedTheme());
                                document.documentElement.classList.toggle('dark', @js($this->savedThemeIsDark()));
                            "
                        >
                            discard the changes
                        </button>.
                    </span>
                </div>
            </div>
        </x-filament::section>
    @endif

    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button
                    color="gray"
                    size="sm"
                    outlined
                    x-data
                    x-on:click="
                        $wire.set('data.theme', @js($this->savedTheme()), false);
                        document.documentElement.dataset.theme = @js($this->savedTheme());
                        document.documentElement.classList.toggle('dark', @js($this->savedThemeIsDark()));
                    "
                >
                    Discard Preview
                </x-filament::button>

                @if ($this->logoExists())
                    <x-filament::button
                        color="danger"
                        size="sm"
                        outlined
                        wire:click="removeLogo"
                        wire:confirm="Remove the current logo?"
                    >
                        Remove Logo
                    </x-filament::button>
                @endif

                <x-filament::button wire:click="save">
                    Save Changes
                </x-filament::button>
            </div>
        </div>

        <div class="xl:sticky xl:top-6">
            <x-filament::section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Live preview</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium text-white"
                        x-data="{ theme: $wire.$entangle('data.theme', true) }"
                        x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent};`"
                        x-text="theme ? (window.brickspointThemeLabel(theme) ?? 'Gold Legacy') : 'Gold Legacy'"
                    >
                        Gold Legacy
                    </span>
                </div>

                <div x-data="{ theme: $wire.$entangle('data.theme', true) }">
                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700"
                        x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { bg: '#f0f2f5' }).bg};`"
                    >
                        <div
                            class="h-2"
                            x-bind:style="`background: linear-gradient(90deg, ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent}, ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent}cc);`"
                        ></div>

                        <div class="flex h-64">
                            <div
                                class="flex w-16 flex-col gap-2 border-e border-black/5 p-2"
                                x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { sidebar: '#1e1e2d' }).sidebar};`"
                            >
                                <span class="h-2 w-8 rounded-full" x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent};`"></span>
                                <span class="h-1.5 w-10 rounded-full bg-white/20"></span>
                                <span class="h-1.5 w-9 rounded-full bg-white/20"></span>
                                <span class="h-1.5 w-7 rounded-full bg-white/20"></span>
                            </div>

                            <div class="flex-1 p-3" x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { surface: '#ffffff' }).surface};`">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="h-2.5 w-24 rounded-full" x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent};`"></span>
                                    <span class="h-4 w-28 rounded-md border border-black/5"></span>
                                </div>
                                <div class="mt-2 space-y-1.5">
                                    <span class="block h-1.5 w-3/4 rounded-full bg-gray-300/60 dark:bg-gray-700/60"></span>
                                    <span class="block h-1.5 w-1/2 rounded-full bg-gray-300/60 dark:bg-gray-700/60"></span>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <span
                                        class="h-7 rounded-lg"
                                        x-bind:style="`background-color: ${(window.brickspointThemePreview(theme ?? 'gold-legacy') ?? { accent: '#C8A165' }).accent};`"
                                    ></span>
                                    <span class="h-7 rounded-lg border border-black/10"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                        The preview simulates the login screen. Your selection is applied to the entire portal in
                        real time until you save or discard it.
                    </p>

                    @if ($this->logoUrl() !== '')
                        <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                            <span class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">Brand logo</span>
                            <img src="{{ $this->logoUrl() }}" alt="Brand logo" class="h-8 w-auto object-contain" />
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>