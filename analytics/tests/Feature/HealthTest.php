<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_healthz_returns_ok(): void
    {
        $this->getJson('/api/healthz')
            ->assertOk()
            ->assertSee('ok');
    }
}
