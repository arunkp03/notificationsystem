<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateTaskAssignmentNotification implements ShouldQueue
{
    public function handle(TaskAssigned $event): void
    {
        Notification::query()->create([
            'user_id' => $event->task->assigned_to,
            'type' => 'task_assigned',
            'message' => $event->notificationMessage(),
        ]);
    }
}
