<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class AuthRouteThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withMiddleware(ThrottleRequests::class);
    }

    public function test_register_returns_429_when_ip_exceeds_rate_limit(): void
    {
        config(['auth.rate_limits.register_per_minute' => 2]);

        $payload = static fn (int $index): array => [
            'name' => 'Throttle User '.$index,
            'email' => "throttle-{$index}@example.com",
            'phone' => '37529'.str_pad((string) $index, 7, '0', STR_PAD_LEFT),
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->postJson('/register', $payload(1))->assertCreated();
        $this->postJson('/register', $payload(2))->assertCreated();
        $this->postJson('/register', $payload(3))->assertStatus(429);
    }
}
