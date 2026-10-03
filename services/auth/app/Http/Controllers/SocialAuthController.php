<?php

namespace App\Http\Controllers;

use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $social,
    ) {}

    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $oauthUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sign-in was cancelled or expired. Try again.',
            ]);
        }

        try {
            $user = $this->social->findOrCreateFromOAuth(
                $oauthUser->getName() ?: (string) $oauthUser->getEmail(),
                (string) $oauthUser->getEmail(),
                'google',
                (string) $oauthUser->getId(),
            );
        } catch (InvalidArgumentException) {
            return redirect()->route('login')->withErrors([
                'email' => 'The identity provider did not return an email address.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
