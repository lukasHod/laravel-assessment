<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\LoginUserAction;
use App\Actions\LogoutUserAction;
use App\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $registerUserAction): JsonResponse
    {
        $result = $registerUserAction(...$request->safe()->only(['name', 'email', 'password']));

        return response()->json(['user' => $result->user, 'token' => $result->token], 201);
    }

    public function login(LoginRequest $request, LoginUserAction $loginUserAction): JsonResponse
    {
        $result = $loginUserAction($request->email, $request->password);

        return response()->json(['user' => $result->user, 'token' => $result->token]);
    }

    public function logout(Request $request, LogoutUserAction $logoutUserAction): JsonResponse
    {
        $logoutUserAction($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }
}
