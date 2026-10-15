<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotificationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_and_show_a_notification(): void
    {
        $log = $this->log();
        $this->bindLogs($log);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', '507f1f77bcf86cd799439011')
            ->assertJsonPath('data.0.event', 'user.registered')
            ->assertJsonPath('data.0.recipient', 'ada@example.com')
            ->assertJsonPath('data.0.status', NotificationLog::STATUS_SENT);

        $this->getJson('/api/notifications/507f1f77bcf86cd799439011')
            ->assertOk()
            ->assertJsonPath('data.id', '507f1f77bcf86cd799439011')
            ->assertJsonPath('data.event', 'user.registered');
    }

    public function test_receives_not_found_for_an_unknown_id(): void
    {
        $this->bindLogs($this->log());

        $this->getJson('/api/notifications/missing')
            ->assertNotFound();
    }

    private function log(): NotificationLog
    {
        $log = new NotificationLog;
        $log->forceFill([
            '_id' => '507f1f77bcf86cd799439011',
            'event' => 'user.registered',
            'channel' => 'email',
            'recipient' => 'ada@example.com',
            'payload' => ['event' => 'user.registered'],
            'status' => NotificationLog::STATUS_SENT,
            'attempts' => 1,
            'error_message' => null,
            'is_dlq' => false,
            'sent_at' => null,
        ]);

        return $log;
    }

    private function bindLogs(NotificationLog $log): void
    {
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('listLatest')->andReturn(new Collection([$log]));
        $logs->shouldReceive('findById')->andReturnUsing(
            fn (string $id): ?NotificationLog => $id === (string) $log->getKey() ? $log : null,
        );

        $this->app->instance(NotificationLogRepositoryInterface::class, $logs);
    }
}
