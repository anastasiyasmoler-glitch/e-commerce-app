<?php

namespace App\Services;

use App\Contracts\Repositories\AdminUserRepositoryInterface;
use App\Exceptions\CannotModifyAdminRolesException;

class AdminService
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $users,
    ) {}

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     phone: string|null,
     *     roles: list<string>,
     *     is_analyst: bool,
     *     created_at: string|null
     * }>
     */
    public function listUsersForAdmin(): array
    {
        $rows = [];

        foreach ($this->users->listWithRoleNames() as $row) {
            $roles = $row['role_names'];
            $rows[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'roles' => $roles,
                'is_analyst' => in_array('analyst', $roles, true),
                'created_at' => $row['created_at'],
            ];
        }

        return $rows;
    }

    public function setAnalyst(int $userId, bool $analyst): void
    {
        if ($this->users->hasRole($userId, 'admin')) {
            throw new CannotModifyAdminRolesException;
        }

        if ($analyst) {
            $this->users->assignRole($userId, 'analyst');
        } else {
            $this->users->removeRole($userId, 'analyst');
        }
    }
}
