<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrustGatewayAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->header('X-Auth-User-Email');

        if (is_string($email) && $email !== '') {
            $name = (string) ($request->header('X-Auth-User-Name') ?: 'Admin');

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Str::random(32),
                ],
            );

            Auth::login($user);

            if ($request->query->has('token')) {
                $cleanQuery = $request->query->all();
                unset($cleanQuery['token']);
                $target = $request->url().($cleanQuery !== [] ? '?'.http_build_query($cleanQuery) : '');

                return redirect()->to($target);
            }
        }

        if (! Auth::check()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
