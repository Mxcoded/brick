<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\Roles\RoleResource;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    public function getHeading(): string
    {
        return 'Roles';
    }

    public function getSubheading(): ?string
    {
        return 'Manage what each role can do. Click edit to tick permissions from each business module, or create a new role from scratch.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
