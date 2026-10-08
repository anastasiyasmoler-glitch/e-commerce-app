<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface SocialUserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByProvider(string $provider, string $providerId): ?User;

    public function createOAuthUser(
        string $name,
        string $email,
        string $provider,
        string $providerId,
    ): User;

    public function assignRole(int $userId, string $role): void;
}
