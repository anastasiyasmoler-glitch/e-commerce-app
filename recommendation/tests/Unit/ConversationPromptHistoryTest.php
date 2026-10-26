<?php

namespace Tests\Unit;

use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConversationPromptHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_prior_messages_are_capped_for_prompt(): void
    {
        config([
            'recommendation.chat.prompt_max_history_messages' => 2,
            'recommendation.chat.prompt_max_history_chars' => 500,
        ]);

        $id = (string) Str::uuid();
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => 1,
            'store_id' => 1,
            'expires_at' => $now->copy()->addDay(),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['one', 'two', 'three', 'four'] as $content) {
            DB::table('recommendation_messages')->insert([
                'conversation_id' => $id,
                'role' => 'user',
                'content' => $content,
                'created_at' => $now,
            ]);
        }

        $service = app(ConversationService::class);
        $prior = $service->priorMessagesForPrompt($id);

        $this->assertCount(2, $prior);
        $this->assertSame('two', $prior[0]['content']);
        $this->assertSame('three', $prior[1]['content']);
    }
}
