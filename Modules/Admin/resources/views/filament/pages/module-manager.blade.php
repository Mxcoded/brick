<x-filament-panels::page>
    <x-filament::section :heading="count($this->modules).' Modules Installed'">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="border-b text-xs font-semibold uppercase tracking-wide text-gray-400 dark:border-white/10 dark:text-gray-500">
                    <tr>
                        <th class="py-3">Module</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Version</th>
                        <th class="py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($this->modules as $module)
                        <tr>
                            <td class="py-3 pr-4 font-semibold text-gray-950 dark:text-white">
                                {{ $module->getName() }}
                                @if ($module->getDescription())
                                    <div class="mt-0.5 text-xs font-normal text-gray-500 dark:text-gray-400">
                                        {{ $module->getDescription() }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                @if ($module->isEnabled())
                                    <x-filament::badge color="success">Enabled</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray">Disabled</x-filament::badge>
                                @endif
                            </td>
                            <td class="py-3">{{ $module->get('version', '—') }}</td>
                            <td class="py-3 text-right">
                                @if ($module->getName() === 'Admin')
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Required</span>
                                @elseif ($module->isEnabled())
                                    <x-filament::button
                                        color="danger"
                                        size="sm"
                                        outlined
                                        wire:click="toggleModule('{{ $module->getName() }}')"
                                        wire:confirm="Disable {{ $module->getName() }}? This will hide all its routes and menus."
                                    >
                                        Disable
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        color="success"
                                        size="sm"
                                        outlined
                                        wire:click="toggleModule('{{ $module->getName() }}')"
                                    >
                                        Enable
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>