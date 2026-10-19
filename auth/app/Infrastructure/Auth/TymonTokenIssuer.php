<?php

namespace App\Infrastructure\Auth;

use App\Contracts\Auth\RefreshTokenStore;
use App\Contracts\Auth\TokenIssuer;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Payload;

class TymonTokenIssuer implements TokenIssuer
{
    public function __construct(
        private readonly RefreshTokenStore $refreshTokens,
    ) {}

    public function attempt(array $credentials): ?array
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $token = $guard->attempt($credentials);

        if (! is_string($token) || $token === '') {
            return null;
        }

        /** @var User $user */
        $user = $guard->user();

        return $this->issuePair($user, $token);
    }

    public function issue(User $user): array
    {
        return $this->issuePair($user);
    }

    public function userIdFromRefreshToken(string $token): int
    {
        return (int) $this->assertRefreshPayload($token)->get('sub');
    }

    public function invalidateAccessToken(): void
    {
        if (auth('api')->check()) {
            auth('api')->logout();
        }
    }

    public function userFromAccessToken(string $token): ?User
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            if ($payload->get('token_use') === 'refresh') {
                return null;
            }

            /** @var JWTGuard $guard */
            $guard = auth('api');

            /** @var User|null $user */
            $user = $guard->setToken($token)->user();
        } catch (\Throwable) {
            return null;
        }

        return $user;
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    private function issuePair(User $user, ?string $accessToken = null): array
    {
        $accessTtl = (int) config('jwt.ttl', 15);
        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 10080);

        $access = $accessToken ?? $this->encode($user, 'access', $accessTtl);
        $refresh = $this->encode($user, 'refresh', $refreshTtlMinutes);

        $this->refreshTokens->put((int) $user->getAuthIdentifier(), $refresh, $refreshTtlMinutes * 60);

        return [
            'access_token' => $access,
            'refresh_token' => $refresh,
            'token_type' => 'bearer',
            'expires_in' => $accessTtl * 60,
        ];
    }

    private function encode(User $user, string $tokenUse, int $ttlMinutes): string
    {
        $factory = JWTAuth::factory();
        $previousTtl = $factory->getTTL();
        $factory->setTTL($ttlMinutes);

        try {
            // The payload factory accumulates claims across make() calls, so a
            // previously encoded token_use=refresh would leak into later decodes.
            $factory->emptyClaims();

            return JWTAuth::customClaims(['token_use' => $tokenUse])->fromUser($user);
        } finally {
            $factory->setTTL($previousTtl);
            $factory->emptyClaims();
            JWTAuth::customClaims([]);
        }
    }

    private function assertRefreshPayload(string $token): Payload
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
