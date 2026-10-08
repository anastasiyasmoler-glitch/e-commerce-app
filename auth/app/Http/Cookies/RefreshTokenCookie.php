<?php

namespace App\Http\Cookies;

use Symfony\Component\HttpFoundation\Cookie;

class RefreshTokenCookie
{
    public function make(string $token): Cookie
    {
        return cookie(
            (string) config('jwt.refresh_cookie'),
            $token,
            (int) config('jwt.refresh_ttl', 10080),
            (string) config('jwt.refresh_cookie_path', '/api'),
            null,
            (bool) config('jwt.refresh_cookie_secure', true),
            true,
            false,
            (string) config('jwt.refresh_cookie_same_site', 'lax'),
        );
    }

    public function forget(): Cookie
    {
        return cookie()->forget(
            (string) config('jwt.refresh_cookie'),
            (string) config('jwt.refresh_cookie_path', '/api'),
        );
    }
}
