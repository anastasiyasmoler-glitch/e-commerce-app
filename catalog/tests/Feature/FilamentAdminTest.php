<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_without_gateway_headers_is_rejected(): void
    {
        $this->get('/catalog-admin')
            ->assertForbidden();
    }

    public function test_gateway_admin_headers_strip_token_query_and_keep_session(): void
    {
        Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);

        $headers = [
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Name' => 'Admin',
        ];

        $this->withHeaders($headers)
            ->get('/catalog-admin/stores?token=access-jwt-from-memory')
            ->assertRedirect('/catalog-admin/stores');

        $this->get('/catalog-admin/stores')
            ->assertOk()
            ->assertSee('Nordic Home');
    }

    public function test_logout_redirects_to_gateway_admin_analytics(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/catalog-admin');

        $this->actingAs($user)
            ->post('/catalog-admin/logout', ['_token' => csrf_token()])
            ->assertRedirect(rtrim((string) config('app.admin_url'), '/').'/analytics');
    }
}
