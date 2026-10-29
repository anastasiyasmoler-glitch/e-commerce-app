<?php

namespace Tests\Unit;

use App\Jobs\SendNotificationEmailJob;
use App\Services\NotificationDispatchService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SendNotificationEmailJobTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_tries_follow_kafka_max_attempts_config(): void
    {
        config(['kafka.max_attempts' => 5]);

        $job = new SendNotificationEmailJob('507f1f77bcf86cd799439011');

        $this->assertSame(5, $job->tries);
    }

    public function test_backoff_returns_seconds_between_queue_attempts(): void
    {
        config(['kafka.max_attempts' => 3, 'kafka.backoff_base_ms' => 200]);

        $job = new SendNotificationEmailJob('507f1f77bcf86cd799439011');

        $this->assertSame([1, 1], $job->backoff());
    }

    public function test_failed_callback_delegates_to_dispatch_service(): void
    {
        $this->expectNotToPerformAssertions();

        $dispatcher = Mockery::mock(NotificationDispatchService::class);
        $dispatcher->shouldReceive('afterDeliveryFailed')
            ->once()
            ->with('507f1f77bcf86cd799439011', true);

        $this->app->instance(NotificationDispatchService::class, $dispatcher);

        $job = new SendNotificationEmailJob('507f1f77bcf86cd799439011', skipDlqPublish: true);
        $job->failed(new RuntimeException('smtp down'));
    }
}
