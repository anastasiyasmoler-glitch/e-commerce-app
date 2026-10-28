<?php

namespace Tests\Unit;

use App\Infrastructure\ClickHouseClientFactory;
use App\Services\AnalyticsEventIngestService;
use ClickHouseDB\Client;
use Mockery;
use Tests\TestCase;

class AnalyticsEventIngestServiceTest extends TestCase
{
    public function test_handle_uses_occurred_at_from_payload(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('insert')
            ->once()
            ->withArgs(function (string $table, array $rows, array $columns): bool {
                if ($table !== 'events_raw' || $columns !== ['topic', 'payload', 'occurred_at']) {
                    return false;
                }

                return $rows[0][0] === 'user.registered'
                    && str_starts_with((string) $rows[0][2], '2026-10-15');
            });

        $factory = Mockery::mock(ClickHouseClientFactory::class);
        $factory->shouldReceive('make')->once()->andReturn($client);

        $service = new AnalyticsEventIngestService($factory);
        $service->handle('user.registered', [
            'user_id' => 1,
            'email' => 'a@example.com',
            'occurred_at' => '2026-10-15T14:30:00+00:00',
        ]);
    }

    public function test_handle_rejects_non_json_serializable_payload(): void
    {
        $factory = Mockery::mock(ClickHouseClientFactory::class);
        $factory->shouldNotReceive('make');

        $service = new AnalyticsEventIngestService($factory);

        $this->expectException(\InvalidArgumentException::class);
        $service->handle('user.registered', [
            'broken' => fopen('php://memory', 'r'),
        ]);
    }
}
