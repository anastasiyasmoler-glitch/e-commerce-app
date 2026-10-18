<?php

namespace App\Contracts\Auth;

use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;

interface TokenIssuer
{
    /**
     * Authenticate credentials and, on success, issue a new token pair.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}|null
     */
    public function attempt(array $credentials): ?array;

    /**
     * Issue a new token pair for the given user.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function issue(User $user): array;

    /**
     * Decode a refresh token and return the id of the user it was issued to.
     *
     * @throws InvalidRefreshTokenException
     */
    public function userIdFromRefreshToken(string $token): int;

    /**
     * Invalidate the access token presented by the current request, if any.
     */
    public function invalidateAccessToken(): void;

    /**
     * Resolve the user for a raw bearer token. Returns null when the token is
     * invalid, expired, or was issued for refreshing rather than access.
     */
    public function userFromAccessToken(string $token): ?User;
}
