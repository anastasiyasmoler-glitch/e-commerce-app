<?php

namespace Tests\Unit;

use App\Tracing\Tracer;
use Tests\TestCase;

class TracerTest extends TestCase
{
    public function test_inject_adds_traceparent_while_a_span_is_open(): void
    {
        $tracer = $this->app->make(Tracer::class);

        $payload = $tracer->run('order.create', function () use ($tracer): array {
            return $tracer->inject(['order_id' => 1]);
        });

        $this->assertArrayHasKey('traceparent', $payload);
        $this->assertMatchesRegularExpression('/^00-[0-9a-f]{32}-[0-9a-f]{16}-01$/', $payload['traceparent']);
    }
}
