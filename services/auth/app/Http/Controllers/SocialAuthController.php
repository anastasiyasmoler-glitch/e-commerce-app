<?php

namespace App\Http\Controllers;

use App\Http\Responses\JwtTokenResponse;
use App\Services\JwtAuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
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
            return response()->json(['message' => 'Sign-in was cancelled or expired. Try again.'], 401);
        }

        try {
            $user = $this->social->findOrCreateFromOAuth(
                $oauthUser->getName() ?: (string) $oauthUser->getEmail(),
                (string) $oauthUser->getEmail(),
                'google',
                (string) $oauthUser->getId(),
            );
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'The identity provider did not return an email address.'], 401);
        }

        return $this->tokens->make($this->auth->issueForUser($user));
    }
}
