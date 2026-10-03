<?php

namespace Modules\Finance\Filament\Resources\FiscalYears\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Resources\FiscalYears\FiscalYearResource;

class EditFiscalYear extends EditRecord
{
    protected static string $resource = FiscalYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePeriods')
                ->label('Generate monthly periods')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Creates any missing monthly periods for this year. Existing periods are left untouched.')
                ->action(function (): void {
                    $this->record->generateMonthlyPeriods();
                    $this->fillForm();
                }),
        ];
    }
}
