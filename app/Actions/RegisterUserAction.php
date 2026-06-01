<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuthResult;
use App\Models\User;

class RegisterUserAction
{
    public function __invoke(string $name, string $email, string $password): AuthResult
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return new AuthResult(
            user: $user,
            token: $user->createToken('auth_token')->plainTextToken,
        );
    }
}
