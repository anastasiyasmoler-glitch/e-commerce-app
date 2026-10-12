<?php

namespace App\Repositories;

use App\Contracts\Repositories\SocialUserRepositoryInterface;
use App\Models\User;

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
        ?string $email,
        string $provider,
        string $providerId,
    ): User {
        // Only verified emails may claim an existing address; unverified or
        // missing emails get a unique synthetic address for this provider account.
        if ($email === null || User::query()->where('email', $email)->exists()) {
            $email = strtolower($provider).'-'.strtolower($providerId).'@oauth.local';
        }

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

    public function linkProvider(User $user, string $provider, string $providerId): User
    {
        if ($user->provider_id !== null && $user->provider_id !== $providerId) {
            return $user;
        }

        $user->provider = $provider;
        $user->provider_id = $providerId;
        $user->save();

        return $user;
    }

    private function findUser(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }
}
