<?php

namespace App\Exceptions;

use Exception;

class InvalidRefreshTokenException extends Exception
{
    public function __construct(string $message = 'Invalid refresh token.')
    {
        parent::__construct($message, 401);
    }
}
