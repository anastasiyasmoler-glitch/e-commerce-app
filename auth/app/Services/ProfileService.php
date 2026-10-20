<?php

namespace App\Services;

use App\Contracts\Auth\RefreshTokenStore;
use App\Contracts\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    private const EMAIL_VERIFY_TTL_SECONDS = 600;

    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshTokenStore $refreshTokens,
    ) {}

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
     * @param  array{name?: string, email?: string, phone?: string}  $attributes
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

        $this->refreshTokens->forgetAll((int) $user->id);
    }

    public function delete(User $user, ?string $password): void
    {
        if ($user->password !== null) {
            $this->assertCurrentPassword($user, (string) $password, 'password');
        }

        $this->users->delete($user);
    }

    public function createEmailVerificationToken(User $user): string
    {
        $token = (string) random_int(100000, 999999);

        Cache::put($this->emailVerificationKey($user), $token, self::EMAIL_VERIFY_TTL_SECONDS);

        return $token;
    }

    public function verifyEmail(User $user, string $token): void
    {
        $key = $this->emailVerificationKey($user);

        if (! is_string($cached = Cache::get($key)) || ! hash_equals($cached, $token)) {
            throw ValidationException::withMessages([
                'token' => ['The provided email verification token is invalid or has expired.'],
            ]);
        }

        Cache::forget($key);

        $user->email_verified_at = now();
        $this->users->save($user);
    }

    private function emailVerificationKey(User $user): string
    {
        return 'email_verify:'.$user->id;
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
