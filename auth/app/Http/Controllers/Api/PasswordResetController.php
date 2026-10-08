<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwords,
    ) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwords->sendLink((string) $request->validated('email'));

        return response()->json(['message' => 'Password reset link sent.']);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwords->reset(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
            (string) $request->validated('token'),
        );

        return response()->json(['message' => 'Password has been reset.']);
    }
}
