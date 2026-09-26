<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Roles\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, int> */
    private array $permissionsToSync = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionsToSync = $this->extractPermissionIds($data['permissions'] ?? []);
        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
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
