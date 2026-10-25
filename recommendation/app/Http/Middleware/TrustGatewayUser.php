<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identity is established by the Auth gateway (JWT validated there via auth_request).
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

        $roles = $request->header('X-Auth-User-Roles');

        $request->attributes->set('auth_user_id', (int) $userId);
        $request->attributes->set('auth_user_email', trim($email));
        $request->attributes->set(
            'auth_user_roles',
            is_string($roles) ? trim($roles) : '',
        );

        return $next($request);
    }
}
