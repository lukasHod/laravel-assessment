<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuthResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function __invoke(string $email, string $password): AuthResult
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return new AuthResult(
            user: $user,
            token: $user->createToken('auth_token')->plainTextToken,
        );
    }
}
