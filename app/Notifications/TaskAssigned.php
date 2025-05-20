<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Tasks\Models\Task;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in database for popup
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_number' => $this->task->task_number,
            'message' => "You have been assigned to task {$this->task->task_number}.",
        ];
    }
}
