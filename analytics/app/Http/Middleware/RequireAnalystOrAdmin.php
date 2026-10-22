<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT is validated at the Auth gateway (auth_request). Analytics trusts X-Auth-User-* on the internal hop.
 */
class RequireAnalystOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-Auth-User-Id');
        $email = $request->header('X-Auth-User-Email');

        if (! is_numeric($userId) || ! is_string($email) || trim($email) === '') {
            abort(401, 'Authentication required.');
        }

        $roles = $this->parseRoles($request->header('X-Auth-User-Roles'));

        if (! $this->hasStaffAnalyticsRole($roles)) {
            abort(403, 'Forbidden.');
        }

        $request->attributes->set('auth_user_id', (int) $userId);
        $request->attributes->set('auth_user_email', trim($email));
        $request->attributes->set('auth_user_roles', $roles);

        return $next($request);
    }

    /**
     * @return list<string>
     */
    private function parseRoles(mixed $raw): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * @param  list<string>  $roles
     */
    private function hasStaffAnalyticsRole(array $roles): bool
    {
        return in_array('admin', $roles, true) || in_array('analyst', $roles, true);
    }
}
