<?php

namespace Modules\Restaurant\Filament\Resources\MenuItems;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Restaurant\Filament\Resources\MenuItems\RelationManagers\RecipesRelationManager;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Menu Items';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'menu item';

    protected static ?string $pluralModelLabel = 'menu items';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/menu-items';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('restaurant_menu_categories_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('name')
                ->required()
                ->maxLength(191),

            Textarea::make('description')
                ->rows(3)
                ->maxLength(1000),

            TextInput::make('price')
                ->label('Price')
                ->numeric()
                ->required()
                ->minValue(0)
                ->step(0.01)
                ->prefix('NGN'),

            Toggle::make('is_available')
                ->label('Available')
                ->helperText('Unavailable items are hidden from the public menu and the POS.')
                // New items go live; the legacy add endpoint defaulted the same way.
                ->default(true),

            TextInput::make('image')
                ->label('Image URL')
                ->maxLength(255)
                ->placeholder('https://…')
                ->helperText('Shown on the public menu and receipt.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/menu-placeholder.png'))
                    ->toggleable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable()
                    ->placeholder('Uncategorised'),

                TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('recipe_items_count')
                    ->label('Recipe')
                    ->counts('recipeItems')
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('Trashed')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Trashed' : 'Active')
                    ->colors(['danger'])
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('restaurant_menu_categories_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->trueLabel('Available')
                    ->falseLabel('Unavailable'),

                TernaryFilter::make('deleted_at')
                    ->label('Trashed only')
                    ->trueLabel('Trashed')
                    ->falseLabel('Active')
                    ->nullable(),
            ])
            ->headerActions([
                Action::make('toggleAvailability')
                    ->label('Set availability')
                    ->icon('heroicon-o-signal')
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_UPDATE))
                    ->action(function (Collection $records, array $data): void {
                        $available = (bool) $data['is_available'];

                        foreach ($records as $item) {
                            $item->forceFill(['is_available' => $available])->save();
                        }
                    })
                    ->form([
                        Toggle::make('is_available')
                            ->label('Available')
                            ->default(true)
                            ->helperText('Applies to every selected item. Unavailable items disappear from the public menu.'),
                    ]),
            ])
            ->actions([
                Action::make('recipes')
                    ->label('Recipe')
                    ->icon('heroicon-o-beaker')
                    // Ingredients are a separate capability: a chef needs to read
                    // the recipe without being able to reprice the dish.
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::RECIPES_READ))
                    // Relation managers have no route of their own in v5; they are
                    // a tab on the owning record's page, selected by query string.
                    ->url(fn (MenuItem $record): string => static::getUrl('edit', [
                        'record' => $record,
                        'activeRelationManager' => 'recipe',
                    ])),

                EditAction::make(),
                RestoreAction::make()->visible(fn (): bool => RestaurantPermission::allows(
                    RestaurantPermission::MENU_ITEMS_RESTORE
                )),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allowsAny(
            RestaurantPermission::MENU_ITEMS_READ,
            RestaurantPermission::MENU_ITEMS_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_DELETE);
    }

    public static function canRestore($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_RESTORE);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->withTrashed()
            ->with('category')
            ->withCount('recipeItems');
    }

    public static function getRelations(): array
    {
        // Keyed, not a bare list: the key becomes the tab id, which is what the
        // Recipe action deep-links to. An unkeyed entry lands on tab "0".
        return [
            'recipe' => RecipesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
