<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Task;

class UpdateTaskAction
{
    public function __invoke(Task $task, array $validated): Task
    {
        $task->update($validated);

        return $task->fresh();
    }
}
