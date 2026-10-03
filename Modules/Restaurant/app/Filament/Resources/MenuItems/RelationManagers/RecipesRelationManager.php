<?php

namespace Modules\Restaurant\Filament\Resources\MenuItems\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Support\RestaurantPermission;

/**
 * The ingredient list for a dish, i.e. what the kitchen consumes per portion.
 *
 * Ingredient quantities are the bridge between menu and stock, so reading the
 * recipe (recipes.read) and editing it (recipes.create/delete) are separate:
 * a chef needs to see the list to serve a plate without being able to change
 * the costing.
 */
class RecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'recipeItems';

    protected static ?string $title = 'Recipe';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stockItem.name')
            ->columns([
                TextColumn::make('stockItem.name')
                    ->label('Ingredient')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stockItem.unit')
                    ->label('Unit')
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Quantity per portion')
                    ->numeric(3),
            ])
            ->defaultSort('stockItem.name')
            ->headerActions([
                Action::make('addIngredient')
                    ->label('Add ingredient')
                    ->icon('heroicon-o-plus')
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::RECIPES_CREATE))
                    ->form([
                        Select::make('restaurant_stock_item_id')
                            ->label('Ingredient')
                            ->options(fn (): array => StockItem::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Add the ingredient to restaurant stock first if it is not listed.'),

                        TextInput::make('quantity')
                            ->label('Quantity per portion')
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->step(0.001),
                    ])
                    ->action(function (array $data): void {
                        $this->getRelationship()->create($data);

                        Notification::make()
                            ->title('Ingredient added')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('deleteIngredient')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::RECIPES_DELETE))
                    ->action(fn () => $this->record->delete()),

                DeleteAction::make()->hidden(),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::RECIPES_READ);
    }
}
