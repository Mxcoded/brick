<?php

namespace Modules\Admin\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
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
            'theme' => Settings::where('key', 'theme')->value('value') ?? 'gold-legacy',
            'logo' => Settings::where('key', 'logo')->value('value'),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Theme')
                    ->description('Pick the colour palette used across the ERP.')
                    ->schema([
                        Radio::make('theme')
                            ->label('Colour theme')
                            ->options([
                                'gold-legacy' => 'Gold Legacy',
                                'platinum-noir' => 'Platinum Noir',
                                'sapphire-regal' => 'Sapphire Regal',
                            ])
                            ->descriptions([
                                'gold-legacy' => 'Warm gold accents on deep navy — the signature Brickspoint look.',
                                'platinum-noir' => 'Monochromatic silver-on-black for a sleek, ultra-modern aesthetic.',
                                'sapphire-regal' => 'Deep sapphire blue with refined gold trim — timeless corporate luxury.',
                            ])
                            ->default('gold-legacy')
                            ->rules(['in:gold-legacy,platinum-noir,sapphire-regal'])
                            ->required(),
                    ]),
                Section::make('Brand Logo')
                    ->description('Logo shown in the sidebar and login screen.')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Brand mark')
                            ->disk('public')
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml'])
                            ->maxSize(2048)
                            ->imagePreviewHeight(60)
                            ->helperText('PNG, JPG or SVG — max 2MB'),
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

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_settings') ?? false;
    }
}
