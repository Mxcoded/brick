<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <div class="flex items-center justify-end">
        <x-filament::button wire:click="save">
            Create User
        </x-filament::button>
    </div>
</x-filament-panels::page>