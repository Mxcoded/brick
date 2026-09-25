<?php

namespace Modules\Admin\Filament\Pages;

use App\Support\ThemeSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Models\Settings;

class Appearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'admin::filament.pages.appearance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'Appearance';

    protected static ?string $title = 'Appearance';

    protected static ?string $slug = 'appearance';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'theme' => ThemeSettings::theme(),
            'logo' => ThemeSettings::logo(),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Colour theme')
                    ->description('Pick the colour palette used across the ERP. Selecting a card previews it live.')
                    ->schema([
                        ViewField::make('theme')
                            ->view('admin::filament.components.theme-selector')
                            ->rules(['required', 'in:gold-legacy,platinum-noir,sapphire-regal']),
                    ]),
                Section::make('Brand logo')
                    ->description('Logo shown in the sidebar and login screen.')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Brand mark')
                            ->disk('public')
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml'])
                            ->maxSize(2048)
                            ->imagePreviewHeight(60)
                            ->helperText('PNG, JPG or SVG — max 2MB. The preview on the right updates after you save.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $data['theme'], 'type' => 'string']
        );
        cache()->forget('app.theme');

        if (filled($data['logo'])) {
            Settings::updateOrCreate(
                ['key' => 'logo'],
                ['value' => $data['logo'], 'type' => 'image']
            );
            cache()->forget('app.logo');
        }

        Notification::make()
            ->success()
            ->title('Appearance updated successfully.')
            ->body('The new theme is now live across the portal.')
            ->send();
    }

    public function removeLogo(): void
    {
        $logo = Settings::where('key', 'logo')->first();
        if ($logo) {
            Storage::disk('public')->delete($logo->value);
            $logo->delete();
            cache()->forget('app.logo');
        }

        $this->form->fill(['logo' => null]);

        Notification::make()
            ->success()
            ->title('Logo removed successfully.')
            ->send();
    }

    public function logoExists(): bool
    {
        return (bool) Settings::where('key', 'logo')->value('value');
    }

    public function logoUrl(): string
    {
        $logo = ThemeSettings::logo();

        return filled($logo) ? Storage::disk('public')->url($logo) : '';
    }

    public function hasUnsavedThemeChanges(): bool
    {
        return (($this->data['theme'] ?? null) !== null)
            && ($this->data['theme'] !== ThemeSettings::theme());
    }

    public function savedTheme(): string
    {
        return ThemeSettings::theme();
    }

    public function savedThemeIsDark(): bool
    {
        return ThemeSettings::isDark();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_settings') ?? false;
    }
}
