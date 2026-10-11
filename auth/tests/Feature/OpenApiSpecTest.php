<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiSpecTest extends TestCase
{
    public function test_openapi_yaml_is_served(): void
    {
        $this->get('/docs/openapi.yaml')
            ->assertOk()
            ->assertSee('openapi: 3.0.3', false)
            ->assertSee('/login', false)
            ->assertSee('/me', false);
    }

    public function test_swagger_ui_is_served(): void
    {
        $this->get('/docs')->assertOk()->assertSee('swagger-ui', false);
    }
}
