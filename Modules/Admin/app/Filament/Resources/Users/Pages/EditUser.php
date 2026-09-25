<?php

namespace Modules\Admin\Filament\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Users\UserResource;
use OwenIt\Auditing\Events\AuditCustom;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?array $previousRoleNames = null;

    protected function beforeSave(): void
    {
        $this->previousRoleNames = $this->record->roles->pluck('name')->all();
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        $newRoleNames = $record->roles->pluck('name')->all();

        $added = array_diff($newRoleNames, $this->previousRoleNames);
        $removed = array_diff($this->previousRoleNames, $newRoleNames);

        if ($added === [] && $removed === []) {
            return;
        }

        $record->auditEvent = ($removed !== []) ? 'role-detached' : 'role-assigned';
        $record->auditCustomOld = ['roles' => $this->previousRoleNames];
        $record->auditCustomNew = ['roles' => $newRoleNames];
        $record->isCustomEvent = true;

        event(new AuditCustom($record));

        $record->isCustomEvent = false;
        $record->auditCustomOld = $record->auditCustomNew = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
