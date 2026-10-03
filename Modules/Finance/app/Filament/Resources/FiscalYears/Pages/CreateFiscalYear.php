<?php

namespace Modules\Finance\Filament\Resources\FiscalYears\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Resources\FiscalYears\FiscalYearResource;

class CreateFiscalYear extends CreateRecord
{
    protected static string $resource = FiscalYearResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function afterCreate(): void
    {
        $this->record->generateMonthlyPeriods();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePeriods')
                ->label('Generate monthly periods')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Creates the monthly periods for this year, clipped to its start and end dates.')
                ->action(fn () => $this->record->generateMonthlyPeriods()),
        ];
    }
}
