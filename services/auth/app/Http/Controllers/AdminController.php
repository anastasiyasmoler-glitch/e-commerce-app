<?php

namespace App\Http\Controllers;

use App\Exceptions\CannotModifyAdminRolesException;
use App\Http\Requests\UpdateUserAnalystRequest;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminService $admin,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Users', [
            'users' => $this->admin->listUsersForAdmin(),
        ]);
    }

    public function updateAnalyst(UpdateUserAnalystRequest $request, User $user): RedirectResponse
    {
        try {
            $this->admin->setAnalyst($user->id, $request->boolean('analyst'));
        } catch (CannotModifyAdminRolesException) {
            abort(403);
        }

        return Redirect::route('admin.users');
    }
}
