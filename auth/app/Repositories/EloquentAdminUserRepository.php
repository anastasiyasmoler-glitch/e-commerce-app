<?php

namespace App\Repositories;

use App\Contracts\Repositories\AdminUserRepositoryInterface;
use App\Models\User;

class EloquentAdminUserRepository implements AdminUserRepositoryInterface
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     phone: string|null,
     *     role_names: list<string>
     * }>
     */
    public function listWithRoleNames(): array
    {
        return User::query()
            ->with('roles')
            ->orderBy('id')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_names' => $user->getRoleNames()->values()->all(),
                'created_at' => $user->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function hasRole(int $userId, string $role): bool
    {
        return $this->findUser($userId)->hasRole($role);
    }

    public function assignRole(int $userId, string $role): void
    {
        $this->findUser($userId)->assignRole($role);
    }

    public function removeRole(int $userId, string $role): void
    {
        $this->findUser($userId)->removeRole($role);
    }

    private function findUser(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }
}
