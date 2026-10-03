<?php

namespace Modules\Restaurant\Filament\Resources\StockItems\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Support\RestaurantPermission;

/**
 * The stock ledger for one ingredient.
 *
 * Movements are append-only: `stock_quantity` on the item is the running total,
 * so correcting a mistake means recording a reversing movement rather than
 * editing history. That is why there is no edit or delete action here, and why
 * the movement increments the balance inside a transaction.
 *
 * The direction is derived from the movement type rather than trusting the
 * sign in the form, matching the legacy stockMovementStore endpoint: usage and
 * wastage always leave the building, purchases always add.
 */
class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Stock movements';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'usage' => 'info',
                        'wastage' => 'danger',
                        'adjustment' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('quantity')
                    ->label('Change')
                    ->numeric(3)
                    // Signed, because the direction is the point of the row.
                    ->color(fn (float $state): string => $state < 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('unit_cost')
                    ->label('Unit cost')
                    ->money('NGN')
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('notes')
                    ->label('Note')
                    ->placeholder('—')
                    ->limit(60)
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('recordMovement')
                    ->label('Record movement')
                    ->icon('heroicon-o-arrows-right-left')
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::STOCK_MOVEMENTS))
                    ->form([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'purchase' => 'Purchase (adds stock)',
                                'usage' => 'Usage (consumes stock)',
                                'wastage' => 'Wastage (removes stock)',
                                'adjustment' => 'Adjustment (adds or removes)',
                            ])
                            ->required()
                            ->default('purchase')
                            ->live(),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->helperText('Always positive. Usage and wastage are recorded as a decrease.'),

                        TextInput::make('unit_cost')
                            ->label('Unit cost')
                            ->numeric()
                            ->prefix('NGN')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(fn (): ?float => (float) $this->getOwnerRecord()->unit_cost)
                            ->helperText('Defaults to the item’s current unit cost.'),

                        TextInput::make('reference')
                            ->label('Reference')
                            ->maxLength(255)
                            ->placeholder('Invoice, supplier, shift number…'),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data): void {
                        /** @var StockItem $item */
                        $item = $this->getOwnerRecord();

                        $quantity = abs((float) $data['quantity']);

                        // Direction comes from the type, not the input.
                        $signed = match ($data['type']) {
                            'purchase' => $quantity,
                            'usage', 'wastage' => -$quantity,
                            // An adjustment may go either way.
                            default => (float) $data['quantity'] >= 0 ? $quantity : -$quantity,
                        };

                        // Usage and wastage must not invent stock.
                        if ($signed < 0 && abs($signed) > (float) $item->stock_quantity) {
                            Notification::make()
                                ->title('Not enough stock')
                                ->body("{$item->name} has ".number_format((float) $item->stock_quantity, 3).' in stock, which is less than the '.number_format(abs($signed), 3).' you tried to remove.')
                                ->danger()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($item, $data, $signed): void {
                            $item->increment('stock_quantity', $signed);
                            $item->movements()->create([
                                'type' => $data['type'],
                                'quantity' => $signed,
                                'unit_cost' => $data['unit_cost'] ?? null,
                                'reference' => $data['reference'] ?? null,
                                'notes' => $data['notes'] ?? null,
                                'user_id' => auth()->id(),
                            ]);
                        });

                        Notification::make()
                            ->title('Movement recorded')
                            ->body($item->fresh()->name.' now at '.number_format((float) $item->fresh()->stock_quantity, 3).'.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::STOCK_READ);
    }
}
