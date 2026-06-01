<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateTaskAction;
use App\Actions\DeleteTaskAction;
use App\Actions\ListUserTasksAction;
use App\Actions\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(IndexTaskRequest $request, ListUserTasksAction $listUserTasksAction): JsonResponse
    {
        $tasks = $listUserTasksAction(
            $request->user(),
            $request->string('search')->trim()->value() ?: null,
            $request->input('status', []),
        );

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction): JsonResponse
    {
        $task = $createTaskAction($request->user(), $request->validated());

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json($task);
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $updateTaskAction): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $updateTaskAction($task, $request->validated());

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task, DeleteTaskAction $deleteTaskAction): JsonResponse
    {
        $this->authorize('delete', $task);

        $deleteTaskAction($task);

        return response()->json(null, 204);
    }
}
