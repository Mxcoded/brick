<?php

namespace Modules\Admin\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Nwidart\Modules\Facades\Module;

class ModuleManager extends Page
{
    protected string $view = 'admin::filament.pages.module-manager';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Modules';

    protected static ?string $title = 'Module Manager';

    protected static ?string $slug = 'modules';

    #[Computed]
    public function modules(): array
    {
        return Module::all();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.update') ?? false;
    }

    public function toggleModule(string $name): void
    {
        $module = Module::find($name);
        if (! $module) {
            Notification::make()
                ->danger()
                ->title("Module '{$name}' not found.")
                ->send();

            return;
        }

        if ($name === 'Admin' && $module->isEnabled()) {
            Notification::make()
                ->warning()
                ->title('The Admin module is required and cannot be disabled.')
                ->send();

            return;
        }

        if ($module->isEnabled()) {
            $module->disable();
            Notification::make()
                ->success()
                ->title("Module '{$name}' disabled.")
                ->send();
        } else {
            $module->enable();
            Notification::make()
                ->success()
                ->title("Module '{$name}' enabled.")
                ->send();
        }
    }
}
