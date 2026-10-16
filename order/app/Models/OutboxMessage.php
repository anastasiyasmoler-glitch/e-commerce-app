<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['topic', 'key', 'payload', 'published_at', 'attempts', 'last_error'])]
class OutboxMessage extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
