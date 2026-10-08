<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CannotModifyAdminRolesException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAnalystRequest;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminService $admin,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::forUser($request->user('api'))->authorize('viewAny', User::class);

        return response()->json(['data' => $this->admin->listUsersForAdmin()]);
    }

    public function updateAnalyst(UpdateUserAnalystRequest $request, User $user): JsonResponse
    {
        Gate::forUser($request->user('api'))->authorize('viewAny', User::class);

        try {
            $this->admin->setAnalyst($user->id, $request->boolean('analyst'));
        } catch (CannotModifyAdminRolesException) {
            abort(403);
        }

        return response()->json(['message' => 'Analyst role updated.']);
    }
}
