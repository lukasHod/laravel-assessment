<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Task;

class DeleteTaskAction
{
    public function __invoke(Task $task): void
    {
        $task->delete();
    }
}
