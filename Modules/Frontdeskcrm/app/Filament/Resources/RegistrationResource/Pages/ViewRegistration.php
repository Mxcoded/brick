<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use InvalidArgumentException;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;
use Modules\Frontdeskcrm\Services\StayService;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Website\Models\RoomUnit;

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
}
