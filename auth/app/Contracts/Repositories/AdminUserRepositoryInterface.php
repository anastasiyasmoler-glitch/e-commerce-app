<?php

namespace App\Contracts\Repositories;

interface AdminUserRepositoryInterface
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     phone: string|null,
     *     role_names: list<string>,
     *     created_at: string|null
     * }>
     */
    public function listWithRoleNames(): array;

    public function hasRole(int $userId, string $role): bool;

    public function assignRole(int $userId, string $role): void;

    public function removeRole(int $userId, string $role): void;
}
