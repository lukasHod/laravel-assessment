<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Task;
use App\Models\User;

class CreateTaskAction
{
    public function __invoke(User $user, array $validated): Task
    {
        return $user->tasks()->create($validated);
    }
}
