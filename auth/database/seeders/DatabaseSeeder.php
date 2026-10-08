<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'customer', 'analyst'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '+10000000000',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if ($admin->phone === null) {
            $admin->phone = '+10000000000';
            $admin->save();
        }

        $admin->syncRoles(['admin']);
    }
}
