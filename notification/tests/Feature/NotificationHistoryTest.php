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

    public function test_can_filter_list_by_query_parameters(): void
    {
        $log = $this->log();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('listFiltered')
            ->once()
            ->with(Mockery::on(function ($filters): bool {
                return $filters->status === NotificationLog::STATUS_SENT
                    && $filters->channel === 'email'
                    && $filters->recipient === 'ada'
                    && $filters->from === '2026-01-01'
                    && $filters->to === '2026-01-31';
            }))
            ->andReturn(new Collection([$log]));
        $logs->shouldReceive('findById')->andReturn(null);

        $this->app->instance(NotificationLogRepositoryInterface::class, $logs);

        $this->getJson('/api/notifications?'.http_build_query([
            'status' => 'sent',
            'channel' => 'email',
            'recipient' => 'ada',
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_rejects_invalid_status_filter(): void
    {
        $this->getJson('/api/notifications?status=unknown')->assertUnprocessable();
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
        $logs->shouldReceive('listFiltered')->andReturn(new Collection([$log]));
        $logs->shouldReceive('findById')->andReturnUsing(
            fn (string $id): ?NotificationLog => $id === (string) $log->getKey() ? $log : null,
        );

        $this->app->instance(NotificationLogRepositoryInterface::class, $logs);
    }
}
