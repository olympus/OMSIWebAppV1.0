<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // Avoid redirect()->intended(): session url.intended is often /admin after the
        // auth middleware sends users to login, which would skip the panel homeUrl.
        return redirect()->to(Filament::getHomeUrl() ?? '/admin/merged-service-requests');
    }
}
