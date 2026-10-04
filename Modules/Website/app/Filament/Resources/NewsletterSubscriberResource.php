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
use Modules\Website\Filament\Resources\NewsletterSubscriberResource\Pages;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Subscriber';

    protected static ?string $pluralModelLabel = 'Subscribers';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscriber')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Subscribed')
                            ->helperText('Inactive subscribers are skipped when a campaign is sent.')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('subscribed_at')
                            ->label('Subscribed at')
                            ->seconds(false)
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Unsubscribed at')
                            ->seconds(false)
                            // The public unsubscribe link sets this itself, so it is
                            // shown rather than typed: an unsubscribe time that the
                            // guest did not ask for would misrepresent the list.
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Subscribed')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscribed_at')->label('Subscribed')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Subscribed'),
                Tables\Filters\Filter::make('unsubscribed')
                    ->label('Unsubscribed only')
                    ->query(fn ($query) => $query->whereNotNull('unsubscribed_at')),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                // Toggling is a POST on the legacy route, so it is done here instead:
                // it is one column write with no mail side effects.
                Actions\Action::make('toggle')
                    ->label(fn (NewsletterSubscriber $record): string => $record->is_active ? 'Unsubscribe' : 'Resubscribe')
                    ->icon('heroicon-o-arrow-path')
                    ->color(fn (NewsletterSubscriber $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (NewsletterSubscriber $record): void {
                        if ($record->is_active) {
                            $record->update([
                                'is_active' => false,
                                'unsubscribed_at' => now(),
                            ]);

                            return;
                        }

                        $record->update([
                            'is_active' => true,
                            'unsubscribed_at' => null,
                        ]);
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                // Import and export stay on the legacy controller: both parse a file
                // and report per-row failures, which is more screen than a modal.
                Actions\Action::make('import')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->url(fn (): string => route('website.admin.newsletter.subscribers.import')),
                Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (): string => route('website.admin.newsletter.subscribers.export')),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
            'view' => Pages\ViewNewsletterSubscriber::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::SUBSCRIBERS_READ,
            WebsitePermission::SUBSCRIBERS_UPDATE,
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

    public static function canCreate(): bool
    {
        return WebsitePermission::allows(WebsitePermission::SUBSCRIBERS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::SUBSCRIBERS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::SUBSCRIBERS_DELETE);
    }
}
