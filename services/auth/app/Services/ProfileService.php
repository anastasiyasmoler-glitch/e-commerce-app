<?php

namespace App\Services;

use App\Contracts\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(private readonly UserRepository $users) {}

    /**
     * @return array{id: mixed, name: string, phone: ?string, email: string, roles: list<string>}
     */
    public function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }

    /**
     * @param  array{name: string, email: string, phone: string}  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        return $this->users->save($user);
    }

    public function changePassword(User $user, string $currentPassword, string $password): void
    {
        $this->assertCurrentPassword($user, $currentPassword, 'current_password');

        $user->password = $password;
        $this->users->save($user);
    }

    public function delete(User $user, string $password): void
    {
        $this->assertCurrentPassword($user, $password, 'password');
        $this->users->delete($user);
    }

    private function assertCurrentPassword(User $user, string $plain, string $field): void
    {
        if (! Hash::check($plain, (string) $user->password)) {
            throw ValidationException::withMessages([
                $field => ['The provided password does not match your current password.'],
            ]);
        }
    }
}
