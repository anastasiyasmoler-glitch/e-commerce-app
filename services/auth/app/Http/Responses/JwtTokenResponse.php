<?php

namespace App\Http\Responses;

use App\Http\Cookies\RefreshTokenCookie;
use Illuminate\Http\JsonResponse;

class JwtTokenResponse
{
    public function __construct(
        private readonly RefreshTokenCookie $refreshCookie,
    ) {}

    /**
     * @param  array{access_token: string, refresh_token: string, token_type: string, expires_in: int}  $tokens
     */
    public function make(array $tokens, int $status = 200): JsonResponse
    {
        $refresh = $tokens['refresh_token'];
        unset($tokens['refresh_token']);

        return response()->json($tokens, $status)
            ->withCookie($this->refreshCookie->make($refresh));
    }
}
