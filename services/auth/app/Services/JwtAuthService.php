<?php

namespace App\Services;

use App\Contracts\RefreshTokenStore;
use App\Contracts\UserRepository;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;

class JwtAuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshTokenStore $refreshTokens,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function register(array $attributes): array
    {
        $user = $this->users->create($attributes);
        $user->assignRole('customer');

        return $this->issuePair($user);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function login(string $email, string $password): array
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $token = $guard->attempt(['email' => $email, 'password' => $password]);

        if (! is_string($token) || $token === '') {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var User $user */
        $user = $guard->user();

        return $this->issuePair($user, $token);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function refresh(string $refreshToken): array
    {
        $userId = $this->refreshTokens->userIdFor($refreshToken);

        if ($userId === null) {
            throw new InvalidRefreshTokenException;
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->refreshTokens->forget($userId, $refreshToken);
            throw new InvalidRefreshTokenException;
        }

        $this->refreshTokens->forget($userId, $refreshToken);

        return $this->issuePair($user);
    }

    public function logout(mixed $refreshToken): void
    {
        if (auth('api')->check()) {
            auth('api')->logout();
        }

        if (! is_string($refreshToken) || $refreshToken === '') {
            return;
        }

        $userId = $this->refreshTokens->userIdFor($refreshToken);

        if ($userId !== null) {
            $this->refreshTokens->forget($userId, $refreshToken);
        }
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    private function issuePair(User $user, ?string $accessToken = null): array
    {
        $access = $accessToken ?? JWTAuth::fromUser($user);
        $refresh = Str::random(64);
        $refreshTtl = (int) config('jwt.refresh_ttl', 10080) * 60;

        $this->refreshTokens->put((int) $user->getAuthIdentifier(), $refresh, $refreshTtl);

        /** @var JWTGuard $guard */
        $guard = auth('api');

        return [
            'access_token' => $access,
            'refresh_token' => $refresh,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ];
    }
}
