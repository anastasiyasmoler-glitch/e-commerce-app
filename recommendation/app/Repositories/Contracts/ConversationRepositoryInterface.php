<?php

namespace App\Repositories\Contracts;

interface ConversationRepositoryInterface
{
    /**
     * @return object{
     *     id: string,
     *     user_id: int,
     *     store_id: int,
     *     expires_at: string,
     *     last_activity_at: string|null,
     *     created_at: string
     * }|null
     */
    public function findOwnedByUser(string $conversationId, int $userId): ?object;

    public function create(int $userId, int $storeId, string $id): void;

    public function touchActivity(string $conversationId): void;

    /**
     * @return list<array{role: string, content: string, created_at: string}>
     */
    public function listMessages(string $conversationId): array;

    public function appendMessage(string $conversationId, string $role, string $content): void;

    public function deleteOwned(string $conversationId, int $userId): bool;

    public function purgeExpiredAndIdle(): int;
}
