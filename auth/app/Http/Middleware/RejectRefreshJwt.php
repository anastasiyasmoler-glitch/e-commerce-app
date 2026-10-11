<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RejectRefreshJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $use = auth('api')->payload()->get('token_use');
        } catch (Throwable) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if ($use === 'refresh') {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
