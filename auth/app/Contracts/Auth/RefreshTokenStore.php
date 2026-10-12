<?php

namespace App\Contracts\Auth;

interface RefreshTokenStore
{
    public function put(int $userId, string $plainToken, int $ttlSeconds): void;

    public function userIdFor(string $plainToken): ?int;

    /**
     * Atomically validates and consumes a refresh token, returning its user id.
     */
    public function claim(string $plainToken): ?int;

    public function forget(int $userId, string $plainToken): void;

    /**
     * Invalidates every refresh token issued to the given user.
     */
    public function forgetAll(int $userId): void;
}
