<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Services\JwtAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly JwtAuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $tokens = $this->auth->register($request->safe()->only(['name', 'email', 'password']));

        return response()->json($tokens, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tokens = $this->auth->login(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        );

        return response()->json($tokens);
    }

    public function refresh(RefreshRequest $request): JsonResponse
    {
        return response()->json(
            $this->auth->refresh((string) $request->validated('refresh_token')),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->input('refresh_token'));

        return response()->json(['message' => 'Successfully logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }
}
