<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiSpecTest extends TestCase
{
    public function test_openapi_yaml_is_served(): void
    {
        $response = $this->get('/docs/openapi.yaml');
        $body = $response->streamedContent();

        $response->assertOk();
        $this->assertStringContainsString('openapi: 3.0.3', $body);
        $this->assertStringContainsString('/users', $body);
        $this->assertStringContainsString('/orders', $body);
        $this->assertStringContainsString('/search', $body);
    }

    public function test_swagger_ui_is_served(): void
    {
        $response = $this->get('/docs');
        $body = $response->streamedContent();

        $response->assertOk();
        $this->assertStringContainsString('swagger-ui', $body);
    }
}
