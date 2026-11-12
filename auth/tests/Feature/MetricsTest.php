<?php

namespace Tests\Feature;

use Tests\TestCase;

class MetricsTest extends TestCase
{
    public function test_metrics_are_public_prometheus_text(): void
    {
        $this->get('/');

        $response = $this->get('/metrics');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
        $response->assertSee('app_up{service=', false);
        $response->assertSee('http_requests_total{service=', false);
    }
}
