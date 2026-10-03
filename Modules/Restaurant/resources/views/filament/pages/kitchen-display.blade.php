{{-- Kitchen display: order cards, not a table.

     The alarm is the part worth caring about. Kitchen staff work with their back
     to the screen, so an order that arrives silently is an order that sits until
     someone walks over. The shared alarm in public/js/kds-alarm.js rings every
     ten seconds for as long as anything is waiting to be accepted, and stops on
     its own once the column is clear. --}}
<x-filament-panels::page>
    {{-- Projection styling. The board is often shown on a wall-mounted screen
         with no one at the keyboard, so fullscreen enlarges the ticket text and
         lets the grid use the whole panel. --}}
    <style>
        .kds-board:fullscreen {
            overflow-y: auto;
            padding: 1.5rem;
            background: #ffffff;
        }

        .dark .kds-board:fullscreen {
            background: #111827;
        }

        .kds-board:fullscreen article {
            font-size: 1.05rem;
        }

        .kds-board:fullscreen article .text-lg {
            font-size: 1.75rem;
        }
    </style>

    <div
        x-data="kdsBoard"
        x-ref="board"
        x-effect="watch(@js($this->pendingIds()))"
        class="kds-board"
    >
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Live tickets. This board refreshes itself every 15 seconds.
            </p>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="muted = alarm.toggleMute()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <span x-text="muted ? 'Alarm off' : 'Alarm on'"></span>
                </button>

                <button
                    type="button"
                    @click="toggleFullscreen()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <span x-text="fullscreen ? 'Exit fullscreen' : 'Fullscreen'"></span>
                </button>

                <x-filament::badge color="gray" icon="heroicon-m-signal">
                    <span wire:offline>Disconnected</span>
                    <span wire:online>Live</span>
                </x-filament::badge>
            </div>
        </div>

        <div wire:poll.15s wire:key="kds-board" class="grid gap-4 lg:grid-cols-3">
            @forelse ($this->board() as $column => $meta)
                <section class="flex flex-col gap-3">
                    <header class="flex items-center gap-2">
                        <x-filament::icon
                            :icon="$meta['icon']"
                            class="h-5 w-5 text-gray-500 dark:text-gray-400"
                        />
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $meta['label'] }}
                        </h2>
                        <x-filament::badge color="gray" size="sm">
                            {{ $meta['orders']->count() }}
                        </x-filament::badge>
                    </header>

                    <div class="flex min-h-32 flex-col gap-3">
                        @forelse ($meta['orders'] as $order)
                            @php
                                $stations = $order->orderItems
                                    ->map(fn ($item) => $this->stationFor($item->menuItem?->category?->name))
                                    ->unique()
                                    ->values();
                                $ageInMinutes = $order->created_at?->diffInMinutes(now()) ?? 0;
                            @endphp

                            <article @class([
                                'rounded-lg border bg-white p-4 shadow-xs dark:bg-gray-900',
                                'border-danger-300 dark:border-danger-700' => $column === 'pending',
                                'border-primary-300 dark:border-primary-700' => $column === 'preparing',
                                'border-success-300 dark:border-success-700' => $column === 'ready',
                            ])>
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-lg font-bold text-gray-950 dark:text-white">
                                        #{{ $order->id }}
                                    </span>

                                    <div class="flex flex-wrap justify-end gap-1">
                                        @foreach ($stations as $station)
                                            <x-filament::badge
                                                color="gray"
                                                size="sm"
                                            >{{ $station }}</x-filament::badge>
                                        @endforeach
                                    </div>
                                </div>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->customer_name ?: 'Table '.($order->source_id ?? '—') }}
                                    &middot;
                                    <span @class([
                                        'font-medium',
                                        'text-danger-600 dark:text-danger-400' => $ageInMinutes >= 20,
                                    ])>{{ $ageInMinutes }}m ago</span>
                                </p>

                                <ul class="mt-3 space-y-1 text-sm">
                                    @foreach ($order->orderItems as $item)
                                        <li class="flex items-start justify-between gap-2">
                                            <span class="text-gray-700 dark:text-gray-300">
                                                <span class="mr-1 text-xs text-gray-400">
                                                    {{ $this->stationFor($item->menuItem?->category?->name) }}
                                                </span>
                                                {{ $item->menuItem?->name ?? 'Item' }}
                                                @if ($item->instructions)
                                                    <span class="block text-xs italic text-warning-700 dark:text-warning-400">
                                                        {{ $item->instructions }}
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="shrink-0 font-semibold text-gray-950 dark:text-white">
                                                &times;{{ $item->quantity }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($this->canUpdate())
                                    <div class="mt-3">
                                        @if ($column === 'pending')
                                            <x-filament::button
                                                size="sm"
                                                color="success"
                                                icon="heroicon-m-check-badge"
                                                wire:click="accept({{ $order->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="accept({{ $order->id }})"
                                                class="w-full"
                                            >
                                                Accept order
                                            </x-filament::button>
                                        @elseif ($column === 'preparing')
                                            <x-filament::button
                                                size="sm"
                                                color="primary"
                                                icon="heroicon-m-check"
                                                wire:click="markReady({{ $order->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="markReady({{ $order->id }})"
                                                class="w-full"
                                            >
                                                Ready to serve
                                            </x-filament::button>
                                        @else
                                            <x-filament::button
                                                size="sm"
                                                color="gray"
                                                icon="heroicon-m-hand-raised"
                                                wire:click="markServed({{ $order->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="markServed({{ $order->id }})"
                                                class="w-full"
                                            >
                                                Served
                                            </x-filament::button>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-400 dark:border-gray-700">
                                Nothing here.
                            </p>
                        @endforelse
                    </div>
                </section>
            @empty
                <p class="text-sm text-gray-500">No columns to show.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/kds-alarm.js') }}"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                window.kdsBoard = () => ({
                    alarm: window.createKdsAlarm({ intervalMs: 10000 }),
                    muted: false,
                    fullscreen: false,

                    init() {
                        // Reflect a mute carried over from a previous shift, so
                        // the button agrees with what the alarm will actually do.
                        this.muted = this.alarm.isMuted();

                        // Fullscreening a single element hides the panel chrome
                        // around it, so the kitchen gets the board and nothing
                        // else. Keep the label in step when Esc is used instead.
                        document.addEventListener('fullscreenchange', () => {
                            this.fullscreen = document.fullscreenElement === this.$refs.board;
                        });
                    },

                    /**
                     * Project the board: put just this element fullscreen, so the
                     * sidebar and topbar drop away without any layout CSS.
                     */
                    async toggleFullscreen() {
                        try {
                            if (document.fullscreenElement) {
                                await document.exitFullscreen();
                            } else {
                                await this.$refs.board.requestFullscreen();
                            }
                        } catch (error) {
                            // Denied by the browser or unsupported. Leaving the
                            // board as it is makes a fine fallback.
                        }
                    },

                    /**
                     * Keep the alarm in step with the pending column. Rung for as
                     * long as there is something waiting to be accepted, silent
                     * the moment there is not.
                     */
                    watch(pendingIds) {
                        this.alarm.setPendingCount(pendingIds.length);
                    },
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
