<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Services\TaskService;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    /**
     * Show the current assignees. The form field is a plain multi-select rather
     * than a relationship() field, so it needs the ids put into its state.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['assignees'] = $this->getRecord()->employees->pluck('id')->all();

        return $data;
    }

    /**
     * Status is changed from the view page and the table, never by editing the
     * record, so that completing a task always runs the recurrence and
     * completion-date logic in one place.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status'], $data['task_number'], $data['completion_date']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(TaskService::class)->update($record, auth()->user(), $data);

        return $record;
    }
}
