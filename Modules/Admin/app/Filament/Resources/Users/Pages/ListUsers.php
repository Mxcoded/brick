<?php

namespace Modules\Admin\Filament\Resources\Users\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Admin\Filament\Pages\CreateUserFromEmployee;
use Modules\Admin\Filament\Resources\Users\UserResource;
use Modules\Admin\Filament\Resources\Users\Widgets\UserStatsOverview;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('createUserFromEmployee')
                ->label('Create from Employee')
                ->icon(Heroicon::OutlinedUserPlus)
                ->url(fn (): string => CreateUserFromEmployee::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserStatsOverview::class,
        ];
    }
}
