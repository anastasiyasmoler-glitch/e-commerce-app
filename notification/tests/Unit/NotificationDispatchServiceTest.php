<?php

namespace Tests\Unit;

use App\Contracts\KafkaPublisherInterface;
use App\Mail\UserRegisteredMail;
use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use App\Services\NotificationDispatchService;
use Illuminate\Contracts\Mail\Mailer;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class NotificationDispatchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_unknown_event_is_rejected(): void
    {
        $service = new NotificationDispatchService(
            Mockery::mock(NotificationLogRepositoryInterface::class),
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $this->expectException(\InvalidArgumentException::class);

        $service->dispatch('not.a.topic', 'a@b.c', []);
    }

    public function test_successful_send_marks_log_sent(): void
    {
        $log = $this->pendingLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('createPending')->once()->andReturn($log);
        $logs->shouldReceive('markSent')->once()->with($log)->andReturn($log);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(Mockery::type(UserRegisteredMail::class));

        $kafka = Mockery::mock(KafkaPublisherInterface::class);
        $kafka->shouldNotReceive('publish');

        $service = new NotificationDispatchService($logs, $mailer, $kafka);
        $result = $service->dispatch('user.registered', 'user@example.com', ['email' => 'user@example.com']);

        $this->assertSame($log, $result);
    }

    public function test_retries_then_publishes_dlq(): void
    {
        config(['kafka.max_attempts' => 3, 'kafka.backoff_base_ms' => 0]);

        $log = $this->pendingLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('createPending')->once()->andReturn($log);
        $logs->shouldReceive('markFailed')->times(3)->andReturn($log);
        $logs->shouldReceive('markDlq')->once()->with($log)->andReturn($log);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send')->times(3)->andThrow(new RuntimeException('smtp down'));

        $kafka = Mockery::mock(KafkaPublisherInterface::class);
        $kafka->shouldReceive('publish')->once()->with(
            'notifications.dlq',
            Mockery::on(function (array $body) use ($log): bool {
                return $body['event'] === 'user.registered'
                    && $body['recipient'] === 'user@example.com'
                    && $body['notification_log_id'] === (string) $log->getKey();
            }),
        );

        $service = new NotificationDispatchService($logs, $mailer, $kafka);
        $service->dispatch('user.registered', 'user@example.com', []);
    }

    private function pendingLog(): NotificationLog
    {
        $log = new NotificationLog;
        $log->forceFill([
            '_id' => '507f1f77bcf86cd799439011',
            'attempts' => 0,
            'status' => NotificationLog::STATUS_PENDING,
        ]);

        return $log;
    }
}
