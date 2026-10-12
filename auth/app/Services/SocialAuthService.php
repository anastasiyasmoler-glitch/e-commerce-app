<?php

namespace App\Services;

use App\Contracts\Repositories\SocialUserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class SocialAuthService
{
    public function __construct(
        private readonly SocialUserRepositoryInterface $users,
        private readonly Dispatcher $events,
    ) {}

    public function findOrCreateFromOAuth(
        string $name,
        ?string $email,
        string $provider,
        string $providerId,
        bool $emailVerified = false,
    ): User {
        if ($providerId === '') {
            throw new InvalidArgumentException('OAuth profile must include a provider id.');
        }

        $normalizedEmail = $email === null ? null : strtolower(trim($email));

        if ($normalizedEmail === '') {
            $normalizedEmail = null;
        }

        $user = $this->users->findByProvider($provider, $providerId);

        if ($user !== null) {
            return $user;
        }

        if ($emailVerified && $normalizedEmail !== null) {
            $user = $this->users->findByEmail($normalizedEmail);

            if ($user !== null) {
                return $this->users->linkProvider($user, $provider, $providerId);
            }
        }

        $user = $this->users->createOAuthUser($name, $normalizedEmail, $provider, $providerId);
        $this->users->assignRole($user->id, 'customer');

        $this->events->dispatch(new Registered($user));

        return $user;
    }
}
