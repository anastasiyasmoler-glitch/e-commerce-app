<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('id')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames()->values()->all(),
                'is_analyst' => $user->hasRole('analyst'),
            ]);

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }
}
