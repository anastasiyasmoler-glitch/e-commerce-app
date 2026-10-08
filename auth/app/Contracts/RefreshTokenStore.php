<?php

namespace App\Contracts;

interface RefreshTokenStore
{
    public function put(int $userId, string $plainToken, int $ttlSeconds): void;

    public function userIdFor(string $plainToken): ?int;

    public function forget(int $userId, string $plainToken): void;
}
