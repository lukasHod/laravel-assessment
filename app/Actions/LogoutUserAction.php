<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

class LogoutUserAction
{
    public function __invoke(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
