<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Cookies\RefreshTokenCookie;
use App\Http\Requests\Api\DeleteProfileRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Models\User;
use App\Services\JwtAuthService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profiles,
        private readonly JwtAuthService $auth,
        private readonly RefreshTokenCookie $refreshCookie,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return response()->json($this->profiles->toArray($user));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $updated = $this->profiles->update(
            $user,
            $request->safe()->only(['name', 'email', 'phone']),
        );

        return response()->json($this->profiles->toArray($updated));
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $this->profiles->changePassword(
            $user,
            (string) $request->validated('current_password'),
            (string) $request->validated('password'),
        );

        return response()->json(['message' => 'Password updated.']);
    }

    public function destroy(DeleteProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $this->profiles->delete($user, (string) $request->validated('password'));
        $this->auth->logout($request->cookie((string) config('jwt.refresh_cookie')));

        return response()->json(['message' => 'Account deleted.'])
            ->withCookie($this->refreshCookie->forget());
    }
}
