<?php

declare(strict_types=1);

namespace Auth\Tests\Unit;

use Auth\Health;
use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_payload(): void
    {
        $payload = (new Health())->payload();

        $this->assertSame('auth', $payload['service']);
        $this->assertSame('ok', $payload['status']);
    }
}
