<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use InvalidArgumentException;
use Modules\Frontdeskcrm\Filament\Resources\InvoiceResource;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Services\CheckInService;
use Modules\Frontdeskcrm\Services\CheckoutService;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Frontdeskcrm\Services\StayService;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\RoomAvailabilityService;

class ViewRegistration extends ViewRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        $members = $this->record->is_group_lead
            ? $this->record->children()
                ->where('stay_status', 'checked_in')
                ->pluck('full_name', 'id')
                ->all()
            : [];

        $formFields = [
            DatePicker::make('new_check_out')
                ->label('New check-out date')
                ->default($this->record->check_out?->format('Y-m-d'))
                ->minDate($this->record->check_in?->format('Y-m-d'))
                ->required()
                ->live(),
            Placeholder::make('room_availability_notice')
                ->label('Room availability')
                ->content(fn (Get $get): string => $this->roomAvailabilityNotice($get('new_check_out'))),
            Select::make('room_unit_id')
                ->label('Room')
                ->default((string) ($this->record->room_unit_id ?? ''))
                ->options(fn (Get $get): array => $this->moveRoomOptions($get('new_check_out')))
                ->visible(fn (): bool => filled($this->record->room_unit_id))
                ->helperText('Pick another free room when the guest needs to move.')
                ->searchable()
                ->columnSpanFull(),
        ];

        if ($this->record->is_group_lead) {
            $formFields[] = Select::make('members_to_extend')
                ->label('Also extend these group members')
                ->multiple()
                ->options($members)
                ->columnSpanFull();
        }

        return [
            Actions\EditAction::make(),

            Actions\Action::make('checkIn')
                ->label('Check In')
                ->icon('heroicon-o-arrow-right-end-on-rectangle')
                ->color('success')
                ->visible(fn (): bool => $this->canCheckIn())
                ->form([
                    Select::make('room_unit_id')
                        ->label('Room')
                        ->options(fn (): array => $this->checkInRoomOptions())
                        ->default((string) ($this->record->room_unit_id ?? ''))
                        ->searchable()
                        ->required(),
                    TextInput::make('room_rate')
                        ->label('Rate per night')
                        ->numeric()
                        ->minValue(0)
                        ->default($this->record->room_rate)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $registration = app(CheckInService::class)->checkIn(
                            $this->record,
                            (int) $data['room_unit_id'],
                            (float) $data['room_rate'],
                            auth()->id(),
                        );
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Guest {$registration->full_name} checked in.")
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\Action::make('checkOut')
                ->label('Check Out')
                ->icon('heroicon-o-arrow-left-start-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Check out this guest?')
                ->modalDescription('The room is released, the stay is closed and any balance flagged for a city ledger account is posted to it.')
                ->visible(fn (): bool => $this->canCheckOut())
                ->action(function (): void {
                    try {
                        $result = app(CheckoutService::class)->checkout($this->record, auth()->id());
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $notification = Notification::make()->title($result['message']);

                    if (filled($result['warnings'])) {
                        $notification->warning()->body(implode(' ', $result['warnings']));
                    } else {
                        $notification->success();
                    }

                    $notification->send();

                    $this->record->refresh();
                }),

            Actions\Action::make('postToCityLedger')
                ->label('Post to City Ledger')
                ->icon('heroicon-o-building-office-2')
                ->color('gray')
                ->visible(fn (): bool => $this->canPostToCityLedger())
                ->modalDescription('The outstanding balance is billed to the selected account and the guest folio is settled.')
                ->form([
                    Select::make('city_ledger_account_id')
                        ->label('City Ledger Account')
                        ->options(fn (): array => $this->cityLedgerAccountOptions())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $account = CityLedgerAccount::find($data['city_ledger_account_id']);

                    if (! $account) {
                        Notification::make()
                            ->title('The selected city ledger account no longer exists.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        app(CityLedgerService::class)->postOutstandingToLedger($account, $this->record, auth()->id());
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Balance posted to {$account->name}.")
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\Action::make('createCityLedgerAccount')
                ->label('Create City Ledger Account')
                ->icon('heroicon-o-plus-circle')
                ->color('gray')
                ->visible(fn (): bool => $this->canCreateCityLedgerAccount())
                ->modalHeading('Create city ledger account from this stay')
                ->modalDescription('Details are lifted from the stay and its guest. Saving creates the account and bills this stay to it.')
                ->form([
                    TextInput::make('name')
                        ->label('Account name')
                        ->default(fn (): string => $this->cityLedgerAccountDefaults()['name'])
                        ->required()
                        ->maxLength(255)
                        ->helperText('Company when the stay has one, otherwise the guest.'),
                    TextInput::make('contact_person')
                        ->default(fn (): ?string => $this->cityLedgerAccountDefaults()['contact_person'])
                        ->maxLength(255),
                    TextInput::make('email')
                        ->default(fn (): ?string => $this->cityLedgerAccountDefaults()['email'])
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->default(fn (): ?string => $this->cityLedgerAccountDefaults()['phone'])
                        ->maxLength(255),
                    TextInput::make('tax_id')->maxLength(255),
                    TextInput::make('credit_limit')->numeric()->minValue(0)->default(0),
                    TextInput::make('payment_terms')->maxLength(255)->default('net30'),
                    Select::make('status')
                        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                        ->default('active')
                        ->required(),
                    Textarea::make('address')
                        ->default(fn (): ?string => $this->cityLedgerAccountDefaults()['address'])
                        ->columnSpanFull()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $result = app(CityLedgerService::class)
                        ->findOrCreateFromRegistration($this->record, $data, auth()->id());

                    $account = $result['account'];

                    $this->record->update(['city_ledger_account_id' => $account->id]);
                    $this->record->refresh();

                    Notification::make()
                        ->title($result['created']
                            ? "City ledger account {$account->code} created and linked."
                            : "Linked to existing account {$account->code}.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('folios')
                ->label('Folios')
                ->icon('heroicon-o-receipt-percent')
                ->color('info')
                ->url(fn (): string => route('frontdesk.folios.index', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('invoices')
                ->label('Invoices')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): string => InvoiceResource::getUrl('index')),

            Actions\Action::make('extendStay')
                ->label('Extend Stay')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->visible(fn (): bool => $this->canExtendStay())
                ->form($formFields)
                ->action(function (array $data): void {
                    $newCheckOut = Carbon::parse($data['new_check_out']);
                    $roomUnitId = filled($data['room_unit_id'])
                        ? (int) $data['room_unit_id']
                        : null;

                    $moving = $roomUnitId && $roomUnitId !== (int) $this->record->getOriginal('room_unit_id');

                    if ($newCheckOut->isSameDay($this->record->check_out) && ! $moving) {
                        Notification::make()
                            ->title('The new check-out date is the same as the current one. No changes were made.')
                            ->warning()
                            ->send();

                        return;
                    }

                    try {
                        app(StayService::class)->adjustStay(
                            $this->record,
                            $newCheckOut,
                            $data['members_to_extend'] ?? [],
                            $roomUnitId,
                        );
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->refresh();

                    $title = 'Stay details have been successfully updated.';

                    if ($moving && ($unit = RoomUnit::find($roomUnitId))) {
                        $title = 'Stay updated. Guest moved to Room '.$unit->room_number.'.';
                    }

                    Notification::make()
                        ->title($title)
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Availability note shown under the picked date: the guest's current room
     * is checked for the extension window, including future reservations and
     * website bookings - so the receptionist knows up-front when a room move
     * is required.
     */
    private function roomAvailabilityNotice(?string $date): string
    {
        $newCheckOut = $date ? Carbon::parse($date) : $this->record->check_out;
        $unit = $this->record->roomUnit;

        if ($newCheckOut && $this->record->check_out && $newCheckOut->isSameDay($this->record->check_out)) {
            return 'No extension - the check-out date is unchanged.';
        }

        if (! $newCheckOut) {
            return 'Pick a check-out date to see room availability.';
        }

        $room = $unit?->room_number ?? 'Your room';
        $reason = app(StayService::class)->roomUnavailableReason($this->record, $newCheckOut, $this->record->room_unit_id);

        if (! $reason) {
            return $room.' is free until '.$newCheckOut->format('D, M j, Y').'. Keep it or pick another free room below.';
        }

        return $reason.'. Pick a different free room below to move the guest, or shorten the extension.';
    }

    /**
     * Free room units (same room type) for the picked extension period. The
     * guest's current room is always listed first (marked "(current)") so the
     * default selection stays valid even when it is not free for the picked
     * date - the availability notice above explains when a move is needed.
     *
     * @return array<string, string>
     */
    private function moveRoomOptions(?string $date): array
    {
        $newCheckOut = $date ? Carbon::parse($date) : $this->record->check_out;

        if (! $newCheckOut) {
            return [];
        }

        $units = app(StayService::class)->availableMoveUnits($this->record, $newCheckOut);
        $currentUnit = $this->record->roomUnit;
        $currentIsFree = $units->contains(fn (RoomUnit $unit): bool => $unit->id === $this->record->room_unit_id);

        $options = [];

        if ($currentUnit && ! $currentIsFree) {
            $options[(string) $currentUnit->id] = $currentUnit->room_number
                .' - '.($currentUnit->roomType?->name ?? 'Room type')
                .' (current - not free on this date)';
        }

        foreach ($units as $unit) {
            $label = $unit->room_number.' - '.($unit->roomType?->name ?? 'Room type');

            if ($unit->id === $this->record->room_unit_id) {
                $label .= ' (current)';
            }

            $options[(string) $unit->id] = $label;
        }

        return $options;
    }

    private function canExtendStay(): bool
    {
        return $this->record->stay_status === 'checked_in'
            && FrontdeskPermission::allows(FrontdeskPermission::REGISTRATIONS_ADJUST_STAY);
    }

    private function canCheckIn(): bool
    {
        return in_array($this->record->stay_status, ['reserved', 'draft_by_guest'], true)
            && FrontdeskPermission::allowsAny(
                FrontdeskPermission::REGISTRATIONS_CHECK_IN,
                FrontdeskPermission::REGISTRATIONS_MANAGE,
            );
    }

    private function canCheckOut(): bool
    {
        return $this->record->stay_status === 'checked_in'
            && FrontdeskPermission::allowsAny(
                FrontdeskPermission::REGISTRATIONS_CHECK_OUT,
                FrontdeskPermission::REGISTRATIONS_MANAGE,
            );
    }

    private function canPostToCityLedger(): bool
    {
        return $this->outstandingBalance() > 0
            && $this->record->city_ledger_transferred_at === null
            && $this->cityLedgerAccountOptions() !== []
            && FrontdeskPermission::allowsAny(
                FrontdeskPermission::INVOICES_CREATE,
                FrontdeskPermission::INVOICES_ISSUE,
                FrontdeskPermission::REGISTRATIONS_MANAGE,
            );
    }

    private function outstandingBalance(): float
    {
        return app(FolioService::class)->getOutstandingBalance($this->record);
    }

    private function canCreateCityLedgerAccount(): bool
    {
        return $this->record->city_ledger_transferred_at === null
            && FrontdeskPermission::allowsAny(
                FrontdeskPermission::INVOICES_CREATE,
                FrontdeskPermission::INVOICES_ISSUE,
                FrontdeskPermission::REGISTRATIONS_MANAGE,
            );
    }

    /**
     * Guest/company details for the "Create City Ledger Account" form, taken
     * from the stay so nothing has to be retyped at the desk.
     *
     * @return array<string, mixed>
     */
    private function cityLedgerAccountDefaults(): array
    {
        $registration = $this->record;

        $name = trim((string) $registration->company_name);

        if ($name === '') {
            $name = trim(($registration->title ?? '').' '.($registration->full_name ?? ''));
        }

        return [
            'name' => $name,
            'contact_person' => $registration->full_name,
            'email' => $registration->email,
            'phone' => $registration->contact_number,
            'address' => $registration->home_address,
            'credit_limit' => 0,
            'payment_terms' => 'net30',
            'status' => 'active',
        ];
    }

    /**
     * Free rooms for the stay's dates, for the check-in form. Units of the
     * reservation's own room type are offered first; when the reservation has no
     * room type yet, every free unit is listed.
     *
     * @return array<string, string>
     */
    private function checkInRoomOptions(): array
    {
        $availability = app(RoomAvailabilityService::class);

        $units = collect();

        if ($this->record->room_type_id) {
            $units = $availability->getAvailableUnits(
                $this->record->room_type_id,
                $this->record->check_in,
                $this->record->check_out,
                $this->record->booking_id,
                $this->record->id,
            );
        } else {
            $units = RoomUnit::query()
                ->whereNotIn('status', RoomAvailabilityService::UNAVAILABLE_UNIT_STATUSES)
                ->orderBy('room_number')
                ->get()
                ->filter(fn (RoomUnit $unit): bool => $availability->isUnitAvailable(
                    $unit->id,
                    $this->record->check_in,
                    $this->record->check_out,
                    $this->record->booking_id,
                    $this->record->id,
                ));
        }

        $options = [];

        foreach ($units as $unit) {
            $options[(string) $unit->id] = $unit->room_number.' - '.($unit->roomType?->name ?? 'Room type');
        }

        // Keep the currently assigned room visible even if the availability
        // query filtered it out, so the default selection stays valid.
        $current = $this->record->roomUnit;

        if ($current && ! array_key_exists((string) $current->id, $options)) {
            $options = [(string) $current->id => $current->room_number.' - '.($current->roomType?->name ?? 'Room type').' (current)']
                + $options;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function cityLedgerAccountOptions(): array
    {
        return CityLedgerAccount::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (CityLedgerAccount $account): array => [(string) $account->id => $account->name])
            ->all();
    }
}
