<?php

namespace Tests\Unit;

use App\Contracts\KafkaPublisherInterface;
use App\Jobs\SendNotificationEmailJob;
use App\Mail\UserRegisteredMail;
use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use App\Services\NotificationDispatchService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Bus;
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
        $this->expectNotToPerformAssertions();

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

    public function test_enqueue_creates_pending_log_and_dispatches_job(): void
    {
        Bus::fake();

        $log = $this->pendingLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('createPending')
            ->once()
            ->with('user.registered', 'email', 'user@example.com', ['name' => 'Ada'])
            ->andReturn($log);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldNotReceive('send');

        $kafka = Mockery::mock(KafkaPublisherInterface::class);
        $kafka->shouldNotReceive('publish');

        $service = new NotificationDispatchService($logs, $mailer, $kafka);
        $result = $service->enqueue('user.registered', 'user@example.com', ['name' => 'Ada']);

        $this->assertSame($log, $result);
        Bus::assertDispatched(
            SendNotificationEmailJob::class,
            fn (SendNotificationEmailJob $job): bool => $job->notificationLogId === '507f1f77bcf86cd799439011'
                && $job->skipDlqPublish === false,
        );
    }

    public function test_deliver_marks_log_sent_when_mail_succeeds(): void
    {
        $this->expectNotToPerformAssertions();

        $log = $this->deliverableLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->with('507f1f77bcf86cd799439011')->andReturn($log);
        $logs->shouldReceive('markSent')->once()->with($log)->andReturn($log);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(Mockery::type(UserRegisteredMail::class));

        $service = new NotificationDispatchService($logs, $mailer, Mockery::mock(KafkaPublisherInterface::class));
        $service->deliver('507f1f77bcf86cd799439011');
    }

    public function test_deliver_marks_failed_and_rethrows_on_mail_error(): void
    {
        $log = $this->deliverableLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->andReturn($log);
        $logs->shouldReceive('markFailed')->once()->with($log, 'smtp down')->andReturn($log);

        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->andThrow(new RuntimeException('smtp down'));

        $service = new NotificationDispatchService($logs, $mailer, Mockery::mock(KafkaPublisherInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('smtp down');

        $service->deliver('507f1f77bcf86cd799439011');
    }

    public function test_after_delivery_failed_publishes_dlq_when_not_replay(): void
    {
        $this->expectNotToPerformAssertions();

        $log = $this->deliverableLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->with('507f1f77bcf86cd799439011')->andReturn($log);
        $logs->shouldReceive('markDlq')->once()->with($log)->andReturn($log);

        $kafka = Mockery::mock(KafkaPublisherInterface::class);
        $kafka->shouldReceive('publish')->once()->with(
            'notifications.dlq',
            Mockery::on(function (array $body): bool {
                return $body['event'] === 'user.registered'
                    && $body['recipient'] === 'user@example.com'
                    && $body['notification_log_id'] === '507f1f77bcf86cd799439011';
            }),
        );

        $service = new NotificationDispatchService($logs, Mockery::mock(Mailer::class), $kafka);
        $service->afterDeliveryFailed('507f1f77bcf86cd799439011', skipDlqPublish: false);
    }

    public function test_after_delivery_failed_skips_kafka_publish_on_replay_job(): void
    {
        $this->expectNotToPerformAssertions();

        $log = $this->deliverableLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->andReturn($log);
        $logs->shouldReceive('markDlq')->once()->with($log)->andReturn($log);

        $kafka = Mockery::mock(KafkaPublisherInterface::class);
        $kafka->shouldNotReceive('publish');

        $service = new NotificationDispatchService($logs, Mockery::mock(Mailer::class), $kafka);
        $service->afterDeliveryFailed('507f1f77bcf86cd799439011', skipDlqPublish: true);
    }

    public function test_replay_notification_log_resets_log_and_dispatches_replay_job(): void
    {
        Bus::fake();

        $log = $this->deliverableLog();
        $log->forceFill([
            'status' => NotificationLog::STATUS_FAILED,
            'is_dlq' => true,
            'attempts' => 3,
            'error_message' => 'smtp down',
        ]);

        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->with('507f1f77bcf86cd799439011')->andReturn($log);
        $logs->shouldReceive('resetForReplay')->once()->with($log)->andReturnUsing(
            function (NotificationLog $entry): NotificationLog {
                $entry->forceFill([
                    'status' => NotificationLog::STATUS_PENDING,
                    'is_dlq' => false,
                    'attempts' => 0,
                    'error_message' => null,
                ]);

                return $entry;
            },
        );

        $service = new NotificationDispatchService(
            $logs,
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $service->replayNotificationLog('507f1f77bcf86cd799439011');

        Bus::assertDispatched(
            SendNotificationEmailJob::class,
            fn (SendNotificationEmailJob $job): bool => $job->notificationLogId === '507f1f77bcf86cd799439011'
                && $job->skipDlqPublish === true,
        );
    }

    public function test_replay_notification_log_rejects_non_dlq_entries(): void
    {
        $log = $this->deliverableLog();
        $log->forceFill(['is_dlq' => false]);

        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->andReturn($log);
        $logs->shouldNotReceive('resetForReplay');

        $service = new NotificationDispatchService(
            $logs,
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $this->expectException(\InvalidArgumentException::class);

        $service->replayNotificationLog('507f1f77bcf86cd799439011');
    }

    public function test_replay_from_dlq_replays_existing_log_by_id(): void
    {
        Bus::fake();

        $log = $this->deliverableLog();
        $log->forceFill(['is_dlq' => true, 'status' => NotificationLog::STATUS_FAILED]);

        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->twice()->with('507f1f77bcf86cd799439011')->andReturn($log);
        $logs->shouldReceive('resetForReplay')->once()->with($log)->andReturn($log);

        $service = new NotificationDispatchService(
            $logs,
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $service->replayFromDlq([
            'notification_log_id' => '507f1f77bcf86cd799439011',
            'event' => 'user.registered',
            'recipient' => 'user@example.com',
            'payload' => ['email' => 'user@example.com'],
        ]);

        Bus::assertDispatched(SendNotificationEmailJob::class);
    }

    public function test_replay_from_dlq_enqueues_when_log_id_is_unknown(): void
    {
        Bus::fake();

        $log = $this->pendingLog();
        $logs = Mockery::mock(NotificationLogRepositoryInterface::class);
        $logs->shouldReceive('findById')->once()->with('missing')->andReturn(null);
        $logs->shouldReceive('createPending')
            ->once()
            ->with('user.registered', 'email', 'user@example.com', ['email' => 'user@example.com'])
            ->andReturn($log);

        $service = new NotificationDispatchService(
            $logs,
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $service->replayFromDlq([
            'notification_log_id' => 'missing',
            'event' => 'user.registered',
            'recipient' => 'user@example.com',
            'payload' => ['email' => 'user@example.com'],
        ]);

        Bus::assertDispatched(
            SendNotificationEmailJob::class,
            fn (SendNotificationEmailJob $job): bool => $job->skipDlqPublish === true,
        );
    }

    public function test_unknown_event_is_rejected_on_enqueue(): void
    {
        Bus::fake();

        $service = new NotificationDispatchService(
            Mockery::mock(NotificationLogRepositoryInterface::class),
            Mockery::mock(Mailer::class),
            Mockery::mock(KafkaPublisherInterface::class),
        );

        $this->expectException(\InvalidArgumentException::class);

        $service->enqueue('not.a.topic', 'a@b.c', []);
    }

    private function deliverableLog(): NotificationLog
    {
        $log = $this->pendingLog();
        $log->forceFill([
            'event' => 'user.registered',
            'recipient' => 'user@example.com',
            'payload' => ['email' => 'user@example.com'],
        ]);

        return $log;
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
