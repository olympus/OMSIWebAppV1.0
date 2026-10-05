<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class RestrictNonProductionMailRecipients
{
    /**
     * Force all outgoing mails to safe recipients in non-production.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            Mail::alwaysTo([
                'ritik.bansal@lyxelandflamingo.com',
                'dipesh.singh@lyxelandflamingo.com',
            ]);
        }

        return $next($request);
    }
}
