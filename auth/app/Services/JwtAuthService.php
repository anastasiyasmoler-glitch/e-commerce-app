<?php

namespace App\Services;

use App\Contracts\Auth\RefreshTokenStore;
use App\Contracts\Repositories\UserRepository;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Payload;

class JwtAuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshTokenStore $refreshTokens,
    ) {}

    /**
     * @param  array{name: string, email: string, phone: string, password: string}  $attributes
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function register(array $attributes): array
    {
        $user = $this->users->create($attributes);
        $user->assignRole('customer');

        event(new Registered($user));

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
        $payload = $this->assertRefreshJwt($refreshToken);
        $userId = $this->refreshTokens->userIdFor($refreshToken);

        if ($userId === null || $userId !== (int) $payload->get('sub')) {
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
    public function issueForUser(User $user): array
    {
        return $this->issuePair($user);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    private function issuePair(User $user, ?string $accessToken = null): array
    {
        $accessTtl = (int) config('jwt.ttl', 15);
        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 10080);

        $access = $accessToken ?? $this->encodeJwt($user, 'access', $accessTtl);
        $refresh = $this->encodeJwt($user, 'refresh', $refreshTtlMinutes);

        $this->refreshTokens->put((int) $user->getAuthIdentifier(), $refresh, $refreshTtlMinutes * 60);

        /** @var JWTGuard $guard */
        $guard = auth('api');

        return [
            'access_token' => $access,
            'refresh_token' => $refresh,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ];
    }

    private function encodeJwt(User $user, string $tokenUse, int $ttlMinutes): string
    {
        $factory = JWTAuth::factory();
        $previousTtl = $factory->getTTL();
        $factory->setTTL($ttlMinutes);

        try {
            return JWTAuth::customClaims(['token_use' => $tokenUse])->fromUser($user);
        } finally {
            $factory->setTTL($previousTtl);
            JWTAuth::customClaims([]);
        }
    }

    private function assertRefreshJwt(string $token): Payload
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
        } catch (JWTException) {
            throw new InvalidRefreshTokenException;
        }

        if ($payload->get('token_use') !== 'refresh') {
            throw new InvalidRefreshTokenException;
        }

        return $payload;
    }
}
