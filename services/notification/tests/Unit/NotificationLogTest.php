<?php

namespace Tests\Unit;

use App\Models\NotificationLog;
use PHPUnit\Framework\TestCase;

class NotificationLogTest extends TestCase
{
    public function test_history_fields_are_fillable(): void
    {
        $model = new NotificationLog;

        $this->assertSame([
            'event',
            'channel',
            'recipient',
            'payload',
            'status',
            'attempts',
            'error_message',
            'is_dlq',
            'sent_at',
        ], $model->getFillable());
    }

    public function test_status_constants(): void
    {
        $this->assertSame('pending', NotificationLog::STATUS_PENDING);
        $this->assertSame('sent', NotificationLog::STATUS_SENT);
        $this->assertSame('failed', NotificationLog::STATUS_FAILED);
    }
}
