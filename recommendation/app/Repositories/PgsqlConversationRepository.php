<?php

namespace App\Repositories;

use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PgsqlConversationRepository implements ConversationRepositoryInterface
{
    public function findOwnedByUser(string $conversationId, int $userId): ?object
    {
        $row = DB::table('recommendation_conversations')
            ->where('id', $conversationId)
            ->where('user_id', $userId)
            ->first();

        return $row !== null ? $row : null;
    }

    public function create(int $userId, int $storeId, string $id): void
    {
        $maxHours = (int) config('recommendation.chat.conversation_max_hours', 24);
        $now = now();

        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => $userId,
            'store_id' => $storeId,
            'expires_at' => $now->copy()->addHours($maxHours),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function touchActivity(string $conversationId): void
    {
        $now = now();
        DB::table('recommendation_conversations')
            ->where('id', $conversationId)
            ->update(['last_activity_at' => $now, 'updated_at' => $now]);
    }

    /**
     * @return list<array{role: string, content: string, created_at: string}>
     */
    public function listMessages(string $conversationId): array
    {
        $rows = DB::table('recommendation_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['role', 'content', 'created_at']);

        $messages = [];
        foreach ($rows as $row) {
            $messages[] = [
                'role' => (string) $row->role,
                'content' => (string) $row->content,
                'created_at' => (string) $row->created_at,
            ];
        }

        return $messages;
    }

    public function deleteOwned(string $conversationId, int $userId): bool
    {
        $deleted = DB::table('recommendation_conversations')
            ->where('id', $conversationId)
            ->where('user_id', $userId)
            ->delete();

        return $deleted > 0;
    }

    public function purgeExpiredAndIdle(): int
    {
        $idleMinutes = (int) config('recommendation.chat.conversation_idle_minutes', 30);
        $idleCutoff = now()->subMinutes($idleMinutes);
        $now = now();

        return DB::table('recommendation_conversations')
            ->where(function ($query) use ($now, $idleCutoff): void {
                $query->where('expires_at', '<', $now)
                    ->orWhere('last_activity_at', '<', $idleCutoff);
            })
            ->delete();
    }
}
