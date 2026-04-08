<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public User $assignedBy,
    ) {}

    public function notificationMessage(): string
    {
        return sprintf(
            'User %s assigned you task: %s',
            $this->assignedBy->name,
            $this->task->title
        );
    }
}
