<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Cookies\RefreshTokenCookie;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Responses\JwtTokenResponse;
use App\Models\User;
use App\Services\JwtAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private readonly JwtAuthService $auth,
        private readonly JwtTokenResponse $tokens,
        private readonly RefreshTokenCookie $refreshCookie,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->tokens->make(
            $this->auth->register($request->safe()->only(['name', 'email', 'phone', 'password'])),
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->tokens->make($this->auth->login(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        ));
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = (string) $request->cookie((string) config('jwt.refresh_cookie'), '');

        return $this->tokens->make($this->auth->refresh($refreshToken));
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

    public function gatewayAdmin(Request $request): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '') {
            return response()->noContent();
        }

        try {
            $payload = JWTAuth::setToken($bearer)->getPayload();

            if ($payload->get('token_use') === 'refresh') {
                abort(401, 'Refresh token cannot be used as an access token.');
            }

            /** @var User|null $user */
            $user = auth('api')->setToken($bearer)->user();
        } catch (Throwable) {
            abort(401);
        }

        if ($user === null) {
            abort(401);
        }

        if (! $user->hasRole('admin')) {
            abort(403);
        }

        return response()->noContent()
            ->header('X-Auth-User-Id', (string) $user->id)
            ->header('X-Auth-User-Email', (string) $user->email)
            ->header('X-Auth-User-Name', (string) $user->name);
    }
}
