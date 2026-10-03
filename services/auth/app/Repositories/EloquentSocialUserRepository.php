<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\SocialUserRepositoryInterface;

class EloquentSocialUserRepository implements SocialUserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function findByProvider(string $provider, string $providerId): ?User
    {
        return User::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    public function createOAuthUser(
        string $name,
        string $email,
        string $provider,
        string $providerId,
    ): User {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => null,
            'password' => null,
            'provider' => $provider,
            'provider_id' => $providerId,
        ]);

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    public function assignRole(int $userId, string $role): void
    {
        $this->findUser($userId)->assignRole($role);
    }

    private function findUser(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }
}
