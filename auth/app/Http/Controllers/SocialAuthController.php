<?php

namespace App\Http\Controllers;

use App\Http\Responses\JwtTokenResponse;
use App\Services\JwtAuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $social,
        private readonly JwtAuthService $auth,
        private readonly JwtTokenResponse $tokens,
    ) {}

    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): JsonResponse
    {
        try {
            $oauthUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return response()->json(['message' => 'Sign-in was cancelled or expired. Try again.'], 400);
        }

        $user = $this->social->findOrCreateFromOAuth(
            $oauthUser->getName() ?: (string) $oauthUser->getEmail(),
            $oauthUser->getEmail(),
            'google',
            (string) $oauthUser->getId(),
            (bool) ($oauthUser->user['email_verified'] ?? false),
        );

        return $this->tokens->make($this->auth->issueForUser($user));
    }
}
