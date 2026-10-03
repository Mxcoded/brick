<?php

namespace Modules\Restaurant\Filament\Resources\MenuCategories;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

class MenuCategoryResource extends Resource
{
    protected static ?string $model = MenuCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Menu Categories';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'menu category';

    protected static ?string $pluralModelLabel = 'menu categories';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/menu-categories';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(191)
                // The legacy add-category endpoint keyed uniqueness on this, and
                // the public menu resolves categories by name.
                ->unique(ignoreRecord: true),

            Select::make('parent_id')
                ->label('Parent category')
                ->relationship('parent', 'name')
                // A category cannot be its own ancestor, and the option list is
                // built from the record being edited so the circular option is
                // never offered in the first place.
                ->options(fn (?MenuCategory $record): array => static::parentOptions($record))
                ->searchable()
                ->placeholder('Top level'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('Top level')
                    ->toggleable(),

                TextColumn::make('menu_items_count')
                    ->label('Items')
                    ->counts('menuItems')
                    ->sortable(),

                IconColumn::make('deleted_at')
                    ->label('Trashed')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('deleted_at')
                    ->label('Trashed only')
                    ->trueLabel('Trashed')
                    ->falseLabel('Active')
                    ->nullable(),

                Filter::make('top_level')
                    ->label('Top level only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),
            ])
            ->actions([
                EditAction::make(),
                // Restore is a separate capability from delete, matching the
                // restore-category permission the legacy routes already checked.
                RestoreAction::make()->visible(fn (): bool => RestaurantPermission::allows(
                    RestaurantPermission::MENU_CATEGORIES_RESTORE
                )),
                DeleteAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allowsAny(
            RestaurantPermission::MENU_CATEGORIES_READ,
            RestaurantPermission::MENU_CATEGORIES_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_CATEGORIES_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_CATEGORIES_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_CATEGORIES_DELETE);
    }

    public static function canRestore($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::MENU_CATEGORIES_RESTORE);
    }

    public static function getEloquentQuery(): Builder
    {
        // Trashed rows stay visible so they can be restored rather than being
        // stranded once the trashed-only filter is applied.
        return static::getModel()::query()->withTrashed()->withCount('menuItems');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuCategories::route('/'),
            'create' => Pages\CreateMenuCategory::route('/create'),
            'edit' => Pages\EditMenuCategory::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    protected static function parentOptions(?MenuCategory $record): array
    {
        $query = MenuCategory::query()->orderBy('name');

        if ($record) {
            // Excluding the record itself stops a category being reparented under
            // itself, which would orphan its children.
            $query->whereKeyNot($record->getKey());
        }

        return $query->pluck('name', 'id')->all();
    }
}
