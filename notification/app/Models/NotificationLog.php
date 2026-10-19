<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $event
 * @property string $channel
 * @property string $recipient
 * @property array<string, mixed> $payload
 * @property string $status
 * @property int $attempts
 * @property string|null $error_message
 * @property bool $is_dlq
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 */
class NotificationLog extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $connection = 'mongodb';

    protected $collection = 'notification_logs';

    protected $fillable = [
        'event',
        'channel',
        'recipient',
        'payload',
        'status',
        'attempts',
        'error_message',
        'is_dlq',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'is_dlq' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }
}
