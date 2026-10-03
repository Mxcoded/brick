<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountForm
{
    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    /** Account types that carry a credit normal balance. */
    public const CREDIT_NORMAL_TYPES = ['liability', 'equity', 'income'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('code')
                    ->label('GL Code')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique ledger code, e.g. 1100.'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->required()
                    ->options(collect(self::TYPES)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all())
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (in_array($state, self::CREDIT_NORMAL_TYPES, true)) {
                            $set('normal_balance', 'credit');
                        } elseif ($state) {
                            $set('normal_balance', 'debit');
                        }
                    })
                    ->helperText('Liability, equity and income accounts default to a credit normal balance.'),

                Select::make('normal_balance')
                    ->required()
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->default('debit'),

                Select::make('parent_id')
                    ->label('Parent Account')
                    ->options(fn (?ChartOfAccount $record): array => ChartOfAccount::query()
                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                        ->orderBy('code')
                        ->get()
                        ->mapWithKeys(fn (ChartOfAccount $a) => [$a->id => "{$a->code} — {$a->name}"])
                        ->all())
                    ->searchable()
                    ->placeholder('None'),

                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),

                Toggle::make('is_contra')
                    ->label('Contra account')
                    ->helperText('A contra account carries the opposite normal balance of its parent.'),

                Toggle::make('active')
                    ->default(true),
            ]);
    }
}
