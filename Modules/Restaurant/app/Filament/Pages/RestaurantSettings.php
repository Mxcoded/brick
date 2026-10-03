<?php

namespace Modules\Restaurant\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Restaurant\Filament\Resources\SalesPoints\SalesPointResource;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\SalesPoint;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Restaurant settings as a single form, not a key/value resource.
 *
 * The table is `restaurant_settings(key, value)`, so a Filament Resource would
 * present it as a list of opaque strings and let someone rename a key. These
 * settings are not arbitrary: the POS, the online checkout and the receipts all
 * read these exact keys, so renaming or adding one silently changes what an
 * invoice means. A fixed form keeps the key set closed.
 *
 * The values here also drive the order totals computed by the POS, which is why
 * editing is gated on settings.update rather than being part of the general
 * back-office permission.
 */
class RestaurantSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'restaurant/settings';

    protected static ?string $title = 'Restaurant settings';

    protected string $view = 'restaurant::filament.pages.settings';

    /**
     * The settings this screen owns, with the type they are stored as.
     *
     * Kept here rather than discovered from the table so that a stray row added
     * by something else cannot appear as an editable form field.
     *
     * @var array<string, string>
     */
    protected const KEYS = [
        'vat_rate' => 'number',
        'service_charge_rate' => 'number',
        'discount_limit' => 'number',
        'shift_start_time' => 'time',
        'shift_end_time' => 'time',
    ];

    /**
     * Form state, keyed by `statePath('settings')` below.
     *
     * Public on purpose: Filament resolves a state path against the Livewire
     * component's public properties, so this cannot be protected.
     */
    public ?array $settings = [];

    public static function canAccess(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::SETTINGS_READ);
    }

    public function canEdit(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::SETTINGS_UPDATE);
    }

    public function mount(): void
    {
        $this->form->fill($this->currentValues());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Charges')
                    ->description('Applied when the POS and the online checkout build an order total.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('vat_rate')
                            ->label('VAT rate (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->helperText('Percentage, not a fraction.'),

                        TextInput::make('service_charge_rate')
                            ->label('Service charge (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->helperText('Percentage, not a fraction.'),

                        TextInput::make('discount_limit')
                            ->label('Discount ceiling')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->helperText('Largest discount a waiter may apply without a manager.'),
                    ]),

                Section::make('Shifts')
                    ->description('Used by shift reporting and the waiter handover.')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('shift_start_time')
                            ->label('Shift starts')
                            ->required()
                            ->seconds(false),

                        TimePicker::make('shift_end_time')
                            ->label('Shift ends')
                            ->required()
                            ->seconds(false)
                            // Same rule as the legacy validation: an inverted
                            // shift would silently produce empty shift reports.
                            ->after('shift_start_time'),
                    ]),
            ])
            ->disabled(! $this->canEdit())
            ->statePath('settings');
    }

    /**
     * Saved values, falling back to something sensible so the form never opens
     * blank on a fresh install.
     *
     * @return array<string, string>
     */
    protected function currentValues(): array
    {
        return [
            'vat_rate' => (string) (RestaurantSetting::getValue('vat_rate') ?? 7.5),
            // Must match the fallback OrderPricingService uses. It used to say
            // 10, so this form would open showing a 10% charge on an install
            // where the POS was charging nothing, and saving the form
            // unchanged would switch the charge on.
            'service_charge_rate' => (string) (RestaurantSetting::getValue('service_charge_rate') ?? 0),
            'discount_limit' => (string) (RestaurantSetting::getValue('discount_limit') ?? 0),
            'shift_start_time' => (string) (RestaurantSetting::getValue('shift_start_time') ?? '08:00'),
            'shift_end_time' => (string) (RestaurantSetting::getValue('shift_end_time') ?? '22:00'),
        ];
    }

    public function save(): void
    {
        if (! $this->canEdit()) {
            Notification::make()
                ->title('Not allowed')
                ->body('You do not have permission to change Restaurant settings.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        // updateOrCreate per key, via the model's own accessor, so the storage
        // convention stays in one place.
        foreach (static::KEYS as $key => $type) {
            RestaurantSetting::setValue($key, $data[$key] ?? null);
        }

        Notification::make()
            ->title('Settings saved')
            ->body('New orders will use these rates.')
            ->success()
            ->send();
    }

    public function resetForm(): void
    {
        $this->form->fill($this->currentValues());

        Notification::make()
            ->title('Reverted to saved values')
            ->send();
    }

    /**
     * Filament v4 renders header actions; the v3 `getFormActions()` hook is
     * ignored, which would have left this page with no way to save at all.
     */
    public function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->icon('heroicon-o-check')
                ->action('save')
                ->disabled(! $this->canEdit()),

            Action::make('reset')
                ->label('Discard changes')
                ->color('gray')
                ->action('resetForm')
                ->disabled(! $this->canEdit()),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /**
     * Sales points are configured elsewhere, but a manager looking for them
     * lands here first.
     *
     * The list itself stays a resource: it is rows with a unique code, a revenue
     * account and a "took orders, so cannot be deleted" rule, none of which fit
     * a fixed key/value form whose save button rewrites every value at once. This
     * is the way in, not a second copy of it.
     */
    public function salesPointsUrl(): ?string
    {
        return static::canViewSalesPoints()
            ? SalesPointResource::getUrl('index')
            : null;
    }

    public function salesPointsCount(): int
    {
        return SalesPoint::query()->count();
    }

    public static function canViewSalesPoints(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::SALES_POINTS_READ);
    }
}
