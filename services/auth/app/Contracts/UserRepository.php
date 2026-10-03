<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepository
{
    /**
     * @param  array{name: string, email: string, phone: string, password: string}  $attributes
     */
    public function create(array $attributes): User;

    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;
}
