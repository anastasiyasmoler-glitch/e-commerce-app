<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identity is established by the Auth gateway (JWT validated there via auth_request).
 * Order only reads trusted X-Auth-User-* headers on the internal hop; no JWT parsing here.
 */
class TrustGatewayUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-Auth-User-Id');
        $email = $request->header('X-Auth-User-Email');

        if (! is_numeric($userId) || ! is_string($email) || trim($email) === '') {
            abort(401, 'Authentication required.');
        }

        $request->attributes->set('auth_user_id', (int) $userId);
        $request->attributes->set('auth_user_email', trim($email));

        return $next($request);
    }
}
