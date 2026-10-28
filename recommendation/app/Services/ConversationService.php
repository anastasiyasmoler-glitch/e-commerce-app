<?php

namespace App\Services;

use App\Repositories\Contracts\ConversationRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ConversationService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversations,
    ) {}

    /**
     * @return array{
     *     conversation_id: string,
     *     store_id: int,
     *     expires_at: string,
     *     last_activity_at: string,
     *     messages: list<array{role: string, content: string, created_at: string}>
     * }
     */
    public function history(int $userId, string $conversationId): array
    {
        $row = $this->conversations->findOwnedByUser($conversationId, $userId);
        if ($row === null) {
            abort(404, 'Conversation not found.');
        }

        $this->assertNotExpired($row);

        return [
            'conversation_id' => (string) $row->id,
            'store_id' => (int) $row->store_id,
            'expires_at' => (string) $row->expires_at,
            'last_activity_at' => (string) ($row->last_activity_at ?? $row->created_at),
            'messages' => $this->conversations->listMessages($conversationId),
        ];
    }

    public function deleteForUser(int $userId, string $conversationId): void
    {
        if (! $this->conversations->deleteOwned($conversationId, $userId)) {
            abort(404, 'Conversation not found.');
        }
    }

    public function purgeExpired(): int
    {
        return $this->conversations->purgeExpiredAndIdle();
    }

    /**
     * @return array{id: string, user_id: int, store_id: int}
     */
    public function resolveForChat(int $userId, int $storeId, ?string $conversationId): array
    {
        if ($conversationId !== null && $conversationId !== '') {
            $row = $this->conversations->findOwnedByUser($conversationId, $userId);
            if ($row !== null && (int) $row->store_id === $storeId && ! $this->isExpired($row)) {
                return [
                    'id' => (string) $row->id,
                    'user_id' => (int) $row->user_id,
                    'store_id' => (int) $row->store_id,
                ];
            }

            // Stale conversation_id from another store: start a fresh context instead of 403.
        }

        $id = (string) Str::uuid();
        $this->conversations->create($userId, $storeId, $id);

        return [
            'id' => $id,
            'user_id' => $userId,
            'store_id' => $storeId,
        ];
    }

    public function touchActivity(string $conversationId): void
    {
        $this->conversations->touchActivity($conversationId);
    }

    public function appendMessage(string $conversationId, string $role, string $content): void
    {
        $this->conversations->appendMessage($conversationId, $role, $content);
    }

    /**
     * Messages before the latest turn (excludes the message just stored for this request).
     *
     * @return list<array{role: string, content: string}>
     */
    public function priorMessagesForPrompt(string $conversationId): array
    {
        $messages = $this->conversations->listMessages($conversationId);
        if ($messages === []) {
            return [];
        }

        array_pop($messages);

        $maxMessages = max(0, (int) config('recommendation.chat.prompt_max_history_messages', 10));
        $maxChars = max(0, (int) config('recommendation.chat.prompt_max_history_chars', 4000));

        if ($maxMessages === 0 || $maxChars === 0) {
            return [];
        }

        if (count($messages) > $maxMessages) {
            $messages = array_slice($messages, -$maxMessages);
        }

        while ($messages !== [] && $this->historyCharCount($messages) > $maxChars) {
            array_shift($messages);
        }

        return array_values(array_map(
            static fn (array $row): array => [
                'role' => (string) $row['role'],
                'content' => (string) $row['content'],
            ],
            $messages,
        ));
    }

    /**
     * @param  list<array{role: string, content: string, created_at?: string}>  $messages
     */
    private function historyCharCount(array $messages): int
    {
        $total = 0;
        foreach ($messages as $message) {
            $total += strlen((string) $message['content']);
        }

        return $total;
    }

    private function assertNotExpired(object $row): void
    {
        if ($this->isExpired($row)) {
            abort(410, 'Conversation expired.');
        }
    }

    private function isExpired(object $row): bool
    {
        $idleMinutes = (int) config('recommendation.chat.conversation_idle_minutes', 30);
        $now = now();

        $expiresAt = $row->expires_at instanceof CarbonInterface
            ? $row->expires_at
            : Carbon::parse((string) $row->expires_at);

        if ($now->greaterThan($expiresAt)) {
            return true;
        }

        $lastActivity = $row->last_activity_at ?? $row->created_at;
        $last = $lastActivity instanceof CarbonInterface
            ? $lastActivity
            : Carbon::parse((string) $lastActivity);

        return $now->diffInMinutes($last) > $idleMinutes;
    }
}
