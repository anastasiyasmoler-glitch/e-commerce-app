<?php

namespace App\Infrastructure;

use App\Contracts\RefreshTokenStore;

class ArrayRefreshTokenStore implements RefreshTokenStore
{
    /** @var array<string, int> */
    private array $tokens = [];

    public function put(int $userId, string $plainToken, int $ttlSeconds): void
    {
        $this->tokens[$this->hash($plainToken)] = $userId;
    }

    public function userIdFor(string $plainToken): ?int
    {
        return $this->tokens[$this->hash($plainToken)] ?? null;
    }

    public function forget(int $userId, string $plainToken): void
    {
        unset($this->tokens[$this->hash($plainToken)]);
    }

    private function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
