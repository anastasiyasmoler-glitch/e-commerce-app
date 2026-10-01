<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleOidcProvider extends GoogleProvider
{
    protected function getAuthUrl($state)
    {
        if (! $this->usesMock()) {
            return parent::getAuthUrl($state);
        }

        return $this->buildAuthUrlFromBase($this->publicIssuer().'/authorize', $state);
    }

    protected function getTokenUrl()
    {
        if (! $this->usesMock()) {
            return parent::getTokenUrl();
        }

        return $this->issuer().'/token';
    }

    protected function getUserByToken($token)
    {
        if (! $this->usesMock()) {
            return parent::getUserByToken($token);
        }

        $response = $this->getHttpClient()->get($this->issuer().'/userinfo', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    private function usesMock(): bool
    {
        return filled(config('services.google.issuer'));
    }

    private function issuer(): string
    {
        return rtrim((string) config('services.google.issuer'), '/');
    }

    private function publicIssuer(): string
    {
        $issuer = $this->issuer();

        return (string) preg_replace('#://oidc:#', '://localhost:', $issuer);
    }
}
