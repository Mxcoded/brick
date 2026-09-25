<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <div class="flex items-center justify-end gap-3">
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
</x-filament-panels::page>