<?php

namespace Modules\Frontdeskcrm\Filament\Resources\FolioResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;

class ViewFolio extends ViewRecord
{
    protected static string $resource = FolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('postCharge')
                ->label('Post Charge')
                ->icon('heroicon-o-plus-circle')
                ->color('warning')
                ->visible(fn (): bool => $this->canPostCharge())
                ->form([
                    Select::make('charge_type')
                        ->options([
                            'service' => 'Service Charge',
                            'laundry' => 'Laundry',
                            'mini_bar' => 'Mini Bar',
                            'restaurant' => 'Restaurant',
                            'damage' => 'Damage / Lost Item',
                            'other' => 'Other',
                        ])
                        ->required(),
                    Textarea::make('description')
                        ->required(),
                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->prefix('₦'),
                    TextInput::make('tax_code'),
                    TextInput::make('tax_rate')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0),
                    DatePicker::make('post_date')
                        ->default(now()),
                ])
                ->action(function (array $data): void {
                    if (! $this->canPostCharge()) {
                        Notification::make()
                            ->title('This folio is not open')
                            ->danger()
                            ->send();

                        return;
                    }

                    app(FolioService::class)->postCharge($this->record, [
                        'charge_type' => $data['charge_type'],
                        'description' => $data['description'],
                        'amount' => (float) $data['amount'],
                        'tax_code' => $data['tax_code'] ?? null,
                        'tax_rate' => (float) ($data['tax_rate'] ?? 0),
                        'post_date' => $data['post_date'] ?? now(),
                    ], auth()->id());

                    $this->record->refresh();

                    Notification::make()
                        ->title('Charge posted to folio')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function canPostCharge(): bool
    {
        return $this->record->status === 'open'
            && FrontdeskPermission::allows(FrontdeskPermission::FOLIOS_POST_CHARGE);
    }
}
