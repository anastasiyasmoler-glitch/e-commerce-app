<?php

namespace App\Services;

use App\Exceptions\CannotModifyAdminRolesException;
use App\Repositories\Contracts\AdminUserRepositoryInterface;

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
     *     is_analyst: bool
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
            ];
        }

        return $rows;
    }

    public function setAnalyst(int $userId, bool $analyst): void
    {
        if ($this->users->hasRole($userId, 'admin')) {
            throw new CannotModifyAdminRolesException();
        }

        if ($analyst) {
            $this->users->assignRole($userId, 'analyst');
        } else {
            $this->users->removeRole($userId, 'analyst');
        }
    }
}
