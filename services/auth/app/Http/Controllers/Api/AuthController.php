<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Cookies\RefreshTokenCookie;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Services\JwtAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly JwtAuthService $auth,
        private readonly RefreshTokenCookie $refreshCookie,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->tokenResponse(
            $this->auth->register($request->safe()->only(['name', 'email', 'phone', 'password'])),
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->tokenResponse($this->auth->login(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        ));
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = (string) $request->cookie((string) config('jwt.refresh_cookie'), '');

        return $this->tokenResponse($this->auth->refresh($refreshToken));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->cookie((string) config('jwt.refresh_cookie')));

        return response()->json(['message' => 'Successfully logged out.'])
            ->withCookie($this->refreshCookie->forget());
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }

    /**
     * @param  array{access_token: string, refresh_token: string, token_type: string, expires_in: int}  $tokens
     */
    private function tokenResponse(array $tokens, int $status = 200): JsonResponse
    {
        $refresh = $tokens['refresh_token'];
        unset($tokens['refresh_token']);

        return response()->json($tokens, $status)
            ->withCookie($this->refreshCookie->make($refresh));
    }
}
