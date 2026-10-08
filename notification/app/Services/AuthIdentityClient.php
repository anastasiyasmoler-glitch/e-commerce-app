<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AuthIdentityClient
{
    /**
     * Ask Auth `GET /me` who this access token belongs to.
     *
     * @return list<string>|null null when the token is missing or Auth rejects it
     *
     * @throws RequestException when Auth responds with something other than 200 or 401
     */
    public function roles(?string $bearerToken): ?array
    {
        if ($bearerToken === null || $bearerToken === '') {
            return null;
        }

        $baseUrl = rtrim((string) config('services.auth.base_url'), '/');

        $response = Http::withToken($bearerToken)
            ->withOptions([
                'verify' => (bool) config('services.auth.verify_ssl', false),
            ])
            ->acceptJson()
            ->get($baseUrl.'/me');

        if ($response->unauthorized()) {
            return null;
        }

        $response->throw();

        $roles = $response->json('roles', []);

        return is_array($roles) ? array_values($roles) : [];
    }
}
