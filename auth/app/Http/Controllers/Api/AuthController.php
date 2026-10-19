<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Auth\TokenIssuer;
use App\Http\Controllers\Controller;
use App\Http\Cookies\RefreshTokenCookie;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Responses\TokenResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly TokenIssuer $issuer,
        private readonly TokenResponse $tokenResponse,
        private readonly RefreshTokenCookie $refreshCookie,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->tokenResponse->make(
            $this->auth->register($request->safe()->only(['name', 'email', 'phone', 'password'])),
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->tokenResponse->make($this->auth->login(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        ));
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = (string) $request->cookie($this->refreshCookie->name(), '');

        return $this->tokenResponse->make($this->auth->refresh($refreshToken));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->cookie($this->refreshCookie->name()));

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

        $user = $this->issuer->userFromAccessToken($bearer);

        if ($user === null) {
            abort(401);
        }

        if (! $user->hasRole('admin')) {
            abort(403);
        }

        return $this->gatewayIdentityResponse($user);
    }

    public function gatewayCustomer(Request $request): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '') {
            abort(401);
        }

        $user = $this->issuer->userFromAccessToken($bearer);

        if ($user === null) {
            abort(401);
        }

        return $this->gatewayIdentityResponse($user);
    }

    private function gatewayIdentityResponse(User $user): Response
    {
        $roles = $user->getRoleNames()->values()->all();

        return response()->noContent()
            ->header('X-Auth-User-Id', (string) $user->id)
            ->header('X-Auth-User-Email', (string) $user->email)
            ->header('X-Auth-User-Name', (string) $user->name)
            ->header('X-Auth-User-Roles', implode(',', $roles));
    }
}
