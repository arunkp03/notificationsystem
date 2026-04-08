<?php

namespace App\Http\Controllers\Api;

use App\Events\TaskAssigned;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request)
    {
        $task = Task::query()->create($request->validated());
        $task->load('assignee');

        TaskAssigned::dispatch($task, $request->user());

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => TaskResource::make($task),
        ], Response::HTTP_CREATED);
    }
}
