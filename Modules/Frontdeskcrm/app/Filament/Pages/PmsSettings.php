<?php

namespace Modules\Frontdeskcrm\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Frontdeskcrm\Models\FrontdeskSetting;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

/**
 * The PMS back-office switches, as one fixed form.
 *
 * The table is `frontdesk_settings(key, value)`, so a Resource would present a
 * list of opaque strings and invite someone to rename a key. The scheduler and
 * the night-audit screen read these exact keys, so the key set is closed here
 * the same way Restaurant settings close theirs.
 *
 * Editing is gated on `pms.settings.update` rather than the general screen
 * access: flipping the automatic charge on makes a scheduler post money every
 * day, so read-only roles (and the desk) can see what time the hotel charges
 * without being able to arm it.
 */
class PmsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $navigationLabel = 'PMS Settings';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'frontdesk/settings';

    protected static ?string $title = 'PMS settings';

    protected string $view = 'frontdeskcrm::filament.pages.pms-settings';

    /**
     * The settings this screen owns, with the type they are stored as.
     *
     * Closed rather than discovered from the table so a stray row cannot appear
     * as an editable field.
     *
     * @var array<string, string>
     */
    protected const KEYS = [
        'auto_charge_enabled' => 'boolean',
        'auto_charge_time' => 'time',
    ];

    /**
     * Form state, keyed by `statePath('settings')` below.
     */
    public ?array $settings = [];

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::PMS_SETTINGS_READ,
            FrontdeskPermission::PMS_SETTINGS_UPDATE,
        );
    }

    public function canEdit(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::PMS_SETTINGS_UPDATE);
    }

    public function mount(): void
    {
        $this->form->fill($this->currentValues());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Automatic room charging')
                    ->description('Posts the nightly room charge for every in-house guest on its own. The scheduler stays permanently idle until this is switched on.')
                    ->schema([
                        Toggle::make('auto_charge_enabled')
                            ->label('Automatic room charging'),

                        TimePicker::make('auto_charge_time')
                            ->label('Charge time')
                            ->helperText('The time of day the room charges are posted.')
                            ->required()
                            ->seconds(false),
                    ]),
            ])
            ->disabled(! $this->canEdit())
            ->statePath('settings');
    }

    /**
     * Saved values, falling back to sensible defaults so a fresh install never
     * opens a blank form (which would invite saving zeros over nothing).
     *
     * @return array<string, mixed>
     */
    protected function currentValues(): array
    {
        return [
            'auto_charge_enabled' => (string) FrontdeskSetting::getValue('auto_charge_enabled', '0') === '1',
            'auto_charge_time' => (string) (FrontdeskSetting::getValue('auto_charge_time', '02:00') ?? '02:00'),
        ];
    }

    public function save(): void
    {
        if (! $this->canEdit()) {
            Notification::make()
                ->title('Not allowed')
                ->body('You do not have permission to change PMS settings.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        foreach (static::KEYS as $key => $type) {
            $value = $data[$key] ?? null;

            if ($type === 'boolean') {
                $value = $value ? '1' : '0';
            }

            FrontdeskSetting::setValue($key, $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->body('Nightly room charges will follow the schedule set here.')
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

    public function getSubheading(): ?string
    {
        return 'Controls the automatic nightly room-charge run. Manual night audits are unaffected.';
    }
}
