<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListUserTasksAction
{
    public function __invoke(User $user, ?string $search, array $statuses): Collection
    {
        $query = $user->tasks()->latest();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($statuses !== []) {
            $query->whereIn('status', $statuses);
        }

        return $query->get();
    }
}
