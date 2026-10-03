<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    /**
     * Assignments and the audit trail are written by the service, so the record
     * is created through it rather than straight off the form.
     */
    protected function handleRecordCreation(array $data): Task
    {
        return app(TaskService::class)->create(auth()->user(), $data);
    }

    /**
     * The form has no status field: a new task is always pending, so there is
     * nothing for a client to set that the service would have to ignore.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['status']);

        return $data;
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
