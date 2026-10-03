<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\SocialUserRepositoryInterface;
use InvalidArgumentException;

class SocialAuthService
{
    public function __construct(
        private readonly SocialUserRepositoryInterface $users,
    ) {}

    public function findOrCreateFromOAuth(
        string $name,
        string $email,
        string $provider,
        string $providerId,
    ): User {
        $email = strtolower(trim($email));

        if ($email === '' || $providerId === '') {
            throw new InvalidArgumentException('OAuth profile must include email and provider id.');
        }

        $user = $this->users->findByProvider($provider, $providerId);

        if ($user !== null) {
            return $user;
        }

        $user = $this->users->findByEmail($email);

        if ($user !== null) {
            return $user;
        }

        $user = $this->users->createOAuthUser($name, $email, $provider, $providerId);
        $this->users->assignRole($user->id, 'customer');

        return $user;
    }
}
