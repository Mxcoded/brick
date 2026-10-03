<?php

namespace Modules\Finance\Filament\Resources\FiscalYears\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Models\FiscalYear;

class FiscalYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Label shown in reports, e.g. "FY2026".'),

                Toggle::make('is_active')
                    ->label('Active fiscal year')
                    ->helperText('Only one year can be active at a time. Reports default to the active year.')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, bool $state) => $state ? $set('is_closed', false) : null),

                DatePicker::make('start_date')
                    ->label('Start date')
                    ->required()
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (mixed $state, Set $set) => $set('end_date', self::defaultEndDate($state))),

                DatePicker::make('end_date')
                    ->required()
                    ->native(false)
                    ->after('start_date')
                    ->minDate(fn (Get $get) => $get('start_date'))
                    ->helperText('Monthly periods are clipped to these bounds.'),

                Toggle::make('is_closed')
                    ->label('Closed')
                    ->helperText('A closed year locks all of its periods and blocks further postings.')
                    ->disabled(fn (?FiscalYear $record): bool => (bool) $record?->is_closed),
            ]);
    }

    protected static function defaultEndDate(mixed $start): ?string
    {
        if (! $start) {
            return null;
        }

        return Carbon::parse($start)->addYear()->subDay()->toDateString();
    }
}
