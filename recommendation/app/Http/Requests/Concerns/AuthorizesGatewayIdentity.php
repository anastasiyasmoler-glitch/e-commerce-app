<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesGatewayIdentity
{
    public function authorize(): bool
    {
        return (int) $this->attributes->get('auth_user_id') > 0;
    }
}
