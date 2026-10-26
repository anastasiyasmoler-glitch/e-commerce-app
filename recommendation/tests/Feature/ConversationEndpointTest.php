<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConversationEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function customerHeaders(): array
    {
        return [
            'X-Auth-User-Id' => '42',
            'X-Auth-User-Email' => 'buyer@example.com',
            'X-Auth-User-Roles' => 'customer',
        ];
    }

    public function test_show_requires_gateway_identity(): void
    {
        $id = (string) Str::uuid();

        $this->getJson('/recommendations/conversations/'.$id)
            ->assertUnauthorized();
    }

    public function test_show_returns_messages_for_owner(): void
    {
        $id = (string) Str::uuid();
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => 42,
            'store_id' => 1,
            'expires_at' => $now->copy()->addHours(24),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('recommendation_messages')->insert([
            'conversation_id' => $id,
            'role' => 'user',
            'content' => 'hello',
            'created_at' => $now,
        ]);

        $this->getJson('/recommendations/conversations/'.$id, $this->customerHeaders())
            ->assertOk()
            ->assertJsonPath('conversation_id', $id)
            ->assertJsonPath('messages.0.content', 'hello');
    }

    public function test_show_returns_not_found_for_other_user(): void
    {
        $id = (string) Str::uuid();
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => 99,
            'store_id' => 1,
            'expires_at' => $now->copy()->addHours(24),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->getJson('/recommendations/conversations/'.$id, $this->customerHeaders())
            ->assertNotFound();
    }

    public function test_destroy_removes_conversation_and_messages(): void
    {
        $id = (string) Str::uuid();
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => 42,
            'store_id' => 1,
            'expires_at' => $now->copy()->addHours(24),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('recommendation_messages')->insert([
            'conversation_id' => $id,
            'role' => 'user',
            'content' => 'bye',
            'created_at' => $now,
        ]);

        $this->deleteJson('/recommendations/conversations/'.$id, [], $this->customerHeaders())
            ->assertNoContent();

        $this->assertDatabaseMissing('recommendation_conversations', ['id' => $id]);
        $this->assertDatabaseMissing('recommendation_messages', ['conversation_id' => $id]);
    }

    public function test_purge_command_removes_expired_conversation(): void
    {
        $id = (string) Str::uuid();
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => 42,
            'store_id' => 1,
            'expires_at' => $now->copy()->subHour(),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->artisan('recommendations:purge-conversations')->assertSuccessful();

        $this->assertDatabaseMissing('recommendation_conversations', ['id' => $id]);
    }
}
