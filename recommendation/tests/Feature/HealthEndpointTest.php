<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_health_returns_json(): void
    {
        $response = $this->getJson('/recommendations/health');

        $response
            ->assertOk()
            ->assertJsonStructure(['status', 'checks' => ['app', 'database']]);
    }
}
