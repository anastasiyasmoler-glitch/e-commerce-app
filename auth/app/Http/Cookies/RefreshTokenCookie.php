<?php

namespace App\Http\Cookies;

use Symfony\Component\HttpFoundation\Cookie;

class RefreshTokenCookie
{
    public function name(): string
    {
        return (string) config('auth.refresh_cookie.name', 'refresh_token');
    }

    public function make(string $token): Cookie
    {
        return cookie(
            $this->name(),
            $token,
            (int) config('jwt.refresh_ttl', 10080),
            (string) config('auth.refresh_cookie.path', '/'),
            null,
            (bool) config('auth.refresh_cookie.secure', true),
            true,
            false,
            (string) config('auth.refresh_cookie.same_site', 'none'),
        );
    }

    public function forget(): Cookie
    {
        return cookie()->forget(
            $this->name(),
            (string) config('auth.refresh_cookie.path', '/'),
        );
    }
}
