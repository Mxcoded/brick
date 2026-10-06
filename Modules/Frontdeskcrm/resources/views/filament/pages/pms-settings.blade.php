{{-- The PMS settings form, as a page body.

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
</x-filament-panels::page>