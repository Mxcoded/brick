{{-- The settings form, as a page body.

     Filament's default page view renders `{{ $this->content }}` and nothing
     else, so a Page carrying a form needs a view of its own or it opens blank:
     the form exists, validates, and is completely invisible. --}}
<x-filament-panels::page>
    <form
        wire:submit="save"
        class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
    >
        {{ $this->form }}

        <div class="mt-4 flex flex-wrap items-center gap-3">
            {{-- Doubles the header action, so saving works from the body of the
                 form as well as from the top of the page. --}}
            <x-filament::button type="submit" icon="heroicon-m-check" :disabled="! $this->canEdit()">
                Save settings
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-m-arrow-uturn-left"
                wire:click="resetForm"
                :disabled="! $this->canEdit()"
            >
                Discard changes
            </x-filament::button>
        </div>
    </form>

    {{-- Outside the form on purpose: this navigates rather than submits, and
         putting a link inside would make it look like another setting. --}}
    @if ($this->salesPointsUrl())
        <div class="fi-section mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Sales points</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{-- A property with one counter needs nothing here, so the count
                     is spelled out rather than left as an unexplained number. --}}
                @if ($this->salesPointsCount() === 1)
                    One sales point is configured.
                @else
                    {{ $this->salesPointsCount() }} sales points are configured.
                @endif
                Give a counter its own revenue account to see it separately in the
                sales report and the ledger.
            </p>

            <x-filament::button
                tag="a"
                href="{{ $this->salesPointsUrl() }}"
                icon="heroicon-m-building-storefront"
                color="gray"
                class="mt-3"
            >
                Manage sales points
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
