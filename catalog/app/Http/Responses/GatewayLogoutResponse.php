<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class GatewayLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $adminUrl = rtrim((string) config('app.admin_url'), '/');

        return redirect()->away($adminUrl.'/analytics');
    }
}
