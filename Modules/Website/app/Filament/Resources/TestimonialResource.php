<?php

namespace Modules\Website\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Website\Filament\Resources\TestimonialResource\Pages;
use Modules\Website\Models\Testimonial;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Testimonial';

    protected static ?string $pluralModelLabel = 'Testimonials';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review')
                    ->schema([
                        Forms\Components\TextInput::make('guest_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('email')->email()->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'stay' => 'Stay',
                                'restaurant' => 'Restaurant',
                                'event' => 'Event',
                            ])
                            ->required()
                            ->live()
                            ->default('stay'),
                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->default(5),
                        Forms\Components\Toggle::make('approved')
                            ->label('Approved')
                            // Approving is what publishes a review to the site, so it
                            // is the update capability rather than a separate one.
                            ->helperText('Approved reviews appear on the public website.')
                            ->default(false),
                        Forms\Components\Textarea::make('text')->rows(4)->required()->columnSpanFull(),
                    ])->columns(2),
                Section::make('Category Ratings')
                    ->description('Optional 1-5 scores shown as the breakdown under the review.')
                    ->schema([
                        Forms\Components\TextInput::make('cleanliness')->numeric()->minValue(1)->maxValue(5),
                        Forms\Components\TextInput::make('staff_rating')->numeric()->minValue(1)->maxValue(5),
                        Forms\Components\TextInput::make('food_rating')->numeric()->minValue(1)->maxValue(5),
                        Forms\Components\TextInput::make('wifi_rating')->numeric()->minValue(1)->maxValue(5),
                        Forms\Components\TextInput::make('maintenance_rating')->numeric()->minValue(1)->maxValue(5),
                    ])->columns(3),
                Section::make('Context')
                    ->description('Where the review came from. Restaurant and event reviews need a venue or event name.')
                    ->schema([
                        Forms\Components\TextInput::make('stay_type')
                            ->label('Stay type')
                            ->placeholder('Weekend, honeymoon, business...')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('dining_venue')
                            ->label('Dining venue')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('event_name')->maxLength(255),
                        Forms\Components\TextInput::make('location')->maxLength(255),
                        Forms\Components\TextInput::make('guest_image')
                            ->label('Guest image')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Wi-Fi Session')
                    ->description('Captured automatically when the guest reviews a stay through the captive portal.')
                    ->schema([
                        Forms\Components\TextInput::make('ap_name')->label('Access point')->maxLength(255),
                        Forms\Components\TextInput::make('ap_mac')->label('AP MAC')->maxLength(255),
                        Forms\Components\TextInput::make('ssid')->maxLength(255),
                        Forms\Components\TextInput::make('client_mac')->label('Client MAC')->maxLength(255),
                        Forms\Components\TextInput::make('client_ip')->label('Client IP')->maxLength(255),
                        Forms\Components\TextInput::make('portal_session')->maxLength(255),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => '★ '.($state ?? '-'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (Testimonial $record): string => $record->typeLabel())
                    ->sortable(),
                Tables\Columns\TextColumn::make('contextLabel')
                    ->label('Context')
                    ->state(fn (Testimonial $record): string => $record->contextLabel())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('text')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\IconColumn::make('approved')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('approved')
                    ->label('Published')
                    ->placeholder('All reviews'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'stay' => 'Stay',
                        'restaurant' => 'Restaurant',
                        'event' => 'Event',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        '5' => '5 stars',
                        '4' => '4 stars',
                        '3' => '3 stars',
                        '2' => '2 stars',
                        '1' => '1 star',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('approve')
                    ->label('Publish')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    // Publishing is an update, so it checks the same capability as
                    // the edit form rather than inventing a moderation-only one.
                    ->visible(fn (Testimonial $record): bool => ! $record->approved)
                    ->action(fn (Testimonial $record) => $record->update(['approved' => true])),
                Actions\Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (Testimonial $record): bool => (bool) $record->approved)
                    ->action(fn (Testimonial $record) => $record->update(['approved' => false])),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
            'view' => Pages\ViewTestimonial::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::TESTIMONIALS_READ,
            WebsitePermission::TESTIMONIALS_UPDATE,
        );
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    /**
     * Reviews are written by guests, not typed in by staff. The capability exists
     * in the taxonomy so `admin` holds the whole set, but nobody in the website
     * team is granted it and the create page is unreachable for them.
     */
    public static function canCreate(): bool
    {
        return WebsitePermission::allows(WebsitePermission::TESTIMONIALS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::TESTIMONIALS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::TESTIMONIALS_DELETE);
    }
}
