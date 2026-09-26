<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Roles\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, int> */
    private array $permissionsToSync = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->permissionsToSync = $this->extractPermissionIds($data['permissions'] ?? []);
        unset($data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->permissionsToSync);
    }

    /**
     * @return array<int, int>
     */
    private function extractPermissionIds(mixed $permissions): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, is_array($permissions) ? $permissions : []),
            fn ($id) => $id > 0,
        )));
    }
}
