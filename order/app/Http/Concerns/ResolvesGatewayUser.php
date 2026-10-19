<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait ResolvesGatewayUser
{
    protected function gatewayUserId(Request $request): int
    {
        return (int) $request->attributes->get('auth_user_id');
    }

    /**
     * @return list<string>
     */
    protected function gatewayRoles(Request $request): array
    {
        $raw = $request->attributes->get('auth_user_roles');

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    protected function gatewayCanViewAllOrders(Request $request): bool
    {
        $roles = $this->gatewayRoles($request);

        return in_array('admin', $roles, true) || in_array('analyst', $roles, true);
    }
}
