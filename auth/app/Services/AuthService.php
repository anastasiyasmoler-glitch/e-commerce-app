<?php

namespace App\Services;

use App\Contracts\Auth\RefreshTokenStore;
use App\Contracts\Auth\TokenIssuer;
use App\Contracts\Repositories\UserRepository;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Registered;

class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshTokenStore $refreshTokens,
        private readonly TokenIssuer $tokens,
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

        return $this->tokens->issue($user);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function login(string $email, string $password): array
    {
        $tokens = $this->tokens->attempt(['email' => $email, 'password' => $password]);

        if ($tokens === null) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $tokens;
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function refresh(string $refreshToken): array
    {
        $userId = $this->tokens->userIdFromRefreshToken($refreshToken);

        if ($this->refreshTokens->claim($refreshToken) !== $userId) {
            throw new InvalidRefreshTokenException;
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new InvalidRefreshTokenException;
        }

        return $this->tokens->issue($user);
    }

    public function logout(mixed $refreshToken): void
    {
        $this->tokens->invalidateAccessToken();

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
        return $this->tokens->issue($user);
    }
}
