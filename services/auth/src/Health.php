<?php

declare(strict_types=1);

namespace Auth;

final class Health
{
    public function payload(): array
    {
        return [
            'service' => 'auth',
            'status' => 'ok',
        ];
    }
}
