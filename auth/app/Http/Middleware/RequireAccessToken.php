<?php

namespace App\Http\Middleware;

use App\Contracts\Auth\TokenIssuer;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAccessToken
{
    public function __construct(
        private readonly TokenIssuer $tokens,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '' || $this->tokens->userFromAccessToken($bearer) === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
