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
use Modules\Website\Filament\Resources\NewsletterResource\Pages;
use Modules\Website\Models\Newsletter;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Newsletter Campaign';

    protected static ?string $pluralModelLabel = 'Newsletters';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('preview_text')
                            ->label('Preview text')
                            // Shown in the inbox beside the subject on clients that
                            // support it, so it is how the reader decides to open.
                            ->maxLength(255)
                            ->helperText('Shown next to the subject in most inboxes.')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Delivery')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                Newsletter::STATUS_DRAFT => 'Draft',
                                Newsletter::STATUS_SCHEDULED => 'Scheduled',
                                Newsletter::STATUS_SENDING => 'Sending',
                                Newsletter::STATUS_SENT => 'Sent',
                                Newsletter::STATUS_FAILED => 'Failed',
                            ])
                            ->required()
                            ->default(Newsletter::STATUS_DRAFT)
                            ->live(),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled for')
                            ->seconds(false)
                            // Only meaningful while a campaign is waiting to go out;
                            // once it is sending or sent the date is history.
                            ->visible(fn (callable $get): bool => $get('status') === Newsletter::STATUS_SCHEDULED),
                    ])->columns(2),
                Section::make('Results')
                    ->description('Filled in by the send. Read-only here so a figure cannot be typed over.')
                    ->schema([
                        Forms\Components\TextInput::make('recipients_count')->label('Recipients')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('sent_count')->label('Sent')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('failed_count')->label('Failed')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('opened_count')->label('Opened')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('clicked_count')->label('Clicked')->disabled()->dehydrated(false),
                        Forms\Components\DateTimePicker::make('sent_at')->label('Sent at')->disabled()->dehydrated(false),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')->searchable()->sortable()->limit(60),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Newsletter::STATUS_DRAFT => 'gray',
                        Newsletter::STATUS_SCHEDULED => 'info',
                        Newsletter::STATUS_SENDING => 'warning',
                        Newsletter::STATUS_SENT => 'success',
                        Newsletter::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')->label('Scheduled')->dateTime()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('recipients_count')->label('Recipients')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('sent_count')->label('Sent')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('failed_count')
                    ->label('Failed')
                    ->numeric()
                    ->sortable()
                    // A number of failures is the reason a manager opens this screen,
                    // so hide it while it is zero rather than a column of zeros.
                    ->color(fn (?int $state): string => ($state ?? 0) > 0 ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Newsletter::STATUS_DRAFT => 'Draft',
                        Newsletter::STATUS_SCHEDULED => 'Scheduled',
                        Newsletter::STATUS_SENDING => 'Sending',
                        Newsletter::STATUS_SENT => 'Sent',
                        Newsletter::STATUS_FAILED => 'Failed',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                // Preview renders the campaign through the real email template with a
                // dummy subscriber, so it stays on the legacy controller. Only GET
                // routes are linked from here: a table action issues a GET, so
                // pointing one at the legacy POST send or duplicate routes would
                // just produce a 405. Those live on the campaign's Filament view page.
                Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Newsletter $record): string => route('website.admin.newsletter.campaigns.preview', $record)),
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
            'index' => Pages\ListNewsletters::route('/'),
            'create' => Pages\CreateNewsletter::route('/create'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
            'view' => Pages\ViewNewsletter::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::NEWSLETTER_READ,
            WebsitePermission::NEWSLETTER_UPDATE,
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
        return WebsitePermission::allows(WebsitePermission::NEWSLETTER_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::NEWSLETTER_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::NEWSLETTER_DELETE);
    }
}
